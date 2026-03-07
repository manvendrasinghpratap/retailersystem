<?php
namespace App\View\Components;

use Illuminate\View\Component;

class FormButtons extends Component
{
    public $name;
    public $label;
    public $url;
    public $submitText;
    public $resetText;

    public function __construct($submitText = 'Submit', $resetText = 'Reset',$name = null, $label = null, $url = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->url = $url;
		$this->submitText = $submitText;
		$this->resetText = $resetText;
    }

    public function render()
    {
        return view('components.form-buttons');
    }
}

?>