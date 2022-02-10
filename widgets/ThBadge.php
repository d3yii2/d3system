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

    public $type;
    public $faIcon;
    public $text = '';
    public $showText = true;
    public $beforeText;
    public $afterText;
    public $htmlOptions = [];
    public $title = '';
    public $url;

    /**
     * @return string|void
     */
    public function run(): string
    {
        if (!$this->type) {
            $this->type = self::TYPE_WARNING;
        }

        return $this->getBadge();
    }

    /**
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return [
            'type' => $this->type,
            'faIcon' => $this->faIcon,
            'text' => $this->text,
            'showText' => $this->showText,
            'beforeText' => $this->beforeText,
            'afterText' => $this->afterText,
            'title' => $this->title,
            'url' => $this->url
        ];
    }

    /**
     * @param array $options
     * @param array $htmlOptions
     * @return string
     */
    protected function getBadge(array $options = [], array $htmlOptions = []): string
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $htmlClass = $htmlOptions['class'] ?? null;
        $htmlOptions = array_merge(
            $htmlOptions,
            [
                'class' => 'badge badge-' . $options['type'] . ' ' . $htmlClass,
                'title' => ! empty($options['title']) ? $options['title'] : $options['text'],
            ]
        );

        $label = '';

        if (!empty($options['beforeText'])) {
            $label .= $options['beforeText'];
        }

        if (!empty($options['faIcon'])) {
            $label .= '<i class="fa ' . $options['faIcon'] . '"></i> ';

            if ($options['showText']) {
                $label .= $options['text'];
            }
        } else {
            $label .= $options['text'];
        }

        if (!empty($options['afterText'])) {
            $label .= $options['afterText'];
        }

        $badgeContent = Html::tag('span', $label, $htmlOptions);

        if (!empty($options['url'])) {
            $badgeContent = $link = Html::a($badgeContent, $options['url']);
        }

        return$badgeContent;
    }
}
