<?php

namespace App\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;

class BadFileFormat extends Exception
{
    #[Pure]
    public function __construct(string $bad, array $allowed, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Attachment format '.$bad.' not allowed [allowed='.implode(',', $allowed).']', $code, $previous);
    }

    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        abort(415, $this->getMessage());
    }
}
