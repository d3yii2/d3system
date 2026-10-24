<?php

namespace d3system\compnents;

use yii\base\Component;
use yii\base\Exception;

/**
 * SSH Tunnel Component for Yii2
 *
 * createPersistentTunnel() starts the system OpenSSH client ("ssh", on Windows 10+ also available)
 * as a background process without a shell. ssh stdout/stderr is written to a log file, so the reason
 * of a failure can be read with getTunnelOutput().
 *
 * createTunnel() uses the PHP ssh2 extension.
 *
 * @property resource $connection
 * @property array $tunnels
 */
class SSHTunnel extends Component
{
    public ?string $sshHost = null;
    public int $sshPort = 22;
    public ?string $sshUsername = null;
    public ?string $sshPassword = null;
    public ?string $sshPrivateKey = null;
    public ?string $sshPublicKey = null;
    public string $sshPassphrase = '';

    /** ssh client executable, e.g. '/usr/bin/ssh' or 'C:\Windows\System32\OpenSSH\ssh.exe' */
    public string $sshBinary = 'ssh';

    /**
     * ssh StrictHostKeyChecking option.
     * 'accept-new' - unknown host key is saved to known_hosts on the first connection (needs writable known_hosts)
     * 'yes' - host key must already be in known_hosts
     */
    public string $strictHostKeyChecking = 'accept-new';

    /** Custom known_hosts file, if the OS user (e.g. www-data) has no writable ~/.ssh/known_hosts */
    public ?string $knownHostsFile = null;

    /** Extra ssh -o options: ['ServerAliveInterval' => 60] or ['ServerAliveInterval=60'] */
    public array $sshOptions = [];

    private $_connection;
    private array $_tunnels = [];

    /** @var array<int, array{process: resource, log: string, command: string}> pid => process data */
    private array $_processes = [];

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        if (!$this->sshHost) {
            throw new Exception('SSH host is required');
        }

