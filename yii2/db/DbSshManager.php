<?php

namespace d3system\yii2\db;

use d3system\compnents\SSHTunnel;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * Database Manager with SSH Tunnel support
 *
 * Example configuration:
 * ```php
 * 'dbSshManager' => [
 *     'class' => DbSshManager::class,
 *     'tunnelConfigs' => [
 *         'agro' => [
 *             'ssh' => [                     // SSHTunnel properties
 *                 'sshHost' => '80.232.216.150',
 *                 'sshPort' => 2256,
 *                 'sshUsername' => 'agroread',
 *                 'sshPrivateKey' => '/var/www/.ssh/.ssh',
 *             ],
 *             'database' => [                // yii\db\Connection config
 *                 'dsn' => 'mysql:host=127.0.0.1;port=3307;dbname=agro',
 *                 'username' => 'AgroRead',
 *                 'password' => '...',
 *             ],
 *             'localPort' => 3307,           // default 3307
 *             'localHost' => '127.0.0.1',    // default 127.0.0.1
 *             'remoteHost' => '127.0.0.1',   // default 127.0.0.1 (as seen from SSH server)
 *             'remotePort' => 3306,          // default 3306
 *             'useTunnel' => true,           // default !YII_ENV_DEV
 *             'skipSsl' => true,             // default true, traffic is already encrypted by SSH
 *         ],
 *     ],
 * ],
 * ```
 *
 * If something already listens on localHost:localPort (e.g. a tunnel kept up by
 * autossh/systemd), it is reused and is never killed by this component.
 */
class DbSshManager extends Component
{
    /** @var array<string, array> connection name => configuration */
    public array $tunnelConfigs = [];

    /** Open all configured connections in init(). If false, they are opened on first getConnection() */
    public bool $eagerConnect = true;

    /** Seconds to wait for the SSH tunnel local port to start accepting connections */
    public float $tunnelTimeout = 10.0;

