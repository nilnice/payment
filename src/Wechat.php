<?php

namespace Nilnice\Payment;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nilnice\Payment\Exception\GatewayException;
use Nilnice\Payment\Wechat\Traits\RequestTrait;
use Nilnice\Payment\Wechat\Traits\SecurityTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Wechat\AppPayment app(array $array)
 * @method Wechat\WapPayment wap(array $array)
 * @method Wechat\ScanPayment scan(array $array)
 * @method Wechat\PubPayment pub(array $array)
 */
class Wechat implements GatewayInterface
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
            'appid'            => '',

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

        if ($this->config->has('log.file')) {
            $this->registerLogger($this->config, 'Wxpay');
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     *
     * @throws \Nilnice\Payment\Exception\GatewayException
     */
    public function __call(string $method, array $arguments)
    {
        $this->setAppId($method);

        return $this->dispatcher($method, ...$arguments);
    }

    /**
     * Query an order information.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     *
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     */
    public function query($order) : Collection
    {
        $this->setAppId();
        $this->payload = self::filterPayload(
            $this->payload,
            $order,
            $this->config
        );
        $gateway = Constant::WX_PAY_QUERY;

        return $this->send($gateway, $this->payload, $this->config->get('key'));
    }

    /**
     * Close an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     */
    public function close($order) : Collection
    {
        $this->setAppId();
        unset($this->payload['spbill_create_ip']);
        $this->payload = self::filterPayload(
            $this->payload,
            $order,
            $this->config
        );
        $gateway = Constant::WX_PAY_CLOSE;

        return $this->send($gateway, $this->payload, $this->config->get('key'));
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
        trigger_error('Wechat did not cancel API, please use close API.');

        return new Collection();
    }

    /**
     * Refund an order.
     *
     * @param array|string $order
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     */
    public function refund($order) : Collection
    {
        $this->payload = self::filterPayload(
            $this->payload,
            $order,
            $this->config
        );

        return $this->send(
            Constant::WX_PAY_REFUND,
            $this->payload,
            $this->config->get('key'),
            $this->config->get('cert_client'),
            $this->config->get('cert_key')
        );
    }

    /**
     * To pay.
     *
     * @param string $gateway
     *
     * @return mixed
     *
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
     *
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
     * Set app identify.
     *
     * @param string $method
     */
    private function setAppId($method = 'wap')
    {
        switch ($method) {
            case 'app': // APP 支付
                $this->payload['appid'] = $this->config->get('app_appid');
                $this->payload['mch_id'] = $this->config->get('app_mchid');
                break;
            case 'wap': // H5 支付
            case 'bar': // 刷卡支付
            case 'scan': // 扫码支付
                $this->payload['appid'] = $this->config->get('app_id');
                break;
            case 'pub': // 公众号支付
                $this->payload['appid'] = $this->config->get('pub_appid');
                break;
            case 'xcx': // 小程序支付
                $this->payload['appid'] = $this->config->get('xcx_appid');
                break;
        }
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
