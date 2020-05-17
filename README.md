# d3system

## Installation

```bash
composer require d3yii2/d3system dev-master
```

## Configuration
add translation
```php
$config = [
   'components' => [
        'i18n' => [
            'translations' => [ 
                'd3system*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@d3yii2/d3system/messages',
                    'sourceLanguage' => 'en-US',
                ],
                'crud' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@d3yii2/d3system/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ]
        ]
    ]
];
```

## Components



### ModelsList

Configuration:
```php
 'components' => [
        'ModelsList' => [
            'class' => 'd3system\compnents\ModelsList',
            'cacheKey' => 'd3system\modeList',
            'cacheDuration' => 3600
        ]    
        
```

Usage:
```php
  $modelId = \Yii::$app->ModelsList->getId($model);

```

## Date & Time conversions

Dependency
https://github.com/d3yii2/yii2-datetime

Conversion works only for the model attributes suffixed vith "_local"
A example, instead
```php 
$model->YOUR_ATTRIBUTE
```
use
```php
$model->YOUR_ATTRIBUTE_local
```

### Add behavior config in model
Add the behavior to your model and list the attributes need to be converted
Important: do NOT add the "_local" suffix here!
```php
public function behaviors(): array
{
    return D3DateTimeBehavior::getConfig(['YOUR_ATTRIBUTE']);
}
```
Or if You need custom options (see the https://github.com/d3yii2/yii2-datetime)
```php
public function behaviors()
{
    return [
        'datetime' => [
            'class' => D3DateTimeBehavior::className(), // Our behavior
            'attributes' => [
                'YOUR_ATTRIBUTE', // List all editable date/time attributes
            ],
            // Date formats or other options
           'originalFormat' => ['datetime', 'yyyy-MM-dd HH:mm:ss'],
           'targetFormat' => 'date',
        ]
    ];
}
```

### Display value in view
```php
<?= $model->YOUR_ATTRIBUTE_local ?>
```

### Assign the value before save
```php
$model->load(Yii::$app->request->post());
```
or
```php
 $model->YOUR_ATTRIBUTE_local = $value;
```
or
```php
 $model->setAttribute('YOUR_ATTRIBUTE_local', $value);
```

By multiple assignment via load() ensure the local attributes have 'safe' rules:
```php
// Virtual params for DateTimeBehavior
public function rules(): array
{   
    return [
        [...],
        [['YOUR_ATTRIBUTE_local'], 'safe'],
    ];
}
```

D3EditableAction Initial Setup in Controller
```
editAbleFields: must match real attributes
editAbleFieldsForbbiden: must match real attributes
modelName: pass current controller model Name with full Namespace
```
```php
/**
 * @var array
 */
public $editAbleFields = ['hasEditable', 'status'];

/**
 * @var array
 */
public $editAbleFieldsForbbiden = [];
```

Actions
```php
public function actions()
{
    return [
        'editable'      => [
            'class'                   => 'D3system\actions\d3EditableAction',
            'modelName'               => 'cewood\cwpallet\models\CwpalletPallet',
            'editAbleFields'          => $this->editAbleFields,
            'editAbleFieldsForbbiden' => $this->editAbleFieldsForbbiden,
        ],
    ];
}
```


