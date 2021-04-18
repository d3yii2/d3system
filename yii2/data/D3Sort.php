<?php


namespace d3system\yii2\data;


use Yii;
use yii\data\Sort;
use yii\web\Request;

class D3Sort extends Sort
{
    private $actualAttributeOrders;
    /**
     * @var mixed
     */
    private $cacheData;

    public function getAttributeOrders($recalculate = false)
    {
        if ($this->actualAttributeOrders && !$recalculate) {
            return $this->actualAttributeOrders;
        }

        if (($params = $this->params) === null) {
            $request = Yii::$app->getRequest();
            $params = $request instanceof Request ? $request->getQueryParams() : [];
        }
        $this->actualAttributeOrders = [];
        if (!isset($params[$this->sortParam])) {
            $this->actualAttributeOrders = $this->getCacheData() ?? [];
        } else {

            foreach ($this->parseSortParam($params[$this->sortParam]) as $attribute) {
                $descending = false;
                if (strncmp($attribute, '-', 1) === 0) {
                    $descending = true;
                    $attribute = substr($attribute, 1);
                }

                if (isset($this->attributes[$attribute])) {
                    $this->actualAttributeOrders[$attribute] = $descending ? SORT_DESC : SORT_ASC;
                    if (!$this->enableMultiSort) {
                        break;
                    }
                }
            }
        }
        if (empty($this->actualAttributeOrders) && is_array($this->defaultOrder)) {
            $this->actualAttributeOrders = $this->defaultOrder;
        }

        $this->saveCacheData($this->actualAttributeOrders);
        return $this->actualAttributeOrders;
    }

    public function setAttributeOrders($attributeOrders, $validate = true)
    {
        if ($attributeOrders === null || !$validate) {
            $this->actualAttributeOrders = $attributeOrders;
        } else {
            $this->actualAttributeOrders = [];
            foreach ($attributeOrders as $attribute => $order) {
                if (isset($this->attributes[$attribute])) {
                    $this->actualAttributeOrders[$attribute] = $order;
                    if (!$this->enableMultiSort) {
                        break;
                    }
                }
            }
        }
        $this->saveCacheData($this->actualAttributeOrders);
    }

    public function getCacheData()
    {
        if ($this->cacheData) {
            return $this->cacheData;
        }
        return $this->cacheData = Yii::$app->cache->get($this->buildKey());
    }

    /**
     * @param array $data
     */
    private function saveCacheData(array $data): void
    {
        $this->cacheData = $data;
        Yii::$app->cache->set($this->buildKey(), $data, 1800);
    }

    private function buildKey(): string
    {
        $route = $this->route ?? Yii::$app->controller->getRoute();
        return 'D3Sort' . md5(
                $route
                . '-'
                . Yii::$app->SysCmp->getActiveCompanyId()
            );
    }
}