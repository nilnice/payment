<?php

namespace Nilnice\Payment;

use Illuminate\Support\Collection;

interface GatewayInterface
{
    /**
     * Pay dispatcher.
     *
     * @param string $gateway
     * @param array  $array
     *
     * @return mixed
     */
    public function pay(string $gateway, array $array = []);

    /**
     * Query an order information.
     *
     * @param string|array $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function query($order) : Collection;
}
