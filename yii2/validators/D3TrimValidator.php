<?php

namespace d3system\yii2\validators;

use yii\validators\TrimValidator;

class D3TrimValidator extends TrimValidator
{
    /** @var bool trim only string values */
    public bool $trimOnlyStringValues = false;

    /**
     * Converts a given value to string and strips declared characters.
     *
     * @param mixed $value the value to strip
     * @return string|array|object|null
     */
    protected function trimValue($value)
    {
        if ($this->trimOnlyStringValues && !is_string($value)) {
            return $value;
        }
        return $this->isEmpty($value) ? '' : trim((string)$value, $this->chars ?: " \n\r\t\v\x00");
    }
}
