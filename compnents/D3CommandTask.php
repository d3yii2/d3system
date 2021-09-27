<?php

namespace d3system\compnents;

use d3system\commands\D3CommandController;
use d3system\exceptions\D3TaskException;


class D3CommandTask
{
    protected $controller;
    
    public function __construct(D3CommandController $controller)
    {
        $this->controller = $controller;
    }
    
    /**
     * @throws D3TaskException
     */
    public function execute()
    {
        $this->controller->out('Testing ok.');
    }
}