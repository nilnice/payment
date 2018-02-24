<?php

namespace Nilnice\Payment;

use Illuminate\Support\Collection;

interface GatewayInterface
{
    /**
     * Query an order information.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function query($order) : Collection;

    /**
     * Close an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function close($order) : Collection;

    /**
     * Cancel an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function cancel($order) : Collection;

    /**
     * Refund an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function refund($order) : Collection;
}
