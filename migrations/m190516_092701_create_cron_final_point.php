<?php

use yii\db\Migration;

/**
 * Class m190516_092701_create_cron_final_point
 */
class m190516_092701_create_cron_final_point extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE `sys_cron_final_point` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `route` varchar(250) DEFAULT NULL COMMENT \'Route\',
              `value` varchar(250) DEFAULT NULL COMMENT \'Value\',
              `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'Timestamp\',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190516_092701_create_cron_final_point cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190516_092701_create_cron_final_point cannot be reverted.\n";

        return false;
    }
    */
}
