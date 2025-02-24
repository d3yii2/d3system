<?php

namespace d3system\models;

class ModelHelper
{
    /**
     * A helper method to normalize float attributes.
     * Ensures attributes are either 0 or valid floats.
     *
     * implementing:
     * ------------------------------------------------------------------------------
     * private const FLOAT_ATTRIBUTES = ['fee','fee_amount','recipient_fee','recipient_fee_amount'];
     *
     * public function rules(): ?array
     * {
     *      return array_merge(
     *          parent::rules(),
     *          [
     *              [self::FLOAT_ATTRIBUTES,'number']
     *          ]
     *      );
     * }
     *
     * public function load($data, $formName = null): bool
     * {
     *      $scope = $formName ?? $this->formName();
     *      $attributes = &$data[$scope];
     *      ModelHelper::normalizeFloatDataAttributes(self::FLOAT_ATTRIBUTES, $attributes);
     *      return parent::load($data, $formName);
     * }
     *
     * @param array $floatAttributes
     * @param array $attributes
     */
    public static function normalizeFloatDataAttributes(array $floatAttributes, array &$attributes): void
    {
        foreach ($floatAttributes as $attributeName) {
            if (!isset($attributes[$attributeName])) {
                $attributes[$attributeName] = 0;
            } else {
                $attributes[$attributeName] = is_numeric($attributes[$attributeName])
                    ? (float)$attributes[$attributeName]
                    : $attributes[$attributeName];
            }
        }
    }

    public static function normalizeIntegerDataAttributes(array $floatAttributes, array &$attributes): void
    {
        foreach ($floatAttributes as $attributeName) {
            if (!isset($attributes[$attributeName])) {
                $attributes[$attributeName] = 0;
            } else {
                $attributes[$attributeName] = is_numeric($attributes[$attributeName]) && filter_var($attributes[$attributeName], FILTER_VALIDATE_INT) !== false
                    ? (int)$attributes[$attributeName]
                    : $attributes[$attributeName];
            }
        }
    }
}