    /** @var array<string, array{tunnel: ?SSHTunnel, connection: Connection, pid: ?int, localPort: int}> */
    private array $_activeTunnels = [];

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if ($this->eagerConnect) {
            foreach ($this->tunnelConfigs as $name => $config) {
                $this->createTunnelConnection($name, $config);
            }
        }
    }

    /**
     * Create database connection through SSH tunnel
     * @param string $name Connection name
     * @param array|null $config Configuration, if null it is taken from $tunnelConfigs
     * @return Connection
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function createTunnelConnection(string $name, ?array $config = null): Connection
    {
        if (isset($this->_activeTunnels[$name])) {
            return $this->_activeTunnels[$name]['connection'];
        }

        if ($config === null) {
            if (!isset($this->tunnelConfigs[$name])) {
                throw new InvalidConfigException("DbSshManager: connection '$name' is not configured");
            }
            $config = $this->tunnelConfigs[$name];
        }
        if (empty($config['database']) || !is_array($config['database'])) {
            throw new InvalidConfigException("DbSshManager: 'database' config is required for '$name'");
        }

        $localHost = $config['localHost'] ?? '127.0.0.1';
        $localPort = (int)($config['localPort'] ?? 3307);
        $useTunnel = $config['useTunnel'] ?? !YII_ENV_DEV;

        $tunnel = null;
        $pid = null;

        // Start our own tunnel only if nothing listens on the local port yet
        if ($useTunnel && !$this->isPortOpen($localHost, $localPort)) {
            if (empty($config['ssh']) || !is_array($config['ssh'])) {
                throw new InvalidConfigException("DbSshManager: 'ssh' config is required for '$name'");
            }
            $remoteHost = $config['remoteHost'] ?? '127.0.0.1';
            $remotePort = (int)($config['remotePort'] ?? 3306);

            $tunnel = new SSHTunnel($config['ssh']);
            $pid = $tunnel->createPersistentTunnel($localPort, $remoteHost, $remotePort, $localHost);

            $error = $this->waitForTunnel($tunnel, $pid, $localHost, $localPort, $this->tunnelTimeout);
            if ($error !== null) {
                $tunnel->killTunnel($pid);
                throw new Exception(sprintf(
                    "DbSshManager: SSH tunnel '%s' (%s:%d -> %s:%d) failed: %s",
                    $name, $localHost, $localPort, $remoteHost, $remotePort, $error
                ));
            }
        }

        $dbConfig = $config['database'];
        // Traffic is already encrypted by the SSH tunnel, so MySQL TLS is not needed
        if ($config['skipSsl'] ?? true) {
            $dbConfig = $this->skipSsl($dbConfig);
        }

        try {
            /** @var Connection $connection */
            $connection = Yii::createObject(array_merge(['class' => Connection::class], $dbConfig));
            $connection->open();
        } catch (Throwable $e) {
            // do not leave an orphan ssh process behind
            if ($tunnel) {
                $tunnel->killTunnel($pid);
            }
            throw $e;
        }

        $this->_activeTunnels[$name] = [
            'tunnel' => $tunnel,
            'connection' => $connection,
            'pid' => $pid,
            'localPort' => $localPort,
        ];

        return $connection;
    }

    /**
     * Get database connection. Opens it on first use, if it is configured in $tunnelConfigs.
     * @param string $name
     * @return Connection|null null if connection is not configured
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getConnection(string $name): ?Connection
    {
        if (isset($this->_activeTunnels[$name])) {
            return $this->_activeTunnels[$name]['connection'];
        }
        if (isset($this->tunnelConfigs[$name])) {
            return $this->createTunnelConnection($name);
        }
        return null;
    }

    /**
     * Close database connection and the tunnel, if it was started by this component
     * @param string $name
     */
    public function closeTunnel(string $name): void
    {
        if (!isset($this->_activeTunnels[$name])) {
            return;
        }
        $active = $this->_activeTunnels[$name];
        unset($this->_activeTunnels[$name]);

        $active['connection']->close();
        if ($active['tunnel']) {
            $active['tunnel']->killTunnel($active['pid']);
        }
    }

    /**
     * Close all tunnels
     */
    public function closeAllTunnels(): void
    {
        foreach (array_keys($this->_activeTunnels) as $name) {
            $this->closeTunnel($name);
        }
    }

    public function __destruct()
    {
        $this->closeAllTunnels();
    }

    /**
     * Remove PDO MySQL SSL attributes so the connection does not use TLS
     * (equivalent of the mysql client "--skip-ssl" option).
     * @param array $dbConfig
     * @return array
     */
    private function skipSsl(array $dbConfig): array
    {
        if (empty($dbConfig['attributes'])) {
            return $dbConfig;
        }
        // PHP 8.4+ has Pdo\Mysql::ATTR_SSL_*, older versions PDO::MYSQL_ATTR_SSL_*
        $prefix = class_exists('Pdo\Mysql') ? 'Pdo\Mysql::ATTR_' : 'PDO::MYSQL_ATTR_';
        foreach (['SSL_KEY', 'SSL_CERT', 'SSL_CA', 'SSL_CAPATH', 'SSL_CIPHER', 'SSL_VERIFY_SERVER_CERT'] as $sslName) {
            if (defined($prefix . $sslName)) {
                unset($dbConfig['attributes'][constant($prefix . $sslName)]);
            }
        }
        return $dbConfig;
    }

    /**
     * Check if something accepts TCP connections on host:port
     */
    private function isPortOpen(string $host, int $port, float $timeout = 0.5): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($socket === false) {
            return false;
        }
        fclose($socket);
        return true;
    }

    /**
     * Wait until the tunnel local port accepts TCP connections
     * @return string|null null on success, otherwise error description with ssh output
     */
    private function waitForTunnel(SSHTunnel $tunnel, int $pid, string $host, int $port, float $timeout): ?string
    {
        $deadline = microtime(true) + $timeout;
        do {
            if ($this->isPortOpen($host, $port)) {
                return null;
            }
            if (!$tunnel->isTunnelActive($pid)) {
                usleep(100000); // let ssh flush its output
                return sprintf(
                    "ssh process exited (OS user '%s'). ssh output: %s Command: %s",
                    SSHTunnel::getOsUser(),
                    $tunnel->getTunnelOutput($pid) ?: '(empty)',
                    $tunnel->getTunnelCommand($pid)
                );
            }
            usleep(200000);
        } while (microtime(true) < $deadline);

        return sprintf(
            'local port did not open within %.1f s (OS user \'%s\'). ssh output: %s Command: %s',
            $timeout,
            SSHTunnel::getOsUser(),
            $tunnel->getTunnelOutput($pid) ?: '(empty)',
            $tunnel->getTunnelCommand($pid)
        );
    }
}
