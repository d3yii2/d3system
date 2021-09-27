<?php

namespace d3system\compnents;

use d3system\commands\D3CommandController;

class D3CommandTask
{
    protected $controller;
    
    public function __construct(D3CommandController $controller)
    {
        $this->controller = $controller;
    }
    
    public function execute()
    {
        $this->controller->out('Testing ok.');
    }
}