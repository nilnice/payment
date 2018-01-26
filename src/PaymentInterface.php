<?php

namespace Nilnice\Payment;

interface PaymentInterface
{
    /**
     * To pay.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return mixed
     */
    public function toPay(string $gateway, array $payload);
}
