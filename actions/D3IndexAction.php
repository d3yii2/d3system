<?php

namespace d3system\actions;

use d3system\helpers\FlashHelper;
use eaBlankonThema\widget\ThRmGridView;
use Yii;

class D3IndexAction extends \yii\base\Action
{

    public const XLS = 'xls';
    public ?string $searchModelClass = null;
    public function run(string $action): string
    {
        $searchModel  = new $this->searchModelClass;
        $dataProvider = $searchModel->search(ThRmGridView::getMergedFilterStateParams());

        if($action  === self::XLS) {
            if ($dataProvider->getTotalCount() > 10000) {
                FlashHelper::addDanger(Yii::t(
                    'crud',
                    'Filtered out more than 10 000 records. So many records cannot be exported to excel.'
                ));
                $this->controller->redirect(['index']);
            }
            $dataProvider->pagination = false;
            $dataProvider->prepare(true);
            return $this->controller->render(
                'index-xls',
                [
                    'dataProvider' => $dataProvider,
                ]
            );
        }
    }
}