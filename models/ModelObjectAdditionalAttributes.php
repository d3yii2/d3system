<?php


namespace d3system\models;


use yii\base\Model;

class ModelObjectAdditionalAttributes extends Model
{
    public function additionalAttributes(): array
    {
        return [
        ];
    }

    public function attributes()
    {
        $list = [];
        foreach ($this->additionalAttributes() as $attrClass) {
            $list[] = $attrClass::getName();
        }
        return $list;
    }

    public function rules()
    {
        $safeFields = [];
        $rules = [];
        foreach ($this->additionalAttributes() as $attrClass) {
            $safeFields[] = $attrClass::getName();
            if(method_exists($attrClass,'rules')){
                $rules[] =$attrClass::rules();
            }
        }
        if($rules) {
            $rules = array_merge(...$rules);
        }
        $rules['additionalAttributesSafe'] = [
                $safeFields,
                'safe'
            ];
        return $rules;

    }

    public function attributeLabels()
    {
        $list = [];
        foreach ($this->additionalAttributes() as $attrClass) {
            $list[$attrClass::getName()] = $attrClass::getLabel();
        }
        return $list;
    }

    public function __get($name)
    {
        foreach ($this->additionalAttributes() as $attrClass) {
            if($name === $attrClass::getName()){
                if(property_exists($this, $name)) {
                    return $this->$name;
                }
                return null;

            }
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        foreach ($this->additionalAttributes() as $attrClass) {
            if($name === $attrClass::getName()){
                return $this->$name = $value;
            }
        }
        return parent::__set($name, $value);
    }

    public function init()
    {
        parent::init();
        foreach ($this->additionalAttributes() as $attrClass) {
            if(method_exists($attrClass,'defaultValue')){
                $attributeName = $attrClass::getName();
                $this->$attributeName = $attrClass::defaultValue();
            }
        }
    }
}