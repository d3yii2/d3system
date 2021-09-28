<?php

namespace d3system\commands;

use d3system\compnents\D3CommandTask;
use Yii;

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


    /**
     * @throws \yii\db\Exception
     */
    public function loop(): bool
    {
        set_time_limit($this->loopTimeLimit);
        $this->loopCnt++;
        $this->loopCntReconnectDb ++;

        /**
         * ending every restartAfterSeconds minutes
         */
        if ($this->loopCnt > $this->loopExitAfterSeconds) {
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

        if (memory_get_usage() > $this->memoryLimit) {
            $this->out('memory limit reached: ' . $this->memoryLimit . ' actual:  ' . memory_get_usage() . ' exit');
            return false;
        }

        return true;
    }
}

