<?php


namespace d3system\helpers;


use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;

class D3FileHelper
{

    /**
     * Create a temp file full path
     * @param string $prefix (optional) Name prefix
     * @return string Full temp file path
     * @throws Exception When tmp directory doesn't exist or failed to create
     */
    public static function getTempFile(string $prefix = 'temp'): string
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
     * @param string $filePrefix
     * @param string $fileSuffix
     * @return string Full temp file path
     * @throws \yii\base\Exception When tmp directory doesn't exist or failed to create
     */
    public static function getTimeStampFile(
        string $subdir,
        string $fileExtension,
        string $filePrefix = '',
        string $fileSuffix = ''
    ): string
    {
        $dir = Yii::$app->runtimePath . '/' . $subdir;

        if (!is_dir($dir) && (!@mkdir($dir) && !is_dir($dir))) {
            throw new Exception('temp directory does not exist: ' . $dir);
        }


        return $dir . '/' . $filePrefix . date('YmdHis').$fileSuffix.'.' . $fileExtension;
    }

    /**
     * @throws \yii\base\Exception
     */
    public static function getRuntimeFilePath(string $directory, string $fileName): string
    {
        $fullPathDirectory = Yii::$app->runtimePath . '/' . $directory;
        FileHelper::createDirectory($fullPathDirectory);
        return $fullPathDirectory . '/' . $fileName;
    }

    /**
     * @throws \yii\base\Exception
     */
    public static function getRuntimeDirectoryPath(string $directory): string
    {
        $fullPathDirectory = Yii::$app->runtimePath . '/' . $directory;
        FileHelper::createDirectory($fullPathDirectory);
        return $fullPathDirectory;
    }

    /**
     * @param string $directory
     * @param string $fileName
     * @param string $content
     * @throws \yii\base\Exception
     * @deprecated  use D3FileHelper::filePutContentInRuntime()
     */
    public static function filePuntContentInRuntime(string $directory, string $fileName, string $content): void
    {
        $filePath = self::getRuntimeFilePath($directory,$fileName);
        file_put_contents($filePath, $content);
    }

    /**
     * @param string $directory runtime directory subdirectory
     * @param string $fileName
     * @param string $content
     * @return string
     * @throws \yii\base\Exception
     */
    public static function filePutContentInRuntime(string $directory, string $fileName, string $content): string
    {
        $filePath = self::getRuntimeFilePath($directory,$fileName);
        file_put_contents($filePath, $content);
        return $filePath;
    }

    /**
     * @throws \yii\base\Exception
     */
    public static function fileUnlinkInRuntime(string $directory, string $fileName): string
    {
        $filePath = self::getRuntimeFilePath($directory, $fileName);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        return $filePath;
    }

    /**
     * @throws \yii\base\Exception
     */
    public static function fileGetContentFromRuntime(string $directory, string $fileName)
    {
        $filePath = self::getRuntimeFilePath($directory,$fileName);
        try{
            return file_get_contents($filePath);
        }catch (\Exception $e){
            return false;
        }

    }

    /**
     * @throws \yii\base\Exception
     */
    public static function getDirectoryFiles(string $directory)
    {
        $directoryPath = self::getRuntimeDirectoryPath($directory);
        return glob($directoryPath . '/*');
    }

}