        if (!$this->sshUsername) {
            throw new Exception('SSH username is required');
        }
    }

    /**
     * Connect to SSH server (ssh2 extension)
     * @return bool
     * @throws Exception
     */
    public function connect(): bool
    {
        if ($this->_connection) {
            return true;
        }

        if (!extension_loaded('ssh2')) {
            throw new Exception('SSH2 extension is not loaded');
        }

        $this->_connection = ssh2_connect($this->sshHost, $this->sshPort);

        if (!$this->_connection) {
            throw new Exception("Cannot connect to SSH server {$this->sshHost}:{$this->sshPort}");
        }

        // Authenticate
        if ($this->sshPrivateKey && $this->sshPublicKey) {
            $auth = ssh2_auth_pubkey_file(
                $this->_connection,
                $this->sshUsername,
                $this->sshPublicKey,
                $this->sshPrivateKey,
                $this->sshPassphrase
            );
        } else {
            $auth = ssh2_auth_password($this->_connection, $this->sshUsername, $this->sshPassword);
        }

        if (!$auth) {
            throw new Exception("SSH authentication failed for user {$this->sshUsername}");
        }

        return true;
    }

    /**
     * Create SSH tunnel (ssh2 extension)
     * @param int $localPort
     * @param string $remoteHost
     * @param int $remotePort
     * @return resource
     * @throws Exception
     */
    public function createTunnel(int $localPort, string $remoteHost, int $remotePort)
    {
        if (!$this->connect()) {
            throw new Exception('SSH connection failed');
        }

        $tunnel = ssh2_tunnel($this->_connection, $remoteHost, $remotePort);

        if (!$tunnel) {
            throw new Exception("Failed to create tunnel to {$remoteHost}:{$remotePort}");
        }

        $this->_tunnels[] = [
            'local_port' => $localPort,
            'remote_host' => $remoteHost,
            'remote_port' => $remotePort,
            'resource' => $tunnel
        ];

        return $tunnel;
    }

    /**
     * Create persistent tunnel using system ssh client.
     * The method returns immediately; check the local port / isTunnelActive() to know when it is ready.
     * @param int $localPort
     * @param string $remoteHost host as seen from the SSH server
     * @param int $remotePort
     * @param string $localHost local bind address
     * @return int Process ID of ssh
     * @throws Exception
     */
    public function createPersistentTunnel(
        int $localPort,
        string $remoteHost,
        int $remotePort,
        string $localHost = '127.0.0.1'
    ): int {
        if ($this->sshPrivateKey && !is_readable($this->sshPrivateKey)) {
            throw new Exception(sprintf(
                "SSH private key '%s' does not exist or is not readable by OS user '%s'",
                $this->sshPrivateKey,
                self::getOsUser()
            ));
        }

        $command = $this->buildSshCommand($localPort, $remoteHost, $remotePort, $localHost);

        $env = null;
        if ($this->sshPassword && !$this->sshPrivateKey) {
            // sshpass -e reads the password from SSHPASS, so it is not visible in the process list
            array_unshift($command, 'sshpass', '-e');
            $env = array_merge(getenv(), ['SSHPASS' => $this->sshPassword]);
        }

        $logFile = tempnam(sys_get_temp_dir(), 'sshtunnel_');
        if ($logFile === false) {
            throw new Exception('Can not create SSH tunnel log file in ' . sys_get_temp_dir());
        }
        $nullDevice = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

        // Array command: no shell, so the returned pid is the pid of ssh itself
        $process = @proc_open(
            $command,
            [
                0 => ['file', $nullDevice, 'r'],
                1 => ['file', $logFile, 'a'],
                2 => ['file', $logFile, 'a'],
            ],
            $pipes,
            null,
            $env
        );

        if (!is_resource($process)) {
            @unlink($logFile);
            $error = error_get_last();
            throw new Exception(
                'Failed to start SSH tunnel process: ' . implode(' ', $command)
                . ($error ? ' (' . $error['message'] . ')' : '')
            );
        }

        $pid = (int)proc_get_status($process)['pid'];
        $this->_processes[$pid] = [
            'process' => $process,
            'log' => $logFile,
            'command' => implode(' ', $command),
        ];

        return $pid;
    }

    /**
     * Build ssh command for the persistent tunnel
     * @return string[]
     */
    public function buildSshCommand(
        int $localPort,
        string $remoteHost,
        int $remotePort,
        string $localHost = '127.0.0.1'
    ): array {
        $options = [
            'ExitOnForwardFailure' => 'yes',
            'ServerAliveInterval' => 30,
            'ServerAliveCountMax' => 3,
            'ConnectTimeout' => 10,
            'StrictHostKeyChecking' => $this->strictHostKeyChecking,
        ];
        if ($this->knownHostsFile) {
            $options['UserKnownHostsFile'] = $this->knownHostsFile;
        }
        if ($this->sshPrivateKey) {
            // never wait for interactive password/passphrase input
            $options['BatchMode'] = 'yes';
            $options['IdentitiesOnly'] = 'yes';
        }
        // sshOptions: ['Name' => 'value'] or ['Name=value'] (both forms can be mixed)
        foreach ($this->sshOptions as $name => $value) {
            if (is_int($name)) {
                [$name, $value] = array_pad(explode('=', (string)$value, 2), 2, '');
            }
            $options[trim($name)] = is_bool($value) ? ($value ? 'yes' : 'no') : trim((string)$value);
        }

        $command = [$this->sshBinary, '-N', '-p', (string)$this->sshPort];
        if ($this->sshPrivateKey) {
            $command[] = '-i';
            $command[] = $this->sshPrivateKey;
        }
        foreach ($options as $name => $value) {
            $command[] = '-o';
            $command[] = $name . '=' . $value;
        }
        $command[] = '-L';
        $command[] = "{$localHost}:{$localPort}:{$remoteHost}:{$remotePort}";
        $command[] = "{$this->sshUsername}@{$this->sshHost}";

        return $command;
    }

    /**
     * Command line of the tunnel process started by this instance (contains no password)
     * @param int $pid
     * @return string
     */
    public function getTunnelCommand(int $pid): string
    {
        return $this->_processes[$pid]['command'] ?? '';
    }

    /**
     * ssh client output (errors) of the tunnel process started by this instance
     * @param int $pid
     * @return string
     */
    public function getTunnelOutput(int $pid): string
    {
        $logFile = $this->_processes[$pid]['log'] ?? null;
        if (!$logFile || !is_readable($logFile)) {
            return '';
        }
        $output = trim((string)file_get_contents($logFile));
        return strlen($output) > 2000 ? '...' . substr($output, -2000) : $output;
    }

    /**
     * Kill tunnel process
     * @param int|null $pid
     * @return bool
     */
    public function killTunnel($pid): bool
    {
        if (!$pid) {
            return false;
        }
        $pid = (int)$pid;

        if (isset($this->_processes[$pid])) {
            $process = $this->_processes[$pid]['process'];
            if (is_resource($process)) {
                if (proc_get_status($process)['running']) {
                    proc_terminate($process);
                }
                proc_close($process);
            }
            @unlink($this->_processes[$pid]['log']);
            unset($this->_processes[$pid]);
            return true;
        }

        // process not started by this instance
        if (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /PID ' . $pid . ' >NUL 2>&1', $output, $returnCode);
            return $returnCode === 0;
        }
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 15);
        }
        exec('kill ' . $pid . ' 2>/dev/null', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Kill all tunnels
     */
    public function killAllTunnels(): void
    {
        foreach (array_keys($this->_processes) as $pid) {
            $this->killTunnel($pid);
        }
        $this->_processes = [];

        foreach ($this->_tunnels as $tunnel) {
            if (is_resource($tunnel['resource'])) {
                fclose($tunnel['resource']);
            }
        }
        $this->_tunnels = [];
    }

    /**
     * Check if tunnel process is running
     * @param int|null $pid
     * @return bool
     */
    public function isTunnelActive($pid): bool
    {
        if (!$pid) {
            return false;
        }
        $pid = (int)$pid;

        if (isset($this->_processes[$pid])) {
            $process = $this->_processes[$pid]['process'];
            return is_resource($process) && proc_get_status($process)['running'];
        }

        if (PHP_OS_FAMILY === 'Windows') {
            exec('tasklist /FI "PID eq ' . $pid . '" /NH', $output);
            return strpos(implode(' ', $output), ' ' . $pid . ' ') !== false;
        }
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        }
        $result = exec('ps -p ' . $pid . ' > /dev/null 2>&1; echo $?');
        return (int)$result === 0;
    }

    /**
     * OS user name of the PHP process
     */
    public static function getOsUser(): string
    {
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_geteuid());
            if (!empty($info['name'])) {
                return $info['name'];
            }
        }
        return (string)(getenv('USERNAME') ?: getenv('USER') ?: get_current_user());
    }

    public function __destruct()
    {
        $this->killAllTunnels();
    }
}
