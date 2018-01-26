<?php

namespace Nilnice\Payment;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;
use Nilnice\Payment\Exception\GatewayException;

class Payment
{
    protected $config;

    /**
     * Payment constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Repository($config);
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    public static function __callStatic($method, $arguments)
    {
        $payment = new self(...$arguments);

        return $payment->create($method);
    }

    /**
     * @param string $method
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    protected function create(string $method)
    {
        $class = __NAMESPACE__ . '\\' . Str::studly($method);
        if (class_exists($class)) {
            return $this->make($class);
        }

        throw new GatewayException("Gateway [{$method}] not exists.");
    }

    /**
     * @param $class
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    protected function make($class)
    {
        $class = new $class($this->config);

        if ($class instanceof GatewayInterface) {
            return $class;
        }

        throw new GatewayException(
            "Gateway [$class] must be an instance of the GatewayInterface.",
            2
        );
    }
}
