<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use App\Enum\StatusSwitch;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * 开关组件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Switcher extends Component
{
    /**
     * @var string|mixed 组件名称
     */
    public string $name;

    public string $filter;

    /**
     * @var StatusSwitch 组件值
     */
    public StatusSwitch $value;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', StatusSwitch $value = StatusSwitch::ENABLED)
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.switcher');
    }
}
