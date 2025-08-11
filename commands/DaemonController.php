<?php

namespace d3system\commands;

use d3system\compnents\D3CommandTask;
use Yii;
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

    public string $monoLogRuntimeDirectory = 'logs/daemon';
    public ?string $monoLogName = null;
    public string $monoLogFileName = 'daemon';
    public int $monoLogMaxFiles = 7;
    private ?D3Monolog $mLogCompnent = null;

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

    public function mLogInfo($message, $context): void
    {
        if ($this->mLogCompnent) {
            $this->mLogCompnent->info($message, $context);
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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->mLogInfo(
            'beforeAction',
            [
                'action' => $action,
                'class' => get_class($action->controller),
            ]
        );
        return true;
    }

    public function afterAction($action, $result)
    {
        $this->mLogInfo(
            'beforeAction',
            [
                'action' => $action,
            ]
        );
        return parent::afterAction($action, $result);
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
}
