<?php

namespace d3system\commands;

use d3system\compnents\D3CommandTask;
use Yii;
use yii\db\Exception;

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
    public $loopTimeLimit = 60; //60 seconds
    
    /**
     * @var int $idleAfterSeconds
     */
    public $idleAfterSeconds = 60;
    
    /**
     * @var int $idleRequireReadSeconds
     */
    public $idleRequireReadSeconds = 60;
    
    /**
     * @var bool|null $recconectDb
     */
    public $recconectDb;
    
    //@TODO
    public $loopExitAfterSeconds = 20 * 60; //20 min
    public $statusReadLogSeconds = 60;
    
    /**
     * @var D3CommandTask $task
     * Tasks are extended in modules, e.g. d3yii2\d3printer\logic\tasks\FtpPrintTask
     */
    protected $task;
    /**
     * @var int
     */
    private $loopCnt = 0;
    /**
     * @var mixed
     */
    private $loopCntReconnectDb = 0;

    public int $memoryIncreasedPercents = 50;
    public ?int $memoryUsage = null;

    /**
     * @var true
     */
    private bool $isTerminated = false;

    public function init()
    {
        parent::init();
        set_time_limit(0);

        if (PHP_OS_FAMILY !== 'Windows') {
            declare(ticks = 1);
            pcntl_signal(SIGTERM, [$this, 'terminateSigterm'], false   );
            pcntl_signal(SIGINT, [$this, 'terminateSigint'], false   );
        }
    }

    public function terminateSigterm()
    {
        $this->out('Daemon terminated by SIGTERM.');
        $this->isTerminated = true;
    }

    public function terminateSigint()
    {
        $this->out('Daemon terminated by SIGINT.');
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
                Yii::error('memory max usage: ' . $maxMemoryUsage . ' actual:  ' . memory_get_usage() . ' exit');
                return false;
            }
        }
        return true;
    }
}
