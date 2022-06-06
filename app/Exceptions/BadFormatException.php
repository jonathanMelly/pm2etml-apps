<?php

namespace App\Exceptions;

use Exception;

class BadFormatException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        abort(422, $this->getMessage());
    }
}
