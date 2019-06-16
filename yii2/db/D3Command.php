<?php

namespace d3system\yii2\db;

use yii\db\Command;

class D3Command extends Command
{

    /**
     * @var string
     */
    public $rawSqlIdentifier;


    public function setRawSqlIdentifier(string $rawSqlIdentifier): self
    {
        $this->rawSqlIdentifier = $rawSqlIdentifier;
        return $this;
    }

    public function getRawSql()
    {
        $rawSql = parent::getRawSql();

        if($this->rawSqlIdentifier){
            return '-- Identifier::' . $this->rawSqlIdentifier . ' -- ' . PHP_EOL . $rawSql;
        }
        return $rawSql;
    }


}