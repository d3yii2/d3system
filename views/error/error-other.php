<?php

use yii\helpers\Url;

$this->title = 'ERROR Other | ' . Yii::$app->params['appName'];

?>

<div class="error-wrapper">
    <h1><?=Yii::t('theme','Error!')?></h1>
    <h3><?=Yii::t('theme','Other error.')?></h3>
    <a href="<?= Url::to(['/']) ?>"><?= Yii::t('theme', 'Home') ?></a>
</div>