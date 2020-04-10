<?php


namespace d3system\yii2\data;



use yii\data\ActiveDataProvider;
use yii\db\QueryInterface;

class D3ActiveDataProvider extends ActiveDataProvider
{

    /**
     * @var QueryInterface the query that is used to fetch [[totalCount]]
     * if it is not explicitly set.
     */
    public $totalQuery;

    /** @var string[]  */
    public $totalAttributes = [];

    /** @var int[]|float[]  */
    private $totals = [];


    public function getTotalCount()
    {
        if(!$this->totalQuery || $this->totalAttributes) {
            return parent::getTotalCount();
        }
        if(!$this->totals){
            $this->loadTotals();
        }
        return $this->totals['countRows'];
    }

    /**
     * @param string $attribute
     * @return float|int
     */
    public function getAttributeTotal(string $attribute)
    {

        if(!$this->totals){
            $this->loadTotals();
        }
        return $this->totals[$attribute] ?? 0;

    }

    private function loadTotals(): void
    {
        if(!$this->totalQuery){
            $select = [
                'countRows' => 'count(*)'
            ];
            foreach($this->totalAttributes as $attribute){
                $select[$attribute] = 'SUM('.($this->query->select[$attribute]??$attribute).')';
            }
            $this->totalQuery = clone $this->query;
            $this->totalQuery->select($select);
        }
        $this->totals = $this->totalQuery->asArray()->one();
    }

    public function setColumnFooter(&$column): void
    {

        if(!isset($column['attribute'])){
            return ;
        }
        $attribute = $column['attribute'];
        if(!$this->totals){
            $this->loadTotals();
        }

        if(!isset($this->totals[$attribute])){
            return;
        }
        $column['footer'] = $this->getAttributeTotal($attribute);
    }
}