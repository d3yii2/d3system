<?php
namespace d3system\compnents;

use Yii;
use Exception;
use yii\base\Widget;

class TryCatch
{
    /**
     * @param $function
     * @param null $displayMessage
     * @return mixed
     */
    public static function function($function, $displayMessage = null)
    {
        try {
            return $function();
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            echo $displayMessage ?? Yii::t('d3system', 'Unexpected Server Error');
        }
    }

    /**
     * @param string $class
     * @param array $params
     * @param string|null $displayMessage
     * @return mixed
     */

    public static function widget(string $class, array $params = [], ?string $displayMessage = null)
    {
        try {
            /** @var Widget $class */
            return $class::widget($params);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return $displayMessage ?? Yii::t('d3system', 'Content error') . '<br>';
        }
    }
}