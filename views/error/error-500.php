<?php

use yii\helpers\Url;

$this->title = 'ERROR 500 | ' . Yii::$app->params['appName'];

?>
<div class="error-wrapper">
    <h1>500</h1>
    <h3><?=Yii::t('theme','Internal Server Error.')?></h3>
    <h4><?=Yii::t('theme','Sorry, something went wrong.')?></h4>
    <a href="<?= Url::to(['/']) ?>"><?= Yii::t('theme', 'Home') ?></a>
</div>