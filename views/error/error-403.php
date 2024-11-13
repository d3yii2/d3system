<?php
use yii\helpers\Url;

/**
 * @var string $message
 */

$message = $message ?? Yii::t('theme','Forbidden: Access is denied.');

$this->title = 'ERROR 403 | ' . Yii::$app->params['appName'];

?>

<div class="error-wrapper">
    <h1>403!</h1>
    <h3><?=$message?></h3>
    <a href="<?= Url::to(['/']) ?>"><?= Yii::t('theme','Home') ?></a>    
</div>