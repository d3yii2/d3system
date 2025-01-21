

## Controller
```php
namespace d3yii2\d3printer\controllers;

use d3system\commands\DaemonController;
use d3system\exceptions\D3TaskException;
use d3system\helpers\D3FileHelper;
use d3yii2\d3printer\logic\D3PrinterException;

use Exception;
use Yii;
use yii\console\ExitCode;



class FtpPrintDaemonController extends DaemonController
{

    /**
     * @throws D3TaskException|\yii\db\Exception
     * @throws \yii\base\Exception
     */
    public function actionIndex(
        string $parma1,
        string $param2 = null
    ): int
    {
        /** process settings */
        $this->loopTimeLimit = 30;
        $this->loopExitAfterSeconds = 0;
        $this->memoryUsage = 30;

        $error = false;
        while ($this->loop()) {
            try {
               /**
               *  code
               */
            } catch (D3PrinterException $e) {
                $this->stdout('!');
            } catch (Exception $e) {
                if($e->getMessage() === (string)$error) {
                    $this->stdout('!');
                } else {
                    $error = get_class($e) . ': ' . $e->getMessage();
                    $this->out('');
                    $this->out(date('Y-m-d H:i:s') . ' ' . $error);
                    Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
            }
        }
        return ExitCode::OK;
    }
}
```

## deamon
```editorconfig
[Unit]
Description=BouncerPrinter

[Service]
User=www-data
# TimeoutStartSec=30
Restart=on-failure
Restart=always
RestartSec=30s
#ExecStartPre=
#ExecStart=/bin/bash /home/chroot/websites/xxxxx/bin/BauncerPrinterPrintDeamon > /home/chroot/websites/xxxxx/runtime/logs/d3printer/atveidnotajs.log
ExecStart=/bin/bash php /home/chroot/websites/xxxxx/yii d3printer/ftp-print-daemon bouncerPrinter >>  /home/chroot/websites/xxxxx/runtime/logs/d3printer/bouncer.log
SyslogIdentifier=BouncerPrinter
#ExecStop=

[Install]
WantedBy=multi-user.target
```