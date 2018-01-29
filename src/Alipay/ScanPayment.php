<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
     * Scan terminal to pay.
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

    /**
     * Send a request.
     *
     * @param array  $array
     * @param string $key
     *
     * @return \Illuminate\Support\Collection
     * @throws \RuntimeException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function sendRequest(array $array, string $key) : Collection
    {
        $method = Arr::get($array, 'method');
        $method = str_replace('.', '_', $method) . '_response';
        $result = $this->post('', $array);
        $result = mb_convert_encoding($result, self::E_UTF8, self::E_GB2312);
        $result = json_decode($result, true);


        $data = Arr::get($result, $method);
        $sign = Arr::get($result, 'sign');
        if (! self::verifySign($data, $key, true, $sign)) {
            throw new InvalidSignException(
                'Invalid Alipay [signature] verify.',
                3
            );
        }

        if ('10000' === $code = Arr::get($result, "{$method}.code")) {
            return new Collection($data);
        }

        throw new GatewayException(
            "Gateway Alipay [{$data['msg']}] error.",
            $code
        );
    }
}
