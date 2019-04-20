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
    public $renderOptions = [];

    /**
     * @return string|void
     */
    public function run(): string
    {
        $badges = [];

        foreach ($this->items as $item) {

            if (!empty($this->renderOptions['iconsWithText'])) {
                $item['showText'] = true;
            }

            $badges[] = $this->getBadge($item);
        }

        return implode($this->separator, $badges);
    }
}
