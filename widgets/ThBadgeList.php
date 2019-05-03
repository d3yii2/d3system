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

            if (!empty($this->renderOptions['beforeText'])) {
                $item['beforeText'] = $this->renderOptions['beforeText'];
            }

            if (!empty($this->renderOptions['afterText'])) {
                $item['afterText'] = $this->renderOptions['afterText'];
            }

            $badges[] = $this->getBadge($item);
        }

        return implode($this->separator, $badges);
    }
}
