<?php

use yii\db\Migration;

/**
 * Class m180821_114733_init
 */
class m180821_114733_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE `sys_models` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `table_name` varchar(256) NOT NULL COMMENT \'Table\',
              `class_name` varchar(256) DEFAULT NULL COMMENT \'Class\',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=ascii        
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('
            DROP TABLE `sys_models`;        
        ');

        return false;
    }

}
