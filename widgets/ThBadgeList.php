<?php

namespace d3system\widgets;

/**
 * Class ThBadgeList
 * @package d3system\widgets
 */
class ThBadgeList extends ThBadge
{
    public $items = [];
    public $separator = ' ';

    /**
     * @return string|void
     */
    public function run()
    {
        $badges = [];

        foreach ($this->items as $item) {

            $type = isset($item['type']) ? $item['type'] : parent::TYPE_WARNING;

            $badgeContent = '';

            if (!empty($item['faIcon'])) {
                $badgeContent .= '<i class="fa ' . $item['faIcon'] . '"></i>';

                if (!empty($this->renderOptions['iconsWithText'])) {
                    $badgeContent .= ' ' . $item['text'];
                }
            } else {
                $badgeContent .= $item['text'];
            }

            $defaultHtmlOptions = [
                'class' => 'badge badge-' . $type,
                'title' => $item['text'],
            ];

            $badgeOptions = isset($item['badgeOptions'])
                ? array_merge($defaultHtmlOptions, $item['badgeOptions'])
                : $defaultHtmlOptions;

            $badges[] = isset($item['url'])
                ? parent::getBadgeLink($badgeContent, $type, $item['url'], $badgeOptions)
                : parent::getBadge($badgeContent, $type, $badgeOptions);
        }

        return implode($this->separator, $badges);
    }
}
