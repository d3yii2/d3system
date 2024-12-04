<?php

namespace d3system\helpers;

use eaBlankonThema\widget\ThAlert;
use Exception;
use Yii;
use yii\base\Model;
use yii\base\UserException;

class FlashHelper
{

    /**
     * @param Exception $e
     * @param string $flashMessage message for displaying to user
     * @param string $errorMessage message for error log
     */
    public static function processException($e, string $flashMessage = '', string $errorMessage = ''): void
    {
        self::addWarning($flashMessage ?: $e->getMessage());
        if (get_class($e) === UserException::class) {
            return;
        }
        $messageList = [];
        if ($errorMessage) {
            $messageList[] = 'ErrorMessage: ' . $errorMessage;
        }
        if ($flashMessage) {
            $messageList[] = 'FlashMessage: ' . $flashMessage;
        }
        $messageList[] = 'ExceptionMessage: ' . $e->getMessage();
        $messageList[] = $e->getTraceAsString();
        Yii::error(implode(PHP_EOL, $messageList));
    }

    public static function addWarning(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_WARNING, $message, ['showStrongText' => $short]);
    }

    public static function addDanger(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_DANGER, $message, ['showStrongText' => $short]);
    }

    public static function addInfo(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_INFO, $message, ['showStrongText' => $short]);
    }

    public static function addDefault(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_DEFAULT, $message, ['showStrongText' => $short]);
    }

    public static function addSuccess(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_SUCCESS, $message, ['showStrongText' => $short]);
    }

    public static function addLilac(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_LILAC, $message, ['showStrongText' => $short]);
    }

    public static function addTeals(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_TEALS, $message, ['showStrongText' => $short]);
    }

    public static function addFlash($type, $message, $options = null): void
    {
        $k = 0;
        $session = Yii::$app->session;
        while ($session->hasFlash(self::createKey($type, $k))) {
            $k++;
        }
        $options['body'] = $message;
        $session->addFlash(self::createKey($type, $k), $options);
    }

    public static function createKey($type, $k): string
    {
        return $type . '::' . $k;
    }

    public static function getTypeFromKey(string $key)
    {
        $list = explode('::', $key, 2);
        if (count($list) !== 2) {
            return $key;
        }

        return $list[0];
    }

    /**
     * @param $model Model
     * @return void
     */
    public static function modelErrorSummary($model): void
    {
        foreach ($model->getErrors() as $attribute => $errorList) {
            foreach ($errorList as $error) {
                self::addWarning($model->getAttributeLabel($attribute) . ': ' . $error);
            }
        }
    }
}
