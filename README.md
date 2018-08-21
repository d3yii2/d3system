#d3system

##Installation

```bash
composer require schmunk42/yii2-giiant:"@dev"
```


##Compnents



###ModelsList

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