<?php

namespace Nilnice\Payment;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nilnice\Payment\Exception\GatewayException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Wechat\WapPayment wap(array $array)
 */
class Wechat implements GatewayInterface
{
    use LogTrait;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var string
     */
    protected $gateway;

    /**
     * Wechat constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->config = new Repository($config);
        $env = $this->config->get('env', 'pro');
        $this->gateway = self::getGatewayUrl($env);
        $this->payload = [
            // 公众账号 ID - 微信分配的公众账号 ID
            'appid'            => $this->config->get('app_id', ''),

            // 商户号 - 微信支付分配的商户号
            'mch_id'           => $this->config->get('mch_id', ''),

            // 随机字符串	 - 随机字符串，不长于32位
            'nonce_str'        => Str::random(),

            // 签名
            'sign'             => '',

            // 终端 IP - 必须传正确的客户端 IP
            'spbill_create_ip' => Request::createFromGlobals()->getClientIp(),

            // 通知地址 - 接收微信支付异步通知回调地址，通知 url 必须为直接可访问的 url，不能携带参数
            'notify_url'       => $this->config->get('notify_url'),

            // 交易类型 - H5 支付的交易类型为 MWEB
            'trade_type'       => '',
        ];

        if (! $this->config->has('log.file')) {
            $this->registerLogger($this->config);
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    public function __call(string $method, array $arguments)
    {
        return $this->dispatcher($method, ...$arguments);
    }


    /**
     * Query an order information.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function query($order) : Collection
    {
        return new Collection([]);
    }

    /**
     * Close an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function close($order) : Collection
    {
        return new Collection([]);
    }

    /**
     * Cancel an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function cancel($order) : Collection
    {
        return new Collection([]);
    }

    /**
     * Refund an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function refund($order) : Collection
    {
        return new Collection([]);
    }

    /**
     * To pay.
     *
     * @param string $gateway
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    protected function toPay(string $gateway)
    {
        $class = new $gateway($this->config); // Instantiate different gateways.

        if ($class instanceof PaymentInterface) {
            return $class->toPay($this->gateway, $this->payload);
        }

        throw new GatewayException(
            "Pay gateway [{$gateway}] must be an instance of the GatewayInterface.",
            2
        );
    }

    /**
     * Pay dispatcher.
     *
     * @param string $gateway
     * @param array  $array
     *
     * @return mixed
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    private function dispatcher(string $gateway, array $array = [])
    {
        $this->payload = array_merge($this->payload, $array);
        $class = \get_class($this) . '\\' . Str::studly($gateway) . 'Payment';

        if (class_exists($class)) {
            return $this->toPay($class);
        }

        throw new GatewayException("Pay gateway [{$gateway}] not exists", 1);
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
        if ($env === 'pro') {
            $uri = Constant::WX_PAY_PRO_URI;
        } elseif ($env === 'dev') {
            $uri = Constant::WX_PAY_DEV_URI;
        }

        return $uri;
    }
}
