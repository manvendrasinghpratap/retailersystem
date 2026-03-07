<?php
namespace App\View\Components;

use Illuminate\View\Component;

class TextInput extends Component
{
    public $name;
    public $label;
    public $value;
	public $mainrows;

    public function __construct($name, $label = null, $value = null, $mainrows = 4)
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->mainrows = $mainrows;
    }

    public function render()
    {
        return view('components.text-input');
    }
}

?>