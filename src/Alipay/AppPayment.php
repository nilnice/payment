<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Support\Arr;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;
use Symfony\Component\HttpFoundation\Response;

class AppPayment extends AbstractAlipay
{
    /**
     * Use app to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     */
    public function toPay(string $gateway, array $payload) : Response
    {
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_APP_PRO_CODE
        );
        self::check($content);
        $payload['method'] = Constant::ALI_PAY_APP_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);

        Log::debug('App order:', [$gateway, $payload]);

        $body = http_build_query($payload);

        return Response::create($body);
    }
}
