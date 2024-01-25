<?php

namespace d3system\yii2\web;


use cornernote\returnurl\ReturnUrl;
use d3system\yii2\LayoutController;
use Exception;
use Yii;
use yii\web\AssetBundle;
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
    public $showLeftSidebar = true;
    
    public $wikiViewUrl = ['/wiki/content/view'];

    public $settings = [];

    /** @var string */
    public $defaultAssetsClass = 'ea\app\AppAsset';

    /** @var string */
    private $leftMenuCode;

    private $pageHeader = '';
    private $pageHeaderButtons = [];
    private $pageFooter = '';
    private $pageHeaderDescription = '';
    private $pageIcon = '';

    /** @var string|null  Use for title, where included html */
    public ?string $pageTitleEncoded = null;

    private $pageNavigationConfig = [];
    private $pageNavigationWidget = null;

    private $pageButtons = [];

    private $pageButtonsRight = [];
    private $finalPageButtonsRight = [];
    private $pageWiki = '';

    private $breadCrumb = [];

    private $settingButtonUrl;
    private $settingButtonTooltip;
    /**
     * @var array = [
     *   [
     *      'lable' => 'label name'
     *      'url' => [
     *          'excel',
     *           'id' => 12
     *      ]
     *   ]
     * ]
     */
    private $exportButtonList = [];
    
    private $showHeader = true;
    public array $backButtons = [];

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

    /**
     * @return array
     */
    public function getPageHeaderButtons(): array
    {
        if(isset($this->params['pageHeaderButtons'])){
            return array_merge([$this->params['pageHeaderButtons']], $this->pageHeaderButtons);
        }
        return $this->pageHeaderButtons;
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
     * @param string $pageButton
     */
    public function addPageHeaderButtons(string $pageButton): void
    {
        $this->pageHeaderButtons[] = $pageButton;
    }

    public function addBackButtons($url, string $label = null): void
    {
        $this->backButtons[] = [
            'url' => $url,
            'label' => $label,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPageButtonsRight(): array
    {
        if($this->finalPageButtonsRight){
            return $this->finalPageButtonsRight;
        }

        $this->finalPageButtonsRight = $this->pageButtonsRight;
        if(isset($this->params['pageButtonsRight'])){
            $this->finalPageButtonsRight[] = $this->params['pageButtonsRight'];
        }
        if(count($this->exportButtonList) === 1){
            $this->finalPageButtonsRight[] = Yii::$app->widget->button::widget([
                'label' => Yii::t('d3system','Export')
                    . ': '
                    . $this->exportButtonList[0]['label'],
                'icon' => Yii::$app->widget->button::ICON_DOWNLOAD,
                'type' => Yii::$app->widget->button::TYPE_PRIMARY,
                'link' => $this->exportButtonList[0]['url']
            ]);
        }elseif(count($this->exportButtonList) > 1){
            $this->finalPageButtonsRight[] = $this->buttonDropdownClass::widget([
                'label' => Yii::t('d3system','Export'),
                'icon' => Yii::$app->widget->button::ICON_DOWNLOAD,
                'type' => Yii::$app->widget->button::TYPE_PRIMARY,
                'items' => $this->exportButtonList
            ]);
        }

        if($this->settingButtonUrl){
            $this->finalPageButtonsRight[] = Yii::$app->widget->button::widget([
                'type' => Yii::$app->widget->button::TYPE_DEFAULT,
                'tooltip' => $this->settingButtonTooltip,
                'link' => $this->settingButtonUrl,
                'icon' => Yii::$app->widget->button::ICON_COG,
            ]);
        }

        return $this->finalPageButtonsRight;
    }

    /**
     * @param string $pageButtonsRight
     */
    public function addPageButtonsRight(string $pageButtonsRight): void
    {
        $this->pageButtonsRight[] = $pageButtonsRight;
    }

    /**
     * @param string $label
     * @param array $url
     */
    public function addExportButtonItem(string $label, array $url): void
    {
        $this->exportButtonList[] = [
            'label' => $label,
            'url' => $url
        ];
    }

    public function setSettingButton(array $url, string $tooltip = ''): void
    {
        $this->settingButtonUrl = $url;
        $this->settingButtonTooltip = $tooltip;
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
    public function getWikiViewUrl($partial = false): array
    {
        if(!$id = $this->params['pageWiki'] ?? $this->pageWiki){
            return [];
        }
        $url = $this->wikiViewUrl;
        $url['id'] = $id;
        $url['ru'] = ReturnUrl::getToken($this->title);
        if ($partial) {
            $url[LayoutController::LAYOUT_MINIMAL_PARAM] = true;
        }
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
        if (!$menuCode) {
            $menuCode = $this->getLeftMenuCode();
        }

        if (class_exists($menuCode)) {
            $menu = new $menuCode();
            return $menu->list();
        }
        if (isset($this->leftMenuFiles[$menuCode])) {
            if (class_exists($this->leftMenuFiles[$menuCode])) {
                $menu = new $this->leftMenuFiles[$menuCode]();
                return $menu->list();
            }
            return require Yii::getAlias($this->leftMenuFiles[$menuCode]);
        }
        return [];
    }

    public function getLeftMenuFirstItemUrl (string $menuCode)
    {
        foreach($this->getLeftMenu($menuCode) as $menuItem){
            if($menuItem['visible'] ?? true){
                return $menuItem['url'];
            }
        }
        return false;
    }

    public function getLeftMenuCode(): string
    {
        if ($this->leftMenuCode) {
            return $this->leftMenuCode;
        }
        if (isset($this->context->leftMenu) && $this->context->leftMenu) {
            return $this->leftMenuCode = $this->context->leftMenu;
        }
        if (isset($this->context->module->leftMenu) && $this->context->module->leftMenu) {
            return $this->leftMenuCode = $this->context->module->leftMenu;
        }

        return $this->leftMenuCode = '';
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
     * @return array
     */
    public function getPageNavigationConfig(): array
    {
        return $this->pageNavigationConfig;
    }

    /**
     * @return string
     */
    public function getPageNavigationWidget(): string
    {
        return $this->pageNavigationWidget;
    }

    /**
     * @param array $config
     * @param string|null $widget
     */
    public function setPageNavigation(array $config, ?string $widget = null): void
    {
        $this->pageNavigationConfig = $config;
        $this->pageNavigationWidget = $widget;
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
     * @param string $content
     */
    public function addToPageFooter(string $content): void
    {
        $this->pageFooter .= $content;
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

    /**
     * Detect the view is partial (URL contains fancybox param)
     * Use the eaBlankonThema\yii2\web\LayoutController:: LAYOUT_MINIMAL_PARAM instead
     * @return bool|null
     * @deprecated  use D3SystemView::isLayoutMinimal()
     */
    public function isPartialView(): ?bool
    {
        return Yii::$app->request->get('fancybox');
    }
    
    /**
     * @return bool
     */
    public function headerEnabled()
    {
        return $this->showHeader;
    }
    
    public function showHeader()
    {
        $this->showHeader = true;
    }

    public function hideHeader()
    {
        $this->showHeader = false;
    }

    public function registerDefaultAssets()
    {
        /** @var AssetBundle $assetClass */
        $assetClass = $this->defaultAssetsClass;
        return $assetClass::register($this);

    }
    
    /**
     * @param   string  $key
     *
     * @return mixed|null
     */
    public function getSetting(string $key)
    {
        return $this->settings[$key] ?? null;
    }
    
    /**
     * @param   string  $key
     * @param   string  $value
     */
    public function setSetting(string $key, string $value): void
    {
        $this->settings[$key] = $value;
    }
}
