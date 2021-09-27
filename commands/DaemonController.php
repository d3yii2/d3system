<?php

namespace d3system\commands;

use cewood\cwkalte\models\data\StatusData;
use d3system\exceptions\D3TaskException;
use d3system\compnents\D3CommandTask;
use DateTime;
use Yii;
use yii\console\ExitCode;


class DaemonController extends D3CommandController
{
    // @TODO - should be configurable
    private const MEMORY_LIMIT = 268435456; //256MB
    private const SLEEP_MICROSECONDS = 1 * 1000000; //1 second
    private const RESTART_SECONDS = 20 * 60; //20 min
    private const LOOP_TIME_LIMIT = 4; //60 sec
    private const IDLE_AFTER_SEC = 60;
    private const IDLE_REGUIRE_READ_SEC = 60;
    private const STATUS_READ_LOG_SEC = 60;
    
    public $recconectDb;
    
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
            set_time_limit(self::LOOP_TIME_LIMIT);
            $loopCnt++;
            /**
             * ending every 20 minutes
             */
            if ($loopCnt > self::RESTART_SECONDS) {
                $this->out('Exit for restart. $loopCnt=' . $loopCnt);
                return ExitCode::OK;
            }
            usleep(self::SLEEP_MICROSECONDS);
            if ($loopCnt % 60 === 0) {
                $this->out('');
                $this->out('memory usage: ' . memory_get_usage());
                $this->out('$loopCnt: ' . $loopCnt);
                
                if ($this->recconectDb) {
                    /**reconnect DB to avoid timeouts and server gone away errors   */
                    Yii::$app->db->close();
                    Yii::$app->db->open();
                }
            }
            if (memory_get_usage() > self::MEMORY_LIMIT) {
                $this->out('memory limit reached: ' . self::MEMORY_LIMIT . ' actual:  ' . memory_get_usage() . ' exit');
                return ExitCode::OK;
            }
            
            $isIdle = ($now->getTimestamp() - $lastPlateCountChange->getTimestamp()) > self::IDLE_AFTER_SEC;
            
            $requireRead = ($now->getTimestamp() - $lastStatusReadTime->getTimestamp()) > self::IDLE_REGUIRE_READ_SEC;
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
            } catch (\Exception $e) {
                Yii::error($e->getMessage());
                $this->out($e->getMessage());
                $this->out(get_class($e));
                $this->out($e->getTraceAsString());
                unset($e);
                continue;
            }
        }
    }
    
    /**
     * @return \d3system\compnents\D3CommandTask
     */
    public function getTask(): D3CommandTask
    {
        return new D3CommandTask($this);
    }
}

