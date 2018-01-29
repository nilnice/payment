<?php

namespace Nilnice\Payment\Alipay;

use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Nilnice\Payment\Constant;

class AppPayment extends AbstractAlipay
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * AppPayment constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Use app to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \GuzzleHttp\Psr7\Response|mixed
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     */
    public function toPay(string $gateway, array $payload)
    {
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_APP_PRO_CODE
        );
        $payload['method'] = Constant::ALI_PAY_APP_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);
        $body = http_build_query($payload);

        return new Response(200, [], $body);
    }
}
