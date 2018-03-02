<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;

class PubPayment extends AbstractWechat
{
    /**
     * Use pub to pay for order.
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
     */
    public function toPay(string $gateway, array $payload) : Collection
    {
        $payload['trade_type'] = Constant::WX_PAY_PUB_TYPE;
        $key = $this->config->get('key');
        $object = $this->prepare(Constant::WX_PAY_PREPARE, $payload);
        $parameter = [
            'appId'     => $payload['appid'],
            'timeStamp' => (string)time(),
            'nonceStr'  => Str::random(),
            'package'   => 'prepay_id=' . $object->get('prepay_id'),
            'signType'  => 'MD5',
        ];
        $parameter['paySign'] = self::generateSign($parameter, $key);

        Log::debug('Pub order:', [$gateway, $payload, $parameter]);

        return new Collection($parameter);
    }
}
