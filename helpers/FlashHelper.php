<?php

namespace d3system\helpers;

use d3system\exceptions\D3UserAlertException;
use eaBlankonThema\widget\ThAlert;
use eaBlankonThema\widget\ThExternalLink;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\base\UserException;
use yii\helpers\Json;


class FlashHelper
{
    private const LOGGING_CATEGORY_FLASH = 'FlashHelperFlash';
    private const LOGGING_CATEGORY_ERROR = 'FlashHelperError';

    /**
     * use in controllers in catch of exception
     * user exceptions are logged as info without trace, but other as error
     * @param object $e
     * @param string|null $flashMessage message for displaying to a user
     * @param string|null $errorMessage message for error log
     */
    public static function processException(object $e, ?string $flashMessage = null, ?string $errorMessage = null): void
    {
        $exceptionClass = get_class($e);
        $exceptionMessage = $e->getMessage();
        $isUserException = $exceptionClass === UserException::class
            || $exceptionClass === D3UserAlertException::class;

        self::addFlash(
            ThAlert::TYPE_DANGER,
            $flashMessage ?? $exceptionMessage
        );

        $messageList = [];
        if ($errorMessage) {
            $messageList[] = 'ErrorMessage: ' . $errorMessage;
        }
        if ($flashMessage) {
            $messageList[] = 'FlashMessage: ' . $flashMessage;
        }
        $messageList[] = 'ClassName: ' . $exceptionClass;
        if ($exceptionMessage) {
            $messageList[] = 'ExceptionMessage: ' . $exceptionMessage;
        }
        if (!$isUserException) {
            $messageList[] = $e->getTraceAsString();
        }
        $message = implode(PHP_EOL, $messageList);
        if ($isUserException) {
            Yii::warning(
                [
                    'msg' => $message,
                    'tags' => ['level' => 'warning'],
                ],
                self::LOGGING_CATEGORY_FLASH
            );
        } else {
            Yii::error(
                $message,
                self::LOGGING_CATEGORY_ERROR
            );
        }
    }

    public static function addWarning(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_WARNING, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'warning'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addDanger(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_DANGER, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'error'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addError(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_DANGER, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'error'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addInfo(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_INFO, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'info'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addDefault(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_DEFAULT, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'default'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addSuccess(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_SUCCESS, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'success'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addLilac(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_LILAC, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'lilac'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addTeals(string $message, bool $short = false): void
    {
        self::addFlash(ThAlert::TYPE_TEALS, $message, ['showStrongText' => $short]);
        Yii::warning(
            [
                'msg' => $message,
                'tags' => ['level' => 'teals'],
            ],
            self::LOGGING_CATEGORY_FLASH
        );
    }

    public static function addFlash($type, $message, $options = null): void
    {
        $k = 0;
        $session = Yii::$app->session;
        while ($session->hasFlash(self::createKey($type, $k))) {
            $k++;
        }
        $options['body'] = self::decodeMessage($message);
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
     * @param $model Model|object
     * @return void
     */
    public static function modelErrorSummary(object $model): void
    {
        foreach ($model->getErrors() as $attribute => $errorList) {
            foreach ($errorList as $error) {
                self::addWarning($model->getAttributeLabel($attribute) . ': ' . $error);
            }
        }
    }

    private static function decodeMessage(string $message): string
    {
        try {
            $decodedMessage = Json::decode($message);
            if (isset($decodedMessage['ExternalLink'])) {
                $message = $decodedMessage['message'] .
                    ' ' .
                    ThExternalLink::widget([
                        'text' => $decodedMessage['ExternalLink']['text'],
                        'url' => $decodedMessage['ExternalLink']['url'],
                    ]);
            }
            return $message;
        } catch (InvalidArgumentException $e) {
            return $message;
        }
    }
}
