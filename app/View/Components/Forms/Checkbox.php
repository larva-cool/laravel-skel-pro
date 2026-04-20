<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public string $name;

    public string $filter;

    public array $selected;

    public array $items;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', $selected = [], array|Collection $items = [])
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->selected = $selected;
        $this->items = $items instanceof Collection ? $items->toArray() : $items;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.checkbox');
    }
}
