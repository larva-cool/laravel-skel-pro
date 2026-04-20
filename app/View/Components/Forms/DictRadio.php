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

class DictRadio extends Component
{
    public string $name;

    public string $filter;

    public $selected;

    public string $placeholder;

    public array $items = [];

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', $selected = '', $type = '')
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->selected = $selected;
        $this->items = Dict::getOptions($type);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.dict-radio');
    }
}
