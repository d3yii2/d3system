<?php

namespace d3system\yii2\db;

use yii\db\ActiveQuery;

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
    public function andFilterWhereDateRange(string $fieldName, ?string $dateRange): ActiveQuery
    {
        if (!$dateRange) {
            return $this;
        }

        $list = explode(' - ', $dateRange);
        if (count($list) !== 2) {
            return $this;
        }
        [$from, $to] = $list;
        return $this->andFilterWhere(['between', $fieldName, $from, $to . '  23:59:59']);
    }

    /**
     * Use for date. $to not incremented by 1 day
     *
     * @param string $fieldName
     * @param string|null $dateRange
     * @return ActiveQuery
     */
    public function andFilterWhereDateStrictRange(string $fieldName, ?string $dateRange): ActiveQuery
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
        if (empty($filterRange)) {
            return false;
        }

        if (!strpos($filterRange, '-')) {
            return false;
        }

        [$start_date, $end_date] = explode(' - ', $filterRange);
        $param = [
            ':start' => $start_date,
            ':end' => $end_date
        ];
        $rangeWhereSql = $fieldName . ' >=:start AND ' . $fieldName . ' < DATE_ADD(:end, INTERVAL 1 DAY)';
        $query->having($rangeWhereSql, $param);
        return true;
    }

    /**
     * @return string
     */
    public function getRawSql(): string
    {
        return $this->createCommand()->getRawSql();
    }

    /**
     * extended method andAndFilterWhere(['like', fieldName, value])
     * @param string $fieldName sql statement field name
     * @param string|null $value filter value. If
     *  - null - ignore
     *  - with "," for example "value1,value2,..."  - create sql statement {fieldName} in (value1,value2,...)
     *  - without "," - create sql statement {fieldName} like "%value%"
     * @return $this|self
     */
    public function andFilterWhereLikeCsv(string $fieldName, ?string $value): self
    {
        if (!$value) {
            return $this;
        }
        if (strpos($value, ',') !== false) {
            $list = explode(',', $value);
            foreach ($list as $key => $item) {
                if (!$item = trim($item)) {
                    unset($list[$key]);
                    continue;
                }
                $list[$key] = $item;
            }
            return $this->andWhere([$fieldName => $list]);
        }
        return $this->andWhere(['like', $fieldName, $value]);
    }
}
