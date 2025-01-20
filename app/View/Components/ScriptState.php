<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ScriptState extends Component
{
    public $state;

    /**
     * Create a new component instance.
     *
     * @param array $state
     */
    public function __construct(array $state)
    {
        $this->state = $state;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.script-state');
    }
}
