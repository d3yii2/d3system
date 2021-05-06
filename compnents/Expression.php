<?php


namespace d3system\compnents;


use ParseError;

class Expression
{
    private const ALLOWED_SYMBOLS = ['(', ')', '*', '+', '/', '-'];

    public $errors = [];

    /** @var array string[] */
    private $variableMapping;

    /** @var string */
    private $expression;

    /**
     * Expression constructor.
     * @param array $variableMapping
     * @param string $expression
     */
    public function __construct(array $variableMapping, string $expression)
    {
        $this->variableMapping = $variableMapping;
        $this->expression = $expression;
    }


    public function validate(): bool
    {
        $this->errors = [];
        $e1 = str_replace(self::ALLOWED_SYMBOLS, '', $this->expression);
        $e1 = preg_replace('#[a-zA-Z0-9]+#', '', $e1);
        if (trim($e1) !== '') {
            $this->errors[] = 'Illegal characters: ' . $e1;
            return false;
        }
        $values = range(1, count($this->variableMapping));
        $e2 = str_replace(array_keys($this->variableMapping), $values, $this->expression);
        try {
            eval('$r=' . $e2 . ';');
        } catch (ParseError $e) {
            $this->errors[] = 'Validattion: ' . $e->getMessage();
            return false;

        }
        return true;
    }


    public function createExpression()
    {
        return str_replace(array_keys($this->variableMapping), $this->variableMapping, $this->expression);
    }

    public function exec(array $fieldValues, array $fieldMapping)
    {
        $params = [];
        foreach($fieldMapping as $formulaFieldName => $fieldValueName){
            $params[$formulaFieldName] = $fieldValues[$fieldValueName]??0;
        }
        $expression = str_replace(array_keys($params), $params, $this->expression);
        $r = null;
        try {
            eval('$r=' . $expression . ';');
        } catch (ParseError $e) {
            $this->errors[] = 'Validattion: ' . $e->getMessage();
            return null;
        }
        return $r ?? null;
    }
}