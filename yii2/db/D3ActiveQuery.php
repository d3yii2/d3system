<?php

namespace d3system\yii2\db;

use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class D3ActiveQuery
 * @package d3system\yii2\db
 */
class D3ActiveQuery extends ActiveQuery
{

    /**
     * Use for datetime 'to' value increment by 1 day
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
        [$from, $to] = $list;
        $expressionTo = new Expression('ADDDATE(\'' . $to . '\',1)');
        return $this->andFilterWhere(['between', $fieldName, $from, $expressionTo]);
    }

    /**
     * Use for date. $to not incremented by 1 day
     *
     * @param string $fieldName
     * @param string|null $dateRange
     * @return ActiveQuery
     */
    public function andFilterWhereDateStrictRange(string $fieldName, $dateRange): ActiveQuery
    {
        if (!$dateRange) {
            return $this;
        }

        $list = explode(' - ', $dateRange);
        if (count($list) !== 2) {
            return $this;
        }
        [$from, $to] = $list;
        return $this->andFilterWhere(['between', $fieldName, $from, $to]);
    }

    /**
     * @param $filterRange
     * @param $fieldName
     * @param ActiveQuery $query
     * @return bool
     */
    public static function addFilterDateRangeToHaving($filterRange, $fieldName, ActiveQuery $query): bool
    {
        if(empty($filterRange)){
            return false;
        }

        if(!strpos($filterRange, '-')){
            return false;
        }

        [$start_date, $end_date] = explode(' - ', $filterRange);
        $param = [
            ':start' => $start_date,
            ':end' => $end_date
        ];
        $rangeWhereSql = $fieldName . ' >=:start AND ' .$fieldName. ' < DATE_ADD(:end, INTERVAL 1 DAY)';
        $query->having($rangeWhereSql,$param);

        return true;

    }

    /**
     * @return string
     */
    public function getRawSql(): string
    {
        return $this->createCommand()->getRawSql();
    }
}