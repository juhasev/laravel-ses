<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Exceptions;

use Exception;

class LaravelSesException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null)
    {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    /**
     * Custom string representation of object
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
