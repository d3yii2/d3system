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

## Compnents



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
