<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CriteriaForm extends Component
{
    public $criteria;
    public $index;

    public function __construct($criteria = null, $index)
    {
        $this->criteria = $criteria;
        $this->index = $index;
    }

    public function render()
    {
        return view('components.criteria-form');
    }
}
