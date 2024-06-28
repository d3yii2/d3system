[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)

# d3system

## Installation

```bash
composer require d3yii2/d3system dev-master
```

## module configuration in seperate configfile
Module extend from  D3Module
```php
class Module extends D3Module 
{
}
```

In configuration file define only path
```php 
        'd3persons' => [
            'class' => 'yii2d3\d3persons\Module',
            'configFilePath' => __DIR__ .'/module_d3persons.php'
        ],
```

In module configuration file add module class for IDE suggester
```php 
return [
    'class' => 'yii2d3\d3persons\Module',
    'ownerExpire' => '+10 years',
    'userExpire' => '+10 days',
];    
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

### Compnent commands <a id='compnentCommands'></a>

d3system must be defined as module in console config
```php
    'modules' => [
        'd3system' => [
            'class' => 'd3system\Module'
        ],
    ]
```

Extens from D3CommandComponent
```php
use d3system\compnents\D3CommandComponent; 
class DailyActivityNotification extends D3CommandComponent {

    public $setting1;
    public $setting2;
    
    public function init()
    {
        //init logic
    }
    
    public function run(D3ComponentCommandController $controller) : bool
    {
        parent::run($controller);
        //runing logic
    }        
}
```

Define as component  in console config
```php 
    'components' => [
        'activityEmail' => [
        'class' => 'd3yii2\d3activity\components\DailyActivityNotification',
        'setting1' => 15,
        'setting2' => 22,
    ]    
```
Executing command component
```shell
yii d3system/d3-component-command activityEmail,component2,component3
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
            'class'                   => D3EditableAction::class,
             'modelName'               => AudAuditor::class,
             'editAbleFields'          => ['status','notes'],
             'editAbleFieldsForbbiden' => $this->editAbleFieldsForbbiden,
             'preProcess' => static function (Inout $model) {
                  if ($model->isAttributeChanged('driver')) {
                     $model->driver = iconv('UTF-8', 'ASCII//TRANSLIT',$model->driver);
                  }   
             },
             'outPreProcess' => static function (ContInout $model, array $output) {
                 if (isset($output['ediBookingId'])) {
                     $output['ediBookingId'] = DepoEdiBookingDictionary::getIdLabel($output['ediBookingId']);
                 }
                 return $output;
             }             
        ],
    ];
}
```


