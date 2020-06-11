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
        if(defined('YII_DEBUG') && YII_DEBUG){
            return parent::widget($config);
        }
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

        if(defined('YII_DEBUG') && YII_DEBUG){
            return parent::run();
        }

        try {
            return parent::run();
        } catch (Exception $err) {
            Yii::error($err->getMessage());
            return self::$errorMessage ?? Yii::t('d3system', 'Widget output error') . '<br>';
        }
    }
}
