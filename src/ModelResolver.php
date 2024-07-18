<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Exception;

class ModelResolver
{
    /**
     * @return class-string
     * @throws Exception
     */
    public static function get(string $name): string
    {
        $class = config('laravelses.models.'.ucfirst($name));

        if (! $class) {
            throw new Exception("Model ($name) could not be resolved");
        }

        return $class;
    }
}