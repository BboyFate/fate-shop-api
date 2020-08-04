<?php

namespace App\Support\Traits;

use App\Support\Response;

/***
 * Trait ResponseTrait
 *
 * @property \App\Support\Response $response
 */
trait ResponseTrait
{
    public function __get($key)
    {
        $callable = [
            'response',
        ];

        if (in_array($key, $callable) && method_exists($this, $key)) {
            return $this->$key();
        }

        throw new \ErrorException('Undefined property ' . get_class($this) . '::' . $key);
    }

    public function response()
    {
        return app(Response::class);
    }
}
