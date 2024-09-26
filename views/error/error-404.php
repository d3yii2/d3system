<?php

use yii\helpers\Url;

$this->title = 'ERROR 404 | ' . Yii::$app->params['appName'];

?>

<div class="error-wrapper">
    <h1>404!</h1>
    <h3><?=Yii::t('theme','The page you are looking for has not been found!')?></h3>
    <h4><?=Yii::t('theme','The page you are looking for might have been removed, or unavailable.')?></h4>
    <a href="<?= Url::to(['/']) ?>"><?= Yii::t('theme', 'Home') ?></a>
</div>