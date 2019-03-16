<?php

namespace d3system\compnents;


use d3system\models\SysModels;
use yii\base\Component;


class ModelsList extends Component
{

    /**
     * @var string cashing key
     */
    public $cacheKey = 'd3system\modeList';

    /**
     * @var int cashing duration
     */
    public $cacheDuration = 3600;

    /**
     * @var string[]
     */
    private $list = [];

    public function init()
    {

        if (!$this->list = \Yii::$app->cache->get($this->cacheKey)) {
            $this->loadListFromDb();
        }
    }

    public function getIdByTableName($model): int
    {
        if (isset($this->list[$model->tableName()])) {
            return $this->list[$model->tableName()];
        }
        SysModels::addRecord($model);
        $this->loadListFromDb();

        return $this->getIdByTableName($model);
    }

    private function loadListFromDb():void
    {
        $this->list = SysModels::getTableNameIdList($this->cacheKey, $this->cacheDuration);
    }
}