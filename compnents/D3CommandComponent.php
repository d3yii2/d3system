<?php

namespace d3system\compnents;

use d3system\controllers\D3ComponentCommandController;
use yii\base\Component;


class D3CommandComponent extends Component {

    /**
     * @var D3ComponentCommandController
     */
    protected $controller;

    /** @var string[]  */
    private $errors = [];

    public function run(D3ComponentCommandController $controller) : bool
    {
        $this->controller = $controller;
        return true;
    }

    public function out(string $string, int $settings = 0): void
    {
        $this->controller->out($string, $settings);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
