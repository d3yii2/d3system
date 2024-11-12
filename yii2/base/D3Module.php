<?php

namespace d3system\yii2\base;

use d3yii2\d3activity\components\ActivityRegistar;
use d3yii2\d3activity\components\DummyActivityRegistar;
use Yii;
use yii\base\Module;

/**
 * Class D3Module
 * @package d3system\yii2\base
 * @property ActivityRegistar $activityRegistar by default set DummyActivityRegistar
 */

class D3Module extends Module
{
	//Default themes
	const THEME_BLANKON = 'blankon';

	const THEME_ARGON = 'argon';

    public $configFilePath;


    /**
     * @var array panels for PanelWidgets
     */
    public $panels;

    /**
     * @var string Left Menu code or class
     */
    public $leftMenu;

	/**
	 * @var string Theme name
	 */
    public $theme = self::THEME_BLANKON;

    public function __construct($id, $parent = null, $config = [])
    {

        /** load config from module config file */
        if(isset($config['configFilePath'])){
            $configFileData = include $config['configFilePath'];
            if (isset($configFileData['class'])) {
                unset($configFileData['class']);
            }
            $config = array_merge($config, $configFileData);
        }

        /**
         * if in config no defined component activityRegistar, set dummy registar
         */
        if(!isset($config['components']['activityRegistar']) && !Yii::$app->has('activityRegistar')){
            $config['components']['activityRegistar'] = DummyActivityRegistar::class;
        }
        parent::__construct($id, $parent, $config);


    }

    public function init()
    {
        parent::init();

        if ($this->theme !== self::THEME_BLANKON) {
            $this->viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views-' . $this->theme;
        }        
    }
}
