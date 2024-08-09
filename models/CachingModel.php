<?php

namespace d3system\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;


/**
 *
 * @property-read array $logData
 * @property-read null $cache
 */
class CachingModel extends Model
{

   /** config */
    public string $cacheComponentName = 'cache';

    public ?self $prev = null;


    /**
     * @throws \Exception
     */
    public function loadData(array $data): void
    {
        foreach ($this->attributes() as $attributeName) {
            if (!isset($data[$attributeName])) {
                continue;
            }
            if ($attributeName === 'prev') {
                $prev = clone $this;
                foreach ($prev->attributes() as $prevAttributeName) {
                    if ($prevAttributeName === 'prev') {
                        continue;
                    }
                    if (isset($data['prev'][$prevAttributeName])) {
                        $prev->$prevAttributeName = $data['prev'][$prevAttributeName];
                    }
                }
                $this->prev = $prev;
                continue;
            }
            $this->$attributeName = $data[$attributeName];

        }
    }

    /**
     * @throws Exception
     */
    public function ignore(): bool
    {
        if (!$prevModel = $this->getCache()) {
            return false;
        }
        foreach ($this->attributes() as $attributeName) {
            if ($attributeName === 'prev') {
                continue;
            }
            if ($this->$attributeName !== $prevModel->$attributeName) {
                return false;
            }
        }
        return true;
    }

    public function getLogData(): array
    {
        $attributes = $this->attributes;
        unset($attributes['cacheComponentName']);
        if ($this->prev) {
            $attributes['prev'] = $this->prev->attributes;
            unset($attributes['prev']['prev'], $attributes['prev']['cacheComponentName']);
        }
        return $attributes;
    }

    public function setCache(): void
    {
        $this->prev = clone $this;
        $this->prev->prev = null;
        Yii::$app
            ->{$this->cacheComponentName}
            ->set(get_class($this),$this->attributes);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function getCache()
    {
        if ($this->prev) {
            return $this->prev;
        }
        if (!$data = Yii::$app
            ->{$this->cacheComponentName}
            ->get(get_class($this))
        ) {
            return null;
        }
        $class = static::class;
        $this->prev = new $class();
        $this->prev->loadData($data);
        return $this->prev;
    }

    /**
     * @throws \Exception
     */
    public function restoreFromCache(): void
    {
        if (!$data = Yii::$app
            ->{$this->cacheComponentName}
            ->get(get_class($this))
        ) {
            return;
        }

        $this->loadData($data);
    }

    public function deleteCache(): void
    {
        Yii::$app
            ->{$this->cacheComponentName}
            ->delete(get_class($this));
    }
}
