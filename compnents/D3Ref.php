<?php

namespace d3system\compnents;

use d3system\dictionaries\SysModelsDictionary;
use yii\base\Component;

class D3Ref extends Component
{
    public $definition;

    public function getLabel(int $modelId, int $recordId)
    {
        $modelLabel = 'ModelId: ' . $modelId;
        $recordLabel = 'ModelRecordId: ' . $recordId;
        if (!$className = SysModelsDictionary::getClassList()[$modelId] ?? false) {
            return $modelLabel . ' ' . $recordLabel;
        }
        $modelLabel = basename(str_replace('\\', '/', $className));
        if (!$definition = $this->definition[$className] ?? false) {
            return $modelLabel . ' ModelRecordId: ' . $recordId;
        }

        if (is_callable($definition)) {
            return call_user_func($definition, $recordId);
        }

        if (!$labelDefinition = $definition['label'] ?? false) {
            return $modelLabel . ' ModelRecordId: ' . $recordId;
        }

        $modelLabel = $labelDefinition['modelLabel'] ?? $modelLabel;

        if ($findModel = $labelDefinition['findModel'] ?? false) {
            if ($recordModel = $findModel::findOne($recordId)) {
                if ($findModelAttribute = $labelDefinition['findModelAttribute'] ?? false) {
                    $recordLabel = $recordModel->$findModelAttribute;
                } elseif (method_exists($recordModel, 'getLabel')) {
                    $recordLabel = $recordModel->getLabel();
                } elseif (property_exists($recordModel, 'name')) {
                    $recordLabel = $recordModel->name;
                }
            }
        }

        return $modelLabel . ' ' . $recordLabel;
    }

    public function getUrl(int $modelId, int $recordId)
    {
        if (!$className = SysModelsDictionary::getClassList()[$modelId] ?? false) {
            return null;
        }
        if (!$definition = $this->definition[$className] ?? false) {
            return null;
        }

        if (!is_array($definition)) {
            return null;
        }

        if (!$urlDefinition = $this->definition[$className]['url'] ?? false) {
            return null;
        }

        foreach ($urlDefinition as $key => $value) {
            if ($value === '@id') {
                $urlDefinition[$key] = $recordId;
            }
        }

        return $urlDefinition;

    }
}
