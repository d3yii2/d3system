<?php

use yii\db\Migration;

/**
* Class m190225_144643_alter_sys_models_add_unique_key*/
class m190225_144643_alter_sys_models_add_unique_key extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    { return true;
        $this->execute("ALTER TABLE `sys_models` ADD UNIQUE( `table_name`, `class_name`)");
    }

    public function safeDown()
    {return true;
        echo "m190225_144643_alter_sys_models_add_unique_key cannot be reverted.\n";
        return false;
    }

}