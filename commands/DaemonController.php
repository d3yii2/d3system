<?php

namespace d3system\commands;

use cewood\cwkalte\models\data\StatusData;
use d3system\exceptions\D3TaskException;
use d3system\compnents\D3CommandTask;
use DateTime;
use Exception;
use Yii;
use yii\console\ExitCode;


class DaemonController extends D3CommandController
{
    /**
     * @var int $memoryLimit
     */
    public $memoryLimit = 268435456; //256MB
    
    /**
     * @var float|int $sleepAfterMicroseconds
     */
    public $sleepAfterMicroseconds = 1 * 1000000; //1 second
    
    /**
     * @var int $loopTimeLimit
     */
    public $loopTimeLimit = 4; //60 sec
    
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
    public $restartAfterSeconds = 20 * 60; //20 min
    public $statusReadLogSeconds = 60;
    
    /**
     * @var D3CommandTask $task
     * Tasks are extended in modules, e.g. d3yii2\d3printer\logic\tasks\FtpPrintTask
     */
    protected $task;
    
    /**
     * daemon. need to add to cron where every 5 seconds/minutes check if daemon still runs. if not run command again
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionIndex(): int
    {
        $this->task = $this->getTask();
        
        ini_set('memory_limit', '300M');
        
        $loopCnt = 0;
        $lastPlateCountChange = new DateTime();
        $lastStatusReadTime = new DateTime();
        
        /** @var StatusData $statusPrevResetet */
        while (true) {
            
            /**
             * Maximal time limit for loop execution
             */
            $now = new DateTime();
            set_time_limit($this->loopTimeLimit);
            $loopCnt++;
            
            /**
             * ending every 20 minutes
             */
            if ($loopCnt > $this->restartAfterSeconds) {
                $this->out('Exit for restart. $loopCnt=' . $loopCnt);
                return ExitCode::OK;
            }
            
            usleep($this->sleepAfterMicroseconds);
            
            if (0 === $loopCnt % 60) {
                $this->out('');
                $this->out('memory usage: ' . memory_get_usage());
                $this->out('$loopCnt: ' . $loopCnt);
                
                if ($this->recconectDb) {
                    /**reconnect DB to avoid timeouts and server gone away errors   */
                    Yii::$app->db->close();
                    Yii::$app->db->open();
                }
            }
            
            if (memory_get_usage() > $this->memoryLimit) {
                $this->out('memory limit reached: ' . $this->memoryLimit . ' actual:  ' . memory_get_usage() . ' exit');
                return ExitCode::OK;
            }
            
            $isIdle = ($now->getTimestamp() - $lastPlateCountChange->getTimestamp()) > $this->idleAfterSeconds;
            $requireRead = ($now->getTimestamp() - $lastStatusReadTime->getTimestamp()) > $this->idleRequireReadSeconds;
            if ($isIdle && !$requireRead) {
                $this->stdout('.');
                continue;
            }
            
            try {
                $this->task->execute();
            } catch (D3TaskException $e) {
                $message = $e->getMessage();
                $this->out('TaskException: ' . $message);
                Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                
                // @TODO Add task caches?
                /*if($cacheError = Yii::$app->cache->get('DaemonTaskError')){
                    if($cacheError['message'] === $message){
                        $cacheError['cnt'] ++;
                    }else{
                        $cacheError = false;
                    }
                }
                if(!$cacheError){
                    $cacheError = [
                        'message' => $message,
                        'cnt' => 1
                    ];
                }
                if($cacheError['cnt'] === 1 || $cacheError['cnt'] % 100 === 0){
                    Yii::error('Daemon Exception: ' . $message . ' cnt: ' . $cacheError['cnt']);
                }
                Yii::$app->cache->set('PrinterDaemonTaskError',$cacheError,60);
                unset($e);*/
                continue;
            } catch (Exception $e) {
                $errMsg = $e->getMessage() . PHP_EOL . get_class($e) . PHP_EOL . $e->getTraceAsString();
                
                Yii::error($errMsg);
                $this->out($errMsg);
                unset($e);
                continue;
            }
        }
    }
    
    /**
     * @return D3CommandTask
     */
    public function getTask(): D3CommandTask
    {
        return new D3CommandTask($this);
    }
}

