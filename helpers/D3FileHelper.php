<?php


namespace d3system\helpers;


use Yii;
use yii\web\NotFoundHttpException;

class D3FileHelper
{

    /**
     * Create a temp file full path
     * @param string $prefix (optional) Name prefix
     * @return string Full temp file path
     * @throws NotFoundHttpException When tmp directory doesn't exist or failed to create
     */
    public static function getTempFile($prefix = 'temp'): string
    {
        $tmpDir = Yii::$app->runtimePath . '/tmp';

        if (!is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir))) {
            throw new NotFoundHttpException ('temp directory does not exist');
        }

        return tempnam($tmpDir, $prefix);
    }
}