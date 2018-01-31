<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\PaymentInterface;
use Nilnice\Payment\Wechat\Traits\RequestTrait;
use Nilnice\Payment\Wechat\Traits\SecurityTrait;

abstract class AbstractWechat implements PaymentInterface
{
    use SecurityTrait;
    use RequestTrait;

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
     * Pregenerating order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    protected function prepare(string $gateway, array $payload) : Collection
    {
        $env = $this->config->get('env', 'pro');
        $key = $this->config->get('key');
        $payload['sign'] = self::generateSign($payload, $key);
        $gateway = self::getGatewayUrl($env) . $gateway;

        return $this->send($gateway, $payload, $key);
    }

    /**
     * Get gateway url.
     *
     * @param string $env
     *
     * @return string
     */
    private static function getGatewayUrl($env = '') : string
    {
        $uri = '';
        switch ($env) {
            case 'pro':
                $uri = Constant::WX_PAY_PRO_URI;
                break;
            case 'dev':
                $uri = Constant::WX_PAY_DEV_URI;
                break;
            case 'hk':
                $uri = Constant::WX_PAY_PRO_HK_URI;
                break;
            default:
                break;
        }

        return $uri;
    }
}
