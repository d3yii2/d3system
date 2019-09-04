<?php

namespace d3system\yii2\web;


use Yii;
use yii\web\View;

class D3SystemView extends View
{

    /**
     * @var string[]
     */
    public $leftMenuFiles = [];

    /**
     * @var string
     */
    public $defaultLeftMenu = 'main';

    /** @var string */
    private $leftMenuCode;

    private $pageHeader = '';
    private $pageFooter = '';
    private $pageHeaderDescription = '';
    private $pageIcon = '';

    private $pageButtons = [];

    private $pageButtonsRight = [];
    private $pageWiki = '';

    private $breadCrumb = [];

    public function init()
    {
        $this->setLeftMenu($this->defaultLeftMenu);
        parent::init();
    }

    /**
     * @return array
     */
    public function getPageButtons(): array
    {
        if(isset($this->params['pageButtons'])){
            return array_merge([$this->params['pageButtons']], $this->pageButtons);
        }
        return $this->pageButtons;
    }

    public function addBreadCrumb(array $url, string $label): void
    {
        $this->breadCrumb[$label] = $url;
    }

    public function getBreadCrumb(): array
    {
        return $this->breadCrumb;
    }
    /**
     * @param string $pageButton
     */
    public function addPageButtons(string $pageButton)
    {
        $this->pageButtons[] = $pageButton;
    }

    /**
     * @return array
     */
    public function getPageButtonsRight(): array
    {
        if(isset($this->params['pageButtonsRight'])){
            return [$this->params['pageButtonsRight']];
        }

        return $this->pageButtonsRight;
    }

    /**
     * @param string $pageButtonsRight
     */
    public function addPageButtonsRight(string $pageButtonsRight)
    {
        $this->pageButtonsRight[] = $pageButtonsRight;
    }

    /**
     * @return string
     */
    public function getPageWiki()
    {
        if(isset($this->params['pageWiki'])){
            return $this->params['pageWiki'];
        }
        return $this->pageWiki;
    }

    /**
     * @param int $pageWiki
     */
    public function setPageWiki(int $pageWiki)
    {
        $this->pageWiki = $pageWiki;
    }

    /**
     * @param string $menuCode
     * @return array
     */
    public function getLeftMenu(string $menuCode = ''): array
    {
        if(!$menuCode){
            $menuCode = $this->leftMenuCode;
        }
        if(isset($this->leftMenuFiles[$menuCode])){
            return require Yii::getAlias($this->leftMenuFiles[$menuCode]);
        }
        return [];
    }

    public function getLeftMenuCode(): string
    {
        return $this->leftMenuCode;
    }

    /**
     * @param string $leftMenuCode
     */
    public function setLeftMenu(string $leftMenuCode)
    {
        $this->leftMenuCode = trim($leftMenuCode);
    }

    /**
     * @return mixed
     */
    public function getPageHeader(): string
    {
        if(isset(Yii::$app->view->params['pageHeader'])){
            return Yii::$app->view->params['pageHeader'];
        }

        return $this->pageHeader;
    }


    /**
     * @param mixed $pageHeader
     */
    public function setPageHeader($pageHeader)
    {
        $this->pageHeader = $pageHeader;
    }

     /**
     * @return mixed
     */
    public function getPageFooter(): string
    {
        if(isset(Yii::$app->view->params['pageFooter'])){
            return Yii::$app->view->params['pageFooter'];
        }

        return $this->pageFooter;
    }


    /**
     * @param mixed $pageFooter
     */
    public function setPageFooter(string $pageFooter)
    {
        $this->pageFooter = $pageFooter;
    }

    /**
     * @return string
     */
    public function getPageHeaderDescription(): string
    {
        if(!empty(Yii::$app->view->params['pageHeaderDescription'])){
            return Yii::$app->view->params['pageHeaderDescription'];
        }

        return $this->pageHeaderDescription;
    }


    /**
     * @var string $description
     */
    public function setPageHeaderDescription(string $description)
    {
        $this->pageHeaderDescription = $description;
    }

    /**
     * @return mixed
     */
    public function getPageIcon():string
    {

        if(!empty(Yii::$app->view->params['pageIcon'])){
            return Yii::$app->view->params['pageIcon'];
        }
        return $this->pageIcon;
    }

    /**
     * @param string $pageIcon
     */
    public function setPageIcon(string $pageIcon)
    {
        $this->pageIcon = $pageIcon;
    }
}