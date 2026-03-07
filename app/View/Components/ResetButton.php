<?php
namespace App\View\Components;

use Illuminate\View\Component;

class ResetButton extends Component
{
    public $name;
    public $label;
    public $value;

    public function __construct($name = null, $label = null, $value = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
    }

    public function render()
    {
        return view('components.reset-button');
    }
}

?>