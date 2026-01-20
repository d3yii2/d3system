<?php


namespace d3system\yii2\log;


use Throwable;
use yii\helpers\VarDumper;
use yii\log\FileTarget;
use yii\log\Logger;
use yii\web\Request;
use yii;

class D3FileTarget extends FileTarget
{
    public $showMessageInfo = [
        'time',
        'ip',
        'user',
        'sysCompany',
        'level',
        'category',
        'text',
        'trace'
    ];
    public function formatMessage($message): string
    {
        [$text, $level, $category, $timestamp] = $message;
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Exception || $text instanceof Throwable) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $rowText = '';

        if($this->isShowInfo('time')) {
            $rowText .= $this->getTime($timestamp);
        }

        if(yii::$app !== null) {
            if ($this->isShowInfo('ip')) {
                $request = Yii::$app->getRequest();
                $ip = $request instanceof Request ? $request->getUserIP() : '-';
                $rowText .= '[' . $ip . ']';
            }

            if ($this->isShowInfo('user')) {
                /* @var $user \yii\web\User */
                $user = yii::$app->has('user', true) ? Yii::$app->get('user') : null;
                if ($user && ($identity = $user->getIdentity(false))) {
                    $userID = $identity->username;
                } else {
                    $userID = '-';
                }
                $rowText .= '[' . $userID . ']';
            }

            if ($this->isShowInfo('sysCompany')) {
                /* @var $user \yii\web\User */
                if($sysCompany = Yii::$app->has('SysCmp') ? Yii::$app->get('SysCmp') : null){
                    $rowText .= '[' . $sysCompany->getActiveCompanyName() . ']';
                }else{
                    $rowText .= '[-]';
                }
            }
        }

        if($this->isShowInfo('level')){
            $rowText .= '[' . Logger::getLevelName($level) . ']';
        }
        if($this->isShowInfo('category')){
            $rowText .= '[' . $category . ']';
        }
        if($this->isShowInfo('text')){
            $rowText .= ' ' . $text;
        }
        if($this->isShowInfo('trace') && isset($message[4])){
            $traces = [];
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
            if(!empty($trace)) {
                $rowText .= "\n    " . implode("\n    ", $traces);
            }
        }
        return $rowText;
    }

    public function isShowInfo(string $info)
    {
        return in_array($info,$this->showMessageInfo);
    }

}