<?php


namespace d3system\yii2\data;



use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\QueryInterface;

/**
 * @deprecated all can do in search mosel:
 * private  $totalQuery = null;
 * public ?self $totalsRow = null;
 *
 * $query = $this->query();
 * if (!$this->totalsRow = $this->totalQuery->one()) {
 *    throw new Exception('No totals');
 * }
 * return new ActiveDataProvider([
 *    'query' => $query,
 *    'totalCount' => $this->totalsRow->countRows,
 * ]);
 *
 * @property-write mixed $columnFooter
 */
class D3ActiveDataProvider extends ActiveDataProvider
{

    /**
     * @var QueryInterface the query that is used to fetch [[totalCount]]
     * if it is not explicitly set.
     * Example:
     *   $totalQuery = (clone $query)
     *     ->select(['COUNT(DISTINCT cwbr_product.id) AS countRows']);
     *  return new D3ActiveDataProvider([
     *       'totalQuery' => $totalQuery
     *       'query' => $query,
     *   ]);
     */
    public $totalQuery;

    /** @var string[]  */
    public array $totalAttributes = [];

    /** @var int[]|float[]  */
    private array $totals = [];

    /**
     * @throws InvalidConfigException
     */
    public function setPagination($value)
    {
        if (is_array($value)) {
            $config = ['class' => D3Pagination::class];
            if ($this->id !== null) {
                $config['pageParam'] = $this->id . '-page';
                $config['pageSizeParam'] = $this->id . '-per-page';
            }
            $pagination = Yii::createObject(array_merge($config, $value));
            parent::setPagination($pagination);
            return;
        }
        parent::setPagination($value);

    }

    /**
     * @throws InvalidConfigException
     */
    public function setSort($value)
    {
        if (is_array($value)) {
            $config = ['class' =>D3Sort::class];
            if ($this->id !== null) {
                $config['sortParam'] = $this->id . '-sort';
            }
            $sort = Yii::createObject(array_merge($config, $value));
            parent::setSort($sort);
            return;
        }
        parent::setSort($value);
    }

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
            $this->totalQuery->groupBy = null;
        }
        $this->totals = $this->totalQuery->asArray()->one();
    }

    public function setColumnFooter(&$column): void
    {

        if(!isset($column['attribute'])){
            return ;
        }
        $attribute = $column['attribute'];
        if(!in_array($attribute,$this->totalAttributes, true)){
            return;
        }
        if(!$this->totals){
            $this->loadTotals();
        }

        if(!isset($this->totals[$attribute])){
            return;
        }
        $column['footer'] = $this->getAttributeTotal($attribute);
        if(!isset($column['footerOptions'])) {
            $column['footerOptions'] = [
                'class' => 'decimal'
            ];
        }
    }
}