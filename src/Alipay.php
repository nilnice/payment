<?php

namespace Nilnice\Payment;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nilnice\Payment\Alipay\Traits\RequestTrait;
use Nilnice\Payment\Alipay\Traits\SecurityTrait;
use Nilnice\Payment\Exception\GatewayException;

class Alipay implements GatewayInterface
{
    use RequestTrait;
    use SecurityTrait;

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
     * Alipay constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $env = $config->get('env', 'pro');
        $this->gateway = self::getGatewayUrl($env);
        $this->payload = [
            // 支付宝分配给开发者的应用 ID
            'app_id'      => $this->config->get('app_id'),

            // 接口名称
            'method'      => '',

            // 数据格式，仅支持 JSON
            'format'      => 'JSON',

            // 同步返回地址，HTTP/HTTPS 开头字符串
            'return_url'  => $this->config->get('return_url'),

            // 请求使用的编码格式，如：UTF-8, GBK, GB2312 等
            'charset'     => 'utf-8',

            // 商户生成签名字符串所使用的签名算法类型，目前支持 RSA2 和 RSA，推荐使用 RSA2
            'sign_type'   => 'RSA2',

            // 商户请求参数的签名串，详见签名
            'sign'        => '',

            // 发送请求的时间，格式"yyyy-MM-dd HH:mm:ss"
            'timestamp'   => date('Y-m-d H:i:s'),

            // 调用的接口版本，固定为：1.0
            'version'     => '1.0',

            // 支付宝服务器主动通知商户服务器里指定的页面 http/https 路径
            'notify_url'  => $this->config->get('notify_url'),

            // 业务请求参数的集合，最大长度不限，除公共参数外所有请求参数都必须放在这个参数中传递，具体参照各产品快速接入文档
            'biz_content' => '',
        ];
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
        return $this->pay($method, ...$arguments);
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
    public function pay(string $gateway, array $array = [])
    {
        $this->payload['biz_content'] = $array;
        $class = \get_class($this) . '\\' . Str::studly($gateway) . 'Payment';

        if (class_exists($class)) {
            return $this->toPay($class);
        }

        throw new GatewayException("Pay gateway [{$gateway}] not exists.", 1);
    }

    /**
     * Query an order information.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function query($order) : Collection
    {
        $key = $this->config->get('private_key');
        $order = \is_array($order) ? $order : ['out_trade_no' => $order];
        $this->payload['method'] = Constant::ALI_PAY_QUERY;
        $this->payload['biz_content'] = json_encode($order);
        $this->payload['sign'] = self::generateSign($this->payload, $key);

        return $this->send($this->payload, $this->config->get('public_key'));
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
            $uri = Constant::ALI_PAY_PRO_URI;
        } elseif ($env === 'dev') {
            $uri = Constant::ALI_PAY_DEV_URI;
        }

        return $uri;
    }
}
