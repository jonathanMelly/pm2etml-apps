<?php

namespace App\View\Components;

class RootLayout extends \Illuminate\View\Component
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('layouts.root');
    }
}
