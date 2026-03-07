<?php
namespace App\View\Components;

use Illuminate\View\Component;

class TextAreaInput extends Component
{
    public $name;
    public $label;
    public $value;
    public $cols;
    public $rows;
    public $mainrows;

    public function __construct($name, $label = null, $value = null,$cols  = 3,$rows = 3,$mainrows = 4)
    {
        $this->name 	= $name;
        $this->label 	= $label;
        $this->value 	= $value;
        $this->cols 	= $cols;
        $this->rows 	= $rows;
        $this->mainrows = $mainrows;
    }

    public function render()
    {
        return view('components.textarea-input');
    }
}

?>