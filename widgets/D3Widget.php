<?php

namespace d3system\widgets;

use yii\base\Widget;
use Exception;
use Yii;

class D3Widget extends Widget
{
    public static $errorMessage;

    /**
     * @param array $config
     * @return string|void
     */
    public static function widget($config = [])
    {
        try {
            return parent::widget($config);
        } catch (Exception $err) {
            Yii::error($err->getMessage());
            return self::$errorMessage ?? Yii::t('d3system', 'Widget init failed') . '<br>';
        }
    }

    /**
     * @return string|void
     */
    public function run()
    {
        try {
            return parent::run();
        } catch (Exception $err) {
            Yii::error($err->getMessage());
            return self::$errorMessage ?? Yii::t('d3system', 'Widget output error') . '<br>';
        }
    }
}
