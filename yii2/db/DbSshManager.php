<?php

namespace d3system\yii2\db;

use d3system\compnents\SSHTunnel;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Connection;

/**
 * Database Manager with SSH Tunnel support
 */
class DbSshManager extends Component
{
    public array $tunnelConfigs = [];
    private array $_activeTunnels = [];

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        // Initialize configured tunnels
        foreach ($this->tunnelConfigs as $name => $config) {
            $this->createTunnelConnection($name, $config);
        }
    }

    /**
     * Create database connection through SSH tunnel
     * @param string $name Connection name
     * @param array $config Configuration
     * @return Connection
     * @throws Exception
     */
    public function createTunnelConnection(string $name, array $config): Connection
    {
        if (isset($this->_activeTunnels[$name])) {
            return $this->_activeTunnels[$name]['connection'];
        }
        $sshConfig = $config['ssh'];
        $dbConfig = $config['database'];
        $localPort = $config['localPort'] ?? 3307;

        if (!YII_ENV_DEV) {
            // Create SSH tunnel
            $tunnel = new SSHTunnel($sshConfig);

            // Create persistent tunnel
            $pid = $tunnel->createPersistentTunnel(
                $localPort,
                $config['remoteHost'] ?? 'localhost',
                $config['remotePort'] ?? 3306
            );


            // Wait for tunnel to establish
            sleep(1);
        } else {
            $tunnel = null;
            $pid = null;
        }
        // Create database connection
        $connection = new Connection($dbConfig);
        $connection->open();
        $this->_activeTunnels[$name] = [
            'tunnel' => $tunnel,
            'connection' => $connection,
            'pid' => $pid,
            'localPort' => $localPort
        ];

        return $connection;
    }

    /**
     * Get database connection
     * @param string $name
     * @return Connection|null
     */
    public function getConnection(string $name): ?Connection
    {
        return isset($this->_activeTunnels[$name]) ? $this->_activeTunnels[$name]['connection'] : null;
    }

    /**
     * Close tunnel connection
     * @param string $name
     */
    public function closeTunnel(string $name): void
    {
        if (isset($this->_activeTunnels[$name])) {
            $connection = $this->_activeTunnels[$name];
            if ($connection['tunnel']) {
                $connection['tunnel']->killTunnel($connection['pid']);
                $connection['connection']->close();
            }
            unset($this->_activeTunnels[$name]);
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
}
