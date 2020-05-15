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
            ALTER TABLE `sys_cron_final_point`   
                ADD COLUMN `key` VARCHAR(50) NULL  COMMENT \'Key\' AFTER `route`;
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

