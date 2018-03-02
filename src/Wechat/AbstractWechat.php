<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\PaymentInterface;
use Nilnice\Payment\Wechat\Traits\RequestTrait;
use Nilnice\Payment\Wechat\Traits\SecurityTrait;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractWechat implements PaymentInterface
{
    use RequestTrait;
    use SecurityTrait;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * AbstractWechat constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getClientIp()
    {
        return Request::createFromGlobals()->server->get('SERVER_ADDR');
    }

    /**
     * Unified order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     */
    protected function prepare(string $gateway, array $payload) : Collection
    {
        if ($payload['trade_type'] === Constant::WX_PAY_APP_TYPE) {
            $key = $this->config->get('app_key');
        } else {
            $key = $this->config->get('key');
        }
        $payload['sign'] = self::generateSign($payload, $key);

        return $this->send($gateway, $payload, $key);
    }
}
