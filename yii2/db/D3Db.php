<?php


namespace d3system\yii2\db;


use Yii;
use Yii\db\Connection;

class D3Db
{
    public static function clone(): Connection
    {
        $db = Yii::$app->db;
        return new Connection([
            'dsn' => $db->dsn,
            'username' => $db->username,
            'password' => $db->password,
            'charset' => $db->charset,
            'tablePrefix' => $db->tablePrefix,
            'enableSchemaCache' => $db->enableSchemaCache,
            'schemaCacheDuration' => $db->schemaCacheDuration,
            'schemaCache' => $db->schemaCache,
        ]);
    }
}