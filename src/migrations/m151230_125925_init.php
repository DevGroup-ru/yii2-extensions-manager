<?php

use yii\db\Migration;

class m151230_125925_init extends Migration
{
    private $tableName = '{{%extensions}}';
    public function up()
    {
        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(180)->notNull(),
                'installed_version' => $this->string(20)->notNull(),
                'description' => $this->string(255)->notNull()->defaultValue(''),
                'active' => $this->integer(1)->notNull()->defaultValue(0),
                'type' => $this->string(20)->notNull(),
            ]
        );
    }

    public function down()
    {
        $this->dropTable($this->tableName);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
