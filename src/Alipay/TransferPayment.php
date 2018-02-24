<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Nilnice\Payment\Alipay\Traits\RequestTrait;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;

class TransferPayment extends AbstractAlipay
{
    use RequestTrait;

    /**
     * Use transfer to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Illuminate\Support\Collection
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
            Constant::ALI_PAY_TRANSFER_PRO_CODE
        );
        $payload['method'] = Constant::ALI_PAY_TRANSFER;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);

        Log::debug('Transfer order:', [$gateway, $payload]);

        return $this->send($payload, $this->config->get('public_key'));
    }
}
