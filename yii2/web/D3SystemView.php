<?php

namespace d3system\yii2\web;


use cornernote\returnurl\ReturnUrl;
use Yii;
use yii\web\View;

/**
 *
 * @property string $leftMenu
 */
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

    public $wikiViewUrl = ['/wiki/content/view'];

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
    public function addPageButtons(string $pageButton): void
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
    public function addPageButtonsRight(string $pageButtonsRight): void
    {
        $this->pageButtonsRight[] = $pageButtonsRight;
    }

    /**
     * @return string
     * @deprecated use self::getWikiViewUrl()
     */
    public function getPageWiki(): string
    {
        return $this->params['pageWiki'] ?? $this->pageWiki;
    }

    /**
     * @return array
     */
    public function getWikiViewUrl(): array
    {
        if(!$id = $this->params['pageWiki'] ?? $this->pageWiki){
            return [];
        }
        $url = $this->wikiViewUrl;
        $url['id'] = $id;
        $url['ru'] = ReturnUrl::getToken($this->title);
        return $url;
    }

    /**
     * @param string $pageWiki
     */
    public function setPageWiki(string $pageWiki): void
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
    public function setLeftMenu(string $leftMenuCode): void
    {
        $this->leftMenuCode = trim($leftMenuCode);
    }

    /**
     * @return mixed
     */
    public function getPageHeader(): string
    {
        return Yii::$app->view->params['pageHeader'] ?? $this->pageHeader;
    }


    /**
     * @param mixed $pageHeader
     */
    public function setPageHeader($pageHeader): void
    {
        $this->pageHeader = $pageHeader;
    }

     /**
     * @return mixed
     */
    public function getPageFooter(): string
    {
        return Yii::$app->view->params['pageFooter'] ?? $this->pageFooter;
    }


    /**
     * @param mixed $pageFooter
     */
    public function setPageFooter(string $pageFooter): void
    {
        $this->pageFooter = $pageFooter;
    }

    /**
     * @return string
     */
    public function getPageHeaderDescription(): string
    {
        return Yii::$app->view->params['pageHeaderDescription']??$this->pageHeaderDescription;
    }


    /**
     * @var string $description
     */
    public function setPageHeaderDescription(string $description): void
    {
        $this->pageHeaderDescription = $description;
    }

    /**
     * @return mixed
     */
    public function getPageIcon():string
    {
        return Yii::$app->view->params['pageIcon'] ?? $this->pageIcon;
    }

    /**
     * @param string $pageIcon
     */
    public function setPageIcon(string $pageIcon): void
    {
        $this->pageIcon = $pageIcon;
    }
}