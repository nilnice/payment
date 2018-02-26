<?php

namespace Nilnice\Payment;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nilnice\Payment\Alipay\Traits\RequestTrait;
use Nilnice\Payment\Alipay\Traits\SecurityTrait;
use Nilnice\Payment\Exception\GatewayException;

/**
 * @method Alipay\AppPayment app(array $array)
 * @method Alipay\BarPayment bar(array $array)
 * @method Alipay\ScanPayment scan(array $array)
 * @method Alipay\TransferPayment transfer(array $array)
 * @method Alipay\WapPayment wap(array $array)
 * @method Alipay\WebPayment web(array $array)
 */
class Alipay implements GatewayInterface
{
    use LogTrait;
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
            // 支付宝分配给开发者的应用 ID
            'app_id'      => $this->config->get('app_id'),

            // 接口名称
            'method'      => '',

            // 数据格式，仅支持 JSON
            'format'      => 'JSON',

            // 同步返回地址，HTTP/HTTPS 开头字符串
            'return_url'  => $this->config->get('return_url'),

            // 请求使用的编码格式，如：UTF-8, GBK, GB2312 等
            'charset'     => 'UTF-8',

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
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function query($order) : Collection
    {
        return $this->getOrderResult($order);
    }

    /**
     * Close an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function close($order) : Collection
    {
        return $this->getOrderResult($order, __FUNCTION__);
    }

    /**
     * Cancel an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function cancel($order) : Collection
    {
        return $this->getOrderResult($order, __FUNCTION__);
    }

    /**
     * Refund an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function refund($order) : Collection
    {
        return $this->getOrderResult($order, __FUNCTION__);
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
        $this->payload['biz_content'] = $array;
        $class = \get_class($this) . '\\' . Str::studly($gateway) . 'Payment';

        if (class_exists($class)) {
            return $this->toPay($class);
        }

        throw new GatewayException("Pay gateway [{$gateway}] not exists.", 1);
    }

    /**
     * Get order result.
     *
     * @param array|string $order
     * @param string       $type
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    private function getOrderResult($order, $type = 'query') : Collection
    {
        $order = \is_array($order) ? $order : ['out_trade_no' => $order];
        switch (trim($type)) {
            case 'close':
                $method = Constant::ALI_PAY_CLOSE;
                break;
            case 'cancel':
                $method = Constant::ALI_PAY_CANCEL;
                break;
            case 'refund':
                $method = Constant::ALI_PAY_REFUND;
                break;
            default:
                $method = Constant::ALI_PAY_QUERY;
                break;
        }
        $this->payload['method'] = $method;
        $this->payload['biz_content'] = json_encode($order);
        $this->payload['sign'] = self::generateSign(
            $this->payload,
            $this->config->get('private_key')
        );
        Log::debug(ucfirst($type) . ' an order:', [
            $this->gateway,
            $this->payload,
        ]);

        return $this->send($this->payload, $this->config->get('public_key'));
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
