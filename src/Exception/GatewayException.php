<?php

namespace Nilnice\Payment\Exception;

use Exception;
use Throwable;

class GatewayException extends Exception
{
    /**
     * GatewayException constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
