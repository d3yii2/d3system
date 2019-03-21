<?php

namespace d3system\compnents;


use d3system\models\SysModels;
use yii\base\Component;
use yii\db\ActiveRecord;


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
    private $listTableNameId = [];

    /**
     * @var string[]
     */
    private $listClassNameId = [];

    public function init(): void
    {

        if (!$this->listTableNameId = \Yii::$app->cache->get($this->cacheKey.'ByTableName')) {
            $this->loadListFromDb();
        }
        $this->listClassNameId = \Yii::$app->cache->get($this->cacheKey.'ByClassName');

    }

    /**
     * @param ActiveRecord $model
     * @return int
     * @throws \d3system\exceptions\D3ActiveRecordException
     */
    public function getIdByTableName($model): int
    {
        if (isset($this->listTableNameId[$model->tableName()])) {
            return $this->listTableNameId[$model->tableName()];
        }
        SysModels::addRecord($model);
        $this->loadListFromDb();

        return $this->getIdByTableName($model);
    }

    /**
     * @param string $className
     * @return int
     * @throws \d3system\exceptions\D3ActiveRecordException
     */
    public function getIdByClassName(string $className): int
    {
        if (isset($this->listClassNameId[$className])) {
            return $this->listClassNameId[$className];
        }

        $model = new $className();
        SysModels::addRecord($model);
        $this->loadListFromDb();

        return $this->getIdByClassName($className);
    }

    private function loadListFromDb():void
    {
        $this->listTableNameId = SysModels::getTableNameIdList($this->cacheKey, $this->cacheDuration);
        $this->listClassNameId = \Yii::$app->cache->get($this->cacheKey.'ByClassName');
    }
}