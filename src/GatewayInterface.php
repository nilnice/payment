<?php

namespace Nilnice\Payment;

use Illuminate\Support\Collection;

interface GatewayInterface
{
    /**
     * Query an order information.
     *
     * @param string|array $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function query($order) : Collection;
}
