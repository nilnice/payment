<?php

namespace Nilnice\Payment\Alipay;

use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Nilnice\Payment\Alipay\Traits\FormTrait;
use Nilnice\Payment\Constant;

class WebPayment extends AbstractAlipay
{
    use FormTrait;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * WebPayment constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Use web to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \GuzzleHttp\Psr7\Response
     * @throws \Nilnice\Payment\Exception\InvalidKeyException|\InvalidArgumentException
     */
    public function toPay(string $gateway, array $payload) : Response
    {
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_WEB_PRO_CODE
        );
        self::check($content);
        $payload['method'] = Constant::ALI_PAY_WEB_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);
        $body = $this->buildRequestForm($gateway, $payload);

        return new Response(200, [], $body);
    }
}
