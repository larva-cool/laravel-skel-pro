<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * 日期范围选择器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Daterange extends Component
{
    /**
     * @var string|mixed 组件名称
     */
    public string $name;

    public string $filter;

    /**
     * @var string|mixed 组件值
     */
    public string $startValue;

    /**
     * @var string|mixed 组件值
     */
    public string $endValue;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', Carbon|string $startValue = '', Carbon|string $endValue = '')
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        if ($startValue instanceof Carbon) {
            $this->startValue = $startValue->toDateString();
        } else {
            $this->startValue = $startValue;
        }
        if ($endValue instanceof Carbon) {
            $this->endValue = $endValue->toDateString();
        } else {
            $this->endValue = $endValue;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.daterange');
    }
}
