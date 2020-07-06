<?php


namespace d3system\helpers;


use Yii;
use yii\base\Exception;

class D3FileHelper
{

    /**
     * Create a temp file full path
     * @param string $prefix (optional) Name prefix
     * @return string Full temp file path
     * @throws Exception When tmp directory doesn't exist or failed to create
     */
    public static function getTempFile($prefix = 'temp'): string
    {
        $tmpDir = Yii::$app->runtimePath . '/temp';

        if (!is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir))) {
            throw new Exception('temp directory does not exist: ' . $tmpDir);
        }

        if(!$tempName = tempnam($tmpDir, $prefix)){
            throw new Exception('Can not create tem file in directory ' . $tmpDir);
        }
        return $tempName;
    }

    /**
     * Create a temp file full path
     * @param string $subdir
     * @param string $fileExtension
     * @return string Full temp file path
     * @throws Exception When tmp directory doesn't exist or failed to create
     */
    public static function getTimeStampFile(string $subdir,string $fileExtension): string
    {
        $dir = Yii::$app->runtimePath . '/' . $subdir;

        if (!is_dir($dir) && (!@mkdir($dir) && !is_dir($dir))) {
            throw new Exception('temp directory does not exist: ' . $dir);
        }


        return $dir . '/' . date('YmdHis').'.' . $fileExtension;
    }

}