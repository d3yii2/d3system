<?php

namespace d3system\commands;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use d3logger\D3Monolog;

class DaemonController extends D3CommandController
{
    /**
     * @var int $memoryLimit
     */
    public $memoryLimit = 268435456; //256MB

    /**
     * @var float|int $sleepAfterMicroseconds
     */
    public $sleepAfterMicroseconds = 10 * 1000000; //10 seconds

    /**
     * @var int $loopTimeLimit
     */
    public int $loopTimeLimit = 60; //60 seconds

    /**
     * @var int $idleAfterSeconds
     */
    public int $idleAfterSeconds = 60;

    /**
     * @var int $idleRequireReadSeconds
     */
    public int $idleRequireReadSeconds = 60;

    /**
     * @var bool|null $recconectDb
     */
    public ?bool $recconectDb = null;

    //@TODO
    public $loopExitAfterSeconds = 20 * 60; //20 min

    public string $monoLogRuntimeDirectory = 'logs/daemon';
    public ?string $monoLogName = null;
    public string $monoLogFileName = 'daemon';
    public int $monoLogMaxFiles = 7;
    public ?D3Monolog $mLogCompnent = null;


    /**
     * @var int
     */
    private int $loopCnt = 0;
    /**
     * @var mixed
     */
    private $loopCntReconnectDb = 0;

    public int $memoryIncreasedPercents = 50;
    public ?int $memoryUsage = null;

    /**
     * @var true
     */
    public bool $isTerminated = false;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        set_time_limit(0);

        if (PHP_OS_FAMILY !== 'Windows') {
            declare(ticks = 1);
            pcntl_signal(SIGTERM, [$this, 'terminateSigterm'], false   );
            pcntl_signal(SIGINT, [$this, 'terminateSigint'], false   );
        }

        if ($this->monoLogName) {
            $this->mLogCompnent = Yii::createObject([
                'class' => D3Monolog::class,
                'name' => $this->monoLogName,
                'fileName' => $this->monoLogFileName,
                'directory' => $this->monoLogRuntimeDirectory,
                'maxFiles' => $this->monoLogMaxFiles,
            ]);
        }
    }

    public function mLogInfo($message, $context = []): void
    {
        if ($this->mLogCompnent) {
            $this->mLogCompnent->info($message, $context);
        }
    }

    public function terminateSigterm(): void
    {
        $this->out('Daemon terminated by SIGTERM.');
        $this->mLogInfo('Daemon terminated by SIGTERM.');
        $this->isTerminated = true;
    }

    public function terminateSigint(): void
    {
        $this->out('Daemon terminated by SIGINT.');
        $this->mLogInfo('Daemon terminated by SIGINT.');
        $this->isTerminated = true;
    }

    /**
     * @throws Exception
     */
    public function loop(): bool
    {
        if ($this->isTerminated) {
            return false;
        }
        if ($this->loopTimeLimit) {
            set_time_limit($this->loopTimeLimit);
        }
        $this->loopCnt++;
        if ($this->loopCnt < 2) {
            return true;
        }
        $this->loopCntReconnectDb ++;

        /**
         * ending every restartAfterSeconds minutes
         */
        if ($this->loopExitAfterSeconds
            && $this->loopCnt > $this->loopExitAfterSeconds
        ) {
            $this->out('Exit for restart. $loopCnt=' . $this->loopCnt);
            return false;
        }

        usleep($this->sleepAfterMicroseconds);

        if ($this->loopCntReconnectDb > 60) {
            $this->loopCntReconnectDb = 0;
            $this->out('');
            $this->out('memory usage: ' . memory_get_usage());
            $this->out('$loopCnt: ' . $this->loopCnt);

            Yii::$app->db->close();
            Yii::$app->db->open();
        }

        /**
         * if memory usage increased by 50 percents, demon restarted
         */

        if (!$this->memoryUsage) {
            $this->memoryUsage = memory_get_usage();
            $this->out('initial memory usage: ' . $this->memoryUsage);
        } else {
            $maxMemoryUsage = $this->memoryUsage * (100 + $this->memoryIncreasedPercents)/100;
            if (memory_get_usage() > $maxMemoryUsage) {
                $this->out('loopCnt: ' . $this->loopCnt .'. Memory max usage: ' . $maxMemoryUsage . ' actual:  ' . memory_get_usage() . ' exit');
                $this->out('$loopCnt: ' . $this->loopCnt);
                return false;
            }
        }
        return true;
    }

    public function sleep(int $seconds): void
    {
        while($seconds > 0){
            $seconds--;
            usleep(1000000);
            if ($this->isTerminated) {
                $this->mLogInfo('Sleep terminated.');
                return;
            }
        }
    }
}
