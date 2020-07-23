<?php
namespace d3system\behaviors;

use d3system\exceptions\D3Exception;
use omnilight\datetime\DateTimeAttribute;
use omnilight\datetime\DateTimeBehavior;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\validators\DateValidator;

/**
 * Class DateTimeBehavior
 * @package d3system\behaviors
 */
class D3DateTimeBehavior extends DateTimeBehavior
{
    public $originalFormat = ['date', 'yyyy-MM-dd'];
    public $processRange = true;
    public $targetFormat = 'date';

    /**
     * @throws D3Exception
     */
    public function init()
    {
        if (empty($this->attributes)) {
            throw new D3Exception('D3DateTimeBehavior error: Attributes are not set');
        }

        if ($this->processRange) {
            $this->attributeConfig = ['class' => 'omnilight\datetime\DateTimeRangeAttribute'];
        }

        parent::init();
    }

    /**
     * @param array $attributes
     * @return array|array[]
     */
    public static function getConfig(array $attributes): array
    {
        return [
            'd3date' => [
                'class' => self::class,
                'attributes' => $attributes,
            ],
        ];
    }

    /**
     * @param array $behaviors
     * @param array $attributes
     * @return array|array[]
     */
    public static function addConfigAttributes(array $behaviors,array $attributes): array
    {
        if(!isset($behaviors['d3date'])){
            $behaviors['d3date'] = [
                'class' => self::class,
                'attributes' => $attributes,
            ];
            return $behaviors;
        }
        foreach ($attributes as $attribute) {
            $behaviors['d3date']['attributes'][] = $attribute;
        }
        return $behaviors;
    }
}
