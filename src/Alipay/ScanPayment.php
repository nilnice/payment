<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;

class ScanPayment extends AbstractAlipay
{
    /**
     * Use scan code to pay for order.
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
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_SCAN_PRO_CODE
        );
        self::check($content);
        $payload['method'] = Constant::ALI_PAY_SCAN_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);

        Log::debug('Scan order:', [$gateway, $payload]);

        return $this->send($payload, $this->config->get('public_key'));
    }
}
