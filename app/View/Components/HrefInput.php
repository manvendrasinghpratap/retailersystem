<?php
namespace App\View\Components;

use Illuminate\View\Component;

class HrefInput extends Component
{
    public $name;
    public $label;
    public $href;

    public function __construct($name, $label = null, $href = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->href = $href;
    }

    public function render()
    {
        return view('components.href-input');
    }
}

?>