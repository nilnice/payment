<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Config\Repository;
use Illuminate\Support\{
    Arr, Collection
};
use Nilnice\Payment\Alipay\Traits\WebTrait;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Exception\{
    GatewayException, InvalidSignException
};

class ScanPayment extends AbstractAlipay
{
    use WebTrait;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * ScanPayment constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Use scan code to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return mixed|void
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload)
    {
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_SCAN_PRO_CODE
        );
        $this->check($content);
        $payload['method'] = Constant::ALI_PAY_SCAN_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);

        return $this->send($payload, $this->config->get('public_key'));
    }
}
