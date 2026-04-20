<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * 选择数字区间
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class SelectRange extends Component
{
    /**
     * @var string|mixed 组件名称
     */
    public string $name;

    public string $filter;

    /**
     * @var string|mixed 组件值
     */
    public int $min;

    /**
     * @var string|mixed 组件值
     */
    public int $max;

    /**
     * @var string|mixed 组件值
     */
    public $value;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', $value = '', $min = 0, $max = 100)
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->value = $value;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.select-range');
    }
}
