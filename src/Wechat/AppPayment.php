<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Support\Str;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AppPayment extends AbstractWechat
{
    /**
     * Use app to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload) : Response
    {
        $payload['trade_type'] = Constant::WX_PAY_APP_TYPE;
        $object = $this->prepare(Constant::WX_PAY_PREPARE, $payload, 'app');
        $key = $this->config->get('key');
        $parameter = [
            'appid'     => $payload['appid'],
            'partnerid' => $payload['mch_id'],
            'prepayid'  => $object->get('prepay_id'),
            'timestamp' => (string)time(),
            'noncestr'  => Str::random(),
            'package'   => 'Sign=WXPay',
        ];
        $parameter['sign'] = self::generateSign($parameter, $key);

        Log::debug('App order:', [$gateway, $payload]);

        return JsonResponse::create($parameter);
    }
}
