<?php

namespace d3system\yii2;

//use eaBlankonThema\widget\ThFancyBoxLink;
//@FIXME - laikam jāiznes widžeti ārpus tēmas
use eaArgonTheme\widget\ThFancyBoxLink;
use yii\helpers\Url;
use yii\web\Controller;
use Yii;

/**
 * Class LayoutController
 * @package ea\app\controllers
 * @property \d3system\yii2\web\D3SystemView $view
 */
class LayoutController extends Controller
{
    /** @var bool */
    private $layoutMinimal;


    protected $isPost;
    protected $postData;
    
    public const LAYOUT_MINIMAL_PARAM = 'partial';

    /** @var string menu route, pēc kuras nosaka active left menu.
     *
     */
    public $menuRoute;

    /**
     * @var string Left menu class (oldest version left menu code)
     */
    public $leftMenu;

    /** @var string controller actions left menu  code or class*/
    public $actionLeftMenu = [];

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Url::remember();
        foreach ($this->actionLeftMenu as $actionName => $actionSettings) {
            if ($actionName === $action->id) {
                if (isset($actionSettings['leftMenu'])) {
                    $this->leftMenu = $actionSettings['leftMenu'];
                }
                if (isset($actionSettings['menuRoute'])) {
                    $this->menuRoute = $actionSettings['menuRoute'];
                }
                break;
            }
        }

        if ($this->isPost = Yii::$app->request->post()) {
            $this->postData = Yii::$app->request->post();
        }
        
        if (isset($_GET['lang'])) {
            setcookie('lang', $_GET['lang'], time() + 31104000, '/'); // 86400 = 1 day
            Yii::$app->language = $_GET['lang'];
        } else {
            if (isset($_COOKIE['lang'])) {
                Yii::$app->language = $_COOKIE['lang'];
            }
        }
        
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        // other custom code here
        
        return true; // or false to not run the action
    }
    
    public function render($view, $params = [])
    {
        /**
         * @todo jaizmet aaraa
         */
        if (!$this->layout) {
            $this->layout = '@layout';
        }
        
        //@TODO - pēc fancybox parametra likvidēšanas izmantot LAYOUT_MINIMAL_PARAM
        if ($this->isLayoutMinimal()) {
            $this->layout = '@layout_minimal';
        }
        
        return parent::render($view, $params); // TODO: Change the autogenerated stub
    }
    
    
    public function redirect($url, $removePartialParam = true, $statusCode = 302)
    {
        // Hacks, lai pēc redirekta no partial skata nesaglabājas parametrs un atkal neielādē partial
        // @TODO - jāizpēta iespēja šo atrisināt pareizāk
        if ($removePartialParam) {
            unset($_GET['fancybox']);
        }
        return Yii::$app->getResponse()->redirect(Url::to($url), $statusCode);
    }

    public function setLayoutMinimalOn(): void
    {
        $this->layoutMinimal = true;
    }

    public function setLayoutMinimalOff(): void
    {
        $this->layoutMinimal = false;
    }

    /**
     * @return bool
     */
    public function isLayoutMinimal(): bool
    {
        if ($this->layoutMinimal !== null) {
            return $this->layoutMinimal;
        }
        return $this->layoutMinimal = (Yii::$app->getRequest()->get(ThFancyBoxLink::PARAM_NAME)
            || Yii::$app->getRequest()->get(self::LAYOUT_MINIMAL_PARAM));
    }
}
