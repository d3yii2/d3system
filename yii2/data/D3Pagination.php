<?php


namespace d3system\yii2\data;


use Yii;
use yii\data\Pagination;

class D3Pagination extends Pagination
{

    /** @var array */
    private $cacheData;

    private $activePage;
    private $activePageSize;

    public function getPage($recalculate = false): int
    {
        if ($this->activePage !== null) {
            return $this->activePage;
        }

        $cacheData = $this->getCacheData();
        $pageParam = $this->pageParam;
        if ($this->getQueryParam($pageParam)) {
            $cacheData[$pageParam] = parent::getPage();
            $this->saveCacheData($cacheData);
        } elseif ($cacheData) {
            $this->setPage($cacheData[$pageParam] ?? parent::getPage());
        }

        return $this->activePage = parent::getPage();
    }

    public function getPageSize(): int
    {
        if ($this->activePageSize !== null) {
            return $this->activePageSize;
        }
        $cacheData = $this->getCacheData();
        $pageSizeParam = $this->pageSizeParam;
        if ($newSize = $this->getQueryParam($pageSizeParam)) {
            if (($cacheData[$pageSizeParam]??false) !== $newSize) {
                $cacheData[$pageSizeParam] = $newSize;
                /** mainoties pageSize, page noliek uz sakumu */
                $this->setPage(0);
                $cacheData[$this->pageParam] = parent::getPage();
                $this->saveCacheData($cacheData);
            }
        } elseif ($cacheData) {
            $this->setPageSize($cacheData[$pageSizeParam] ?? parent::getPageSize());
        }

        return $this->activePageSize = parent::getPageSize();
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
        return 'D3Pagination' . md5(
                $route
                . '-'
                . Yii::$app->SysCmp->getActiveCompanyId()
            );
    }


}