<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\View\Components\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Textarea extends Component
{
    public string $name;

    public string $filter;

    public $value;

    public string $placeholder;

    public bool $required;

    /**
     * Create a new component instance.
     */
    public function __construct($name = '', $value = '', $placeholder = '', $required = 'false')
    {
        $this->name = $name;
        $this->filter = sanitize_key($this->name);
        $this->value = $value;
        $this->placeholder = $placeholder ?: '';
        $this->required = $required == 'true';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.textarea');
    }
}
