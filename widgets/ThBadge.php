<?php

namespace d3system\widgets;

use yii\bootstrap\Widget;
use yii\helpers\Html;

/**
 * Class ThBadge
 * @package d3system\widgets
 */
class ThBadge extends Widget
{

    const TYPE_SUCCESS = 'success';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';
    const TYPE_DEFAULT = 'default';
    const TYPE_PRIMARY = 'primary';
    const TYPE_LILAC = 'lilac';
    const TYPE_INVERSE = 'inverse';
    const TYPE_TEALS = 'teals';

    public $type = false;
    public $faIcon = false;
    public $text = '';
    public $showText = true;
    public $badgeHtmlOptions = [];

    /**
     * @return string|void
     */
    public function run()
    {
        if (!$this->type) {
            $this->type = self::TYPE_WARNING;
        }

        $content = '';

        if (!empty($this->faIcon)) {
            $content .= '<i class="fa ' . $this->faIcon . '"></i>';

            if ($this->showText) {
                $content .= ' ' . $this->text;
            }
        } else {
            $content .= $this->text;
        }

        $this->badgeHtmlOptions = [
            'class' => 'badge badge-' . $this->type,
            'title' => $this->text,
        ];

        return $this->getBadge($content, $this->type, $this->badgeHtmlOptions);
    }

    /**
     * @param string $content
     * @param string $type
     */
    protected function getBadge(string $content, string $type, array $htmlOptions = [])
    {
        return  Html::tag('span', $content, $htmlOptions);
    }

    /**
     * @param string $content
     * @param string $type
     * @param string $url
     * @return string
     */
    protected function getBadgeLink(string $content, string $type, string $url, array $badgeOptions = [])
    {
        $badge = Html::tag('span', $content, $badgeOptions);

        $link = Html::a($badge, $url);

        return $link;
    }
}
