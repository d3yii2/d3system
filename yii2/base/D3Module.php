<?php


namespace d3system\yii2\base;


use yii\base\Module;

class D3Module extends Module
{
    public $configFilePath;

    /**
     * @var array panels for PanelWidgets
     */
    public $panels;

    public function __construct($id, $parent = null, $config = [])
    {
        if(isset($config['configFilePath'])){
            $config = array_merge($config,include $config['configFilePath']);
        }
        parent::__construct($id, $parent, $config);
    }

}