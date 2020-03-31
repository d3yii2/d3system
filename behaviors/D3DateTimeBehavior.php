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
    public $targetFormat =   ['date', 'dd.MM.yyyy'];
    public $processRange = true;

    /**
     * @throws D3Exception
     */
    public function init()
    {
        if (empty($this->attributes)) {
            throw new D3Exception('D3DateTimeBehavior error: Attributes are not set');
        }

        parent::init();
    }

    /**
     * @param Event $event
     * @throws InvalidConfigException
     */
    public function onBeforeValidate($event): void
    {
        foreach ($this->attributeValues as $targetAttribute => $value) {
            if ($value instanceof DateTimeAttribute) {
                $validateAttr = true;

                if ($this->processRange) {
                    $originValue = $this->owner->{$value->originalAttribute . '_local'};
                    // Check for date range, format and merge it back
                    if ($originValue && false !== strpos($originValue, '-')) {
                        // Split value to separate dates
                        $dates = explode('-', $originValue);
                        // Check both dates are given
                        if (!empty($dates[0]) && !empty($dates[1])) {
                            // Convert date to table format
                            $formattedDate = $this->formatter->asDate(
                                $dates[0],
                                self::normalizeIcuFormat($value->originalFormat, $this->formatter)[1]
                            );
                            $formattedDate .= ' - ';
                            $formattedDate .= $this->formatter->asDate(
                                $dates[1],
                                self::normalizeIcuFormat($value->originalFormat, $this->formatter)[1]
                            );
                            $this->owner->{$value->originalAttribute} = $formattedDate;
                            $validateAttr = false;
                        }
                    }
                }

                if ($validateAttr) {
                    $validator = Yii::createObject([
                        'class' => DateValidator::class,
                        'format' => self::normalizeIcuFormat($value->targetFormat, $this->formatter)[1],
                    ]);
                    $validator->validateAttribute($this->owner, $targetAttribute);
                }
            }
        }
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
}
