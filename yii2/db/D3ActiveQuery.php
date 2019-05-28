<?php

namespace d3system\yii2\db;

use yii\db\ActiveQuery;

/**
 * Class D3ActiveQuery
 * @package d3system\yii2\db
 */
class D3ActiveQuery extends \yii\db\ActiveQuery
{

    /**
     * @param string $fieldName
     * @param string|null $dateRange
     * @return ActiveQuery
     */
    public function andFilterWhereDateRange(string $fieldName, $dateRange): ActiveQuery
    {
        if (!$dateRange) {
            return $this;
        }

        $list = explode(' - ', $dateRange);
        if (count($list) !== 2) {
            return $this;
        }
        list($from, $to) = $list;
        $expressionTo = new Expression('ADDDATE(\'' . $to . '\',1)');
        return $this->andFilterWhere(['between', $fieldName, $from, $expressionTo]);
    }

    /**
     * @param $filterRange
     * @param $fieldName
     * @param ActiveQuery $query
     * @return bool
     */
    public static function addFilterDateRangeToHaving($filterRange, $fieldName, ActiveQuery &$query): bool
    {
        if(empty($filterRange)){
            return false;
        }

        if(!strpos($filterRange, '-')){
            return false;
        }

        list($start_date, $end_date) = explode(' - ', $filterRange);
        $param = [
            ':start' => $start_date,
            ':end' => $end_date
        ];
        $rangeWhereSql = $fieldName . " >=:start AND ".$fieldName." < DATE_ADD(:end, INTERVAL 1 DAY)";
        $query->having($rangeWhereSql,$param);

        return true;

    }

    /**
     * @param ActiveQuery $query
     * @return string
     */
    public static function getRawSql(ActiveQuery $query): string
    {
        $sql = $query->createCommand()->getRawSql();

        return $sql;
    }
}