<?php

/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */

echo "<?php\n";
?>

use yii\db\Migration;

/**
* Class <?= $className ?>
*/
class <?= $className ?> extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    {
        $this->execute('

        ');
    }

    public function safeDown()
    {
        echo "<?= $className ?> cannot be reverted.\n";
        return false;
    }

}