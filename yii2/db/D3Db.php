<?php


namespace d3system\yii2\db;


use Yii;
use Yii\db\Connection;

class D3Db
{

    /**
     * @var Connection[]
     */
    private static $dbConnection = [];

    public static function clone(string $name = 'default'): Connection
    {
        if(isset(self::$dbConnection[$name])){
            return self::$dbConnection[$name];
        }
        $db = Yii::$app->db;
        return self::$dbConnection[$name] = new Connection([
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