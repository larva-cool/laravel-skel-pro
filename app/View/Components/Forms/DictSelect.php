<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use App\Models\System\Dict;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * 字典下拉
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class DictSelect extends Component
{
    public string $name;

    public string $filter;

    public $selected;

    public string $placeholder;

    public array $items = [];

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', $selected = '', $type = '', $placeholder = '')
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->selected = $selected;
        $this->placeholder = $placeholder;
        $this->items = Dict::getOptions($type);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.dict-select');
    }
}
