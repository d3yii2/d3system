<?php


namespace d3system\yii2\base;


use d3yii2\d3activity\components\ActivityRegistar;
use d3yii2\d3activity\components\DummyActivityRegistar;
use yii\base\Module;

/**
 * Class D3Module
 * @package d3system\yii2\base
 * @property ActivityRegistar $activityRegistar by default set DummyActivityRegistar
 */

class D3Module extends Module
{
    public $configFilePath;


    /**
     * @var array panels for PanelWidgets
     */
    public $panels;

    /**
     * @var define Left Menu code or class
     */
    public $leftMenu;

    public function __construct($id, $parent = null, $config = [])
    {
        if(isset($config['configFilePath'])){
            $config = array_merge($config,include $config['configFilePath']);
        }

        /**
         * if in config no defined component activityRegistar, set dummy registar
         */
        if(!isset($config['components']['activityRegistar']) && !\Yii::$app->has('activityRegistar')){
            $config['components']['activityRegistar'] = DummyActivityRegistar::class;
        }
        parent::__construct($id, $parent, $config);


    }

}