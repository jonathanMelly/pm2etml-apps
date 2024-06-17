<?php

namespace App\View\Components;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
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
     */
    public function render(): View|Factory|Application
    {
        $joke = 'Quel est le crustacé le plus léger de la mer ? La palourde ;-)';

        if (config('joke', false) !== false) {
            try {
                $json = file_get_contents('https://j0ke.xyz/free/api/joke');
                $obj = json_decode($json);
                $joke = implode(' ', $obj->joke);
            } catch (Exception $e) {
                Log::warning('Cannot get joke from j0ke.xyz : '.var_export($e, true));
            }
        }

        return view('components.joke')->with('joke', $joke);
    }
}
