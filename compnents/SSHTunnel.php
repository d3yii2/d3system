<?php

namespace d3system\compnents;

use yii\base\Component;
use yii\base\Exception;

/**
 * SSH Tunnel Component for Yii2
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

    private $_connection;
    private array $_tunnels = [];
    private array $_processes = [];

    /**
     * Initialize SSH connection
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        if (!extension_loaded('ssh2')) {
            throw new Exception('SSH2 extension is not loaded');
        }

        if (!$this->sshHost) {
            throw new Exception('SSH host is required');
        }

        if (!$this->sshUsername) {
            throw new Exception('SSH username is required');
        }
    }

    /**
     * Connect to SSH server
     * @return bool
     * @throws Exception
     */
    public function connect(): bool
    {
        if ($this->_connection) {
            return true;
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
     * Create SSH tunnel
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
     * Create persistent tunnel using system command
     * @param int $localPort
     * @param string $remoteHost
     * @param int $remotePort
     * @return int Process ID
     * @throws Exception
     */
    public function createPersistentTunnel(
        int $localPort,
        string $remoteHost,
        int $remotePort
    ): int {
        $keyOption = '';
        if ($this->sshPrivateKey) {
            $keyOption = "-i {$this->sshPrivateKey}";
        }

        $command = "ssh  {$keyOption} {$this->sshUsername}@{$this->sshHost} -p {$this->sshPort} -L {$localPort}:{$remoteHost}:{$remotePort} -N &";


        if ($this->sshPassword && !$this->sshPrivateKey) {
            // Use sshpass for password authentication
            $command = "sshpass -p '{$this->sshPassword}' " . $command;
        }

        $output = [];
        exec($command . " > /dev/null 2>&1 & echo $!", $output, $returnCode);

        if (empty($output[0])) {
            throw new Exception("Failed to create persistent tunnel");
        }

        $pid = (int)$output[0];
        $this->_processes[] = $pid;

        return $pid;
    }

    /**
     * Kill tunnel process
     * @param int $pid
     * @return bool
     */
    public function killTunnel($pid): bool
    {
        if (!$pid) {
            return false;
        }

        exec("kill {$pid} 2>/dev/null");
        return true;
    }

    /**
     * Kill all tunnels
     */
    public function killAllTunnels(): void
    {
        foreach ($this->_processes as $pid) {
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
     * Check if tunnel is active
     * @param int $pid
     * @return bool
     */
    public function isTunnelActive($pid): bool
    {
        if (!$pid) {
            return false;
        }

        $result = exec("ps -p {$pid} > /dev/null 2>&1; echo $?");
        return (int)$result === 0;
    }

    public function __destruct()
    {
        $this->killAllTunnels();
    }
}
