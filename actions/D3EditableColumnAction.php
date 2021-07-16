<?php


namespace d3system\actions;


use d3system\exceptions\D3UserAlertException;
use kartik\grid\EditableColumnAction;


class D3EditableColumnAction extends EditableColumnAction
{

    public function run()
    {
        return parent::run();
    }

    public function validateEditable()
    {
        try {
            return parent::validateEditable();
        } catch (D3UserAlertException $e) {
            return ['output' => '', 'message' => $e->getMessage()];
        }
    }
}