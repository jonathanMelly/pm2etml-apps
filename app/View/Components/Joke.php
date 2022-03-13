<?php

namespace App\View\Components;

use Exception;
use Illuminate\View\Component;

class Joke extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $joke ="";
        try {
            $json = file_get_contents('https://j0ke.xyz/free/api/joke');
            $obj = json_decode($json);
            $joke = implode(' ',$obj->joke);
        }
        catch (Exception $e)
        {
            $joke = "Quel est le crustacé le plus léger de la mer ? La palourde ;-)";
        }


        return view('components.joke')->with("joke",$joke);
    }
}
