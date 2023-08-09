<?php

namespace d3system\helpers;

use cornernote\returnurl\ReturnUrl;
use Exception;
use Yii;
use yii\web\Request;

class FieldAppendButtonHelper
{

    /**
     * @param string|null $modelPrimaryKeyValue
     * @param Request $request
     * @param array $addModels
     * @return bool|string
     */
    public static function createReturnUrl($modelPrimaryKeyValue, Request $request, array $addModels = [])
    {
        try {
            /**
             * if create open by "INPUT +", redirect back with fixed relAtributes
             */

            $returnFormUpdateField = $request->get('rfuf');
            $returnUrl = ReturnUrl::getUrl();
            $returnForm = $request->get('rf');

            if (!$returnForm) {
                return false;
            }

            $returnFormData = $request->get($returnForm, []);

            if ($returnFormUpdateField && isset($returnFormData[$returnFormUpdateField])) {
                self::removeQsVar($returnUrl, $returnForm . '[' . $returnFormUpdateField . ']');
            }
            if ($modelPrimaryKeyValue) {
                $returnFormData[$returnFormUpdateField] = $modelPrimaryKeyValue;
            }
            $returnFormData = [$returnForm => $returnFormData];

            /**
             * load add models
             */

            if ($addForms = $request->get('addModels')) {
                $addForms = explode(',', $addForms);
                foreach ($addForms as $addFormName) {
                    if ($addFormData = $request->get($addFormName)) {
                        if ($returnFormUpdateField && isset($addFormData[$returnFormUpdateField])) {
                            self::removeQsVar($returnUrl, $addFormName . '[' . $returnFormUpdateField . ']');
                            $addFormData[$returnFormUpdateField] = $modelPrimaryKeyValue;
                        }
                        $returnFormData[$addFormName] = $addFormData;
                    }
                }
            }

            /**
             * Assign fields from another models if aliases specified
             */
            if (!empty($addModels) && $aliases = $request->get('rfa')) {
                $data = unserialize(base64_decode($aliases));
                if (is_array($data)) {
                    foreach ($data as $formName => $fields) {
                        foreach ($fields as $field => $alias) {
                            $aliasData = explode('|', $alias);
                            if (!isset($addModels[$aliasData[0]])) {
                                continue;
                            }
                            if (!isset($addModels[$aliasData[0]]->{$aliasData[1]})) {
                                continue;
                            }
                            $returnFormData[$formName][$field] = $addModels[$aliasData[0]]->{$aliasData[1]};
                        }
                    }
                }
            }

            $returnParams = http_build_query($returnFormData);
            return $returnUrl . '&' . $returnParams;
        } catch (Exception $err) {
            Yii::error($err->getMessage());
        }
    }

    /**
     * @param string $fieldName
     * @param Request $request
     * @return bool|mixed
     */
    public static function getReturnFormField(string $fieldName, Request $request)
    {
        if (!($returnFormName = $request->get('rf'))) {
            return false;
        }
        if (!($returnForm = $request->get($returnFormName))) {
            return false;
        }
        return $returnForm[$fieldName];
    }

    /**
     * @param $url
     * @param $varname
     * @return string
     */
    public static function removeQsVar($url, $varname): string
    {
        [$urlpart, $qspart] = array_pad(explode('?', $url), 2, '');
        parse_str($qspart, $qsvars);
        if (!isset($qsvars[$varname])) {
            return $url;
        }
        unset($qsvars[$varname]);
        $newqs = http_build_query($qsvars);
        return $urlpart . '?' . $newqs;
    }
}
