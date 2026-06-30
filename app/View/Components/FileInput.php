<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FileInput extends Component
{
    public string $name;
    public ?string $label;
    public ?string $value;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        ?string $label = null,
        ?string $value = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.file-input');
    }
}