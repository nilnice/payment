<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;

class ScanPayment extends AbstractWechat
{
    /**
     * Use scan to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload) : Collection
    {
        $payload['spbill_create_ip'] = $this->getClientIp();
        $payload['trade_type'] = Constant::WX_PAY_SCAN_TYPE;
        $gateway .= Constant::WX_PAY_PREPARE;

        Log::debug('Scan order:', [$gateway, $payload]);

        return $this->prepare($gateway, $payload, 'scan');
    }
}
