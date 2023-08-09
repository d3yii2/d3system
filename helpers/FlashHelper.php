<?php

namespace d3system\helpers;

use Exception;
use Yii;
use yii\base\Model;

class FlashHelper
{
    public const TYPE_SUCCESS = 'success';
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_DANGER = 'danger';
    public const TYPE_DEFAULT = 'default';
    public const TYPE_LILAC = 'lilac';
    public const TYPE_INVERSE = 'inverse';
    public const TYPE_TEALS = 'teals';    
    
    /**
     * @param Exception $e
     * @param string $flashMessage message for displaying to user
     * @param string $errorMessage message for error log
     */
    public static function processException($e, string $flashMessage = '', string $errorMessage = ''): void
    {
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

        self::addWarning($flashMessage ?: $e->getMessage());
    }

    public static function addWarning(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_WARNING, $message, ['showStrongText' => $short]);
    }

    public static function addDanger(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_DANGER, $message, ['showStrongText' => $short]);
    }

    public static function addInfo(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_INFO, $message, ['showStrongText' => $short]);
    }

    public static function addDefault(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_DEFAULT, $message, ['showStrongText' => $short]);
    }

    public static function addSuccess(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_SUCCESS, $message, ['showStrongText' => $short]);
    }

    public static function addLilac(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_LILAC, $message, ['showStrongText' => $short]);
    }

    public static function addTeals(string $message, bool $short = false): void
    {
        self::addFlash(self::TYPE_TEALS, $message, ['showStrongText' => $short]);
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
