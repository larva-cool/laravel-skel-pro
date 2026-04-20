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
 * 日期时间选择器
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Datetimepicker extends Component
{
    /**
     * @var string|mixed 组件名称
     */
    public string $name;

    public string $filter;

    /**
     * @var string|mixed 组件值
     */
    public string $value;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', Carbon|string $value = '')
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        if ($value instanceof Carbon) {
            $this->value = $value->toDateTimeString();
        } else {
            $this->value = $value;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.datetimepicker');
    }
}
