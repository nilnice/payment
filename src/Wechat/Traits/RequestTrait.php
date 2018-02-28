<?php

namespace Nilnice\Payment\Wechat\Traits;

use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Exception\GatewayException;
use Nilnice\Payment\Exception\InvalidSignException;
use Psr\Http\Message\ResponseInterface;

trait RequestTrait
{
    use SecurityTrait;

    /**
     * Send a Wechat interface request.
     *
     * @param string      $gateway
     * @param array       $array
     * @param string      $key
     * @param string|null $certClient
     * @param string|null $certKey
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function send(
        string $gateway,
        array $array,
        string $key,
        string $certClient = null,
        string $certKey = null
    ) : Collection {
        $cert = ($certClient !== null && $certKey !== null)
            ? ['cert' => $certClient, 'ssl_key' => $certKey]
            : [];
        $result = $this->post($gateway, self::toXml($array), $cert);
        $result = \is_array($result) ? $result : self::fromXml($result);

        $flag = 'SUCCESS';
        $returnCode = Arr::get($result, 'return_code');
        $resultCode = Arr::get($result, 'result_code');

        if ($flag !== $returnCode || $flag !== $resultCode) {
            throw new GatewayException(
                'Wxpay API Error: ' . $result['return_msg'],
                20000
            );
        }

        if (self::generateSign($result, $key) === $result['sign']) {
            return new Collection($result);
        }

        throw new InvalidSignException(
            'Invalid Wxpay [signature] verify.',
            3
        );
    }

    /**
     * Send a post request.
     *
     * @param string $gateway
     * @param mixed  $parameter
     * @param array  ...$options
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function post(
        string $gateway,
        $parameter = null,
        ...$options
    ) {
        $options = $options[0] ?? [];
        if (\is_array($parameter)) {
            $options['form_params'] = $parameter;
        } else {
            $options['body'] = $parameter;
        }

        return $this->request('post', $gateway, $options);
    }

    /**
     * Send a request.
     *
     * @param string $method
     * @param string $gateway
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function request(
        string $method,
        string $gateway,
        array $options = []
    ) {
        if (property_exists($this, 'config')) {
            $baseuri = $this->config->get('env') === 'dev'
                ? Constant::WX_PAY_DEV_URI
                : Constant::WX_PAY_PRO_URI;
        }
        $baseuri = $baseuri ?? '';
        $timeout = property_exists($this, 'timeout') ? $this->timeout : 5.0;
        $config = ['base_uri' => $baseuri, 'timeout' => $timeout];

        $client = new Client($config);
        $response = $client->{$method}($gateway, $options);

        return $this->jsonResponse($response);
    }

    /**
     * Filter payload.
     *
     * @param array                         $payload
     * @param array|string                  $order
     * @param \Illuminate\Config\Repository $config
     *
     * @return array
     */
    public static function filterPayload(
        array $payload,
        $order,
        Repository $config
    ) : array {
        $order = \is_array($order) ? $order : ['out_trade_no' => $order];
        $payload = array_merge($payload, $order);

        unset($payload['notify_url'], $payload['trade_type'], $payload['type']);

        $payload['sign'] = self::generateSign($payload, $config->get('key'));

        return $payload;
    }

    /**
     * Convert array to xml.
     *
     * @param array $array
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function toXml(array $array) : string
    {
        if (empty($array)) {
            throw new \InvalidArgumentException('Invalid [array] argument.', 2);
        }

        $xml = '<xml>';
        foreach ($array as $key => $val) {
            $xml .= is_numeric($val)
                ? "<{$key}>{$val}</{$key}>"
                : "<{$key}><![CDATA[{$val}]]></{$key}>";
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * Convert xml to array.
     *
     * @param string $xml
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public static function fromXml(string $xml) : array
    {
        if (! $xml) {
            throw new \InvalidArgumentException('Invalid [xml] argument.', 3);
        }
        libxml_disable_entity_loader(true);
        $array = simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            LIBXML_NOCDATA
        );
        $array = json_decode(
            json_encode($array, JSON_UNESCAPED_UNICODE),
            true
        );

        return $array;
    }

    /**
     * Decodes a json/javascript/xml response contents.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    protected function jsonResponse(ResponseInterface $response)
    {
        $type = $response->getHeaderLine('Content-Type');
        $content = $response->getBody()->getContents();

        if (false !== self::contains($type, 'json')) {
            $content = json_decode($content, true);
        }

        return $content;
    }

    /**
     * Find the position of the first occurrence of a substring in a string.
     *
     * @param string $needle
     * @param string $haystack
     * @param bool   $isStrict
     *
     * @return bool|int
     */
    private static function contains(
        string $needle,
        string $haystack,
        bool $isStrict = false
    ) {
        return $isStrict
            ? strpos($haystack, $needle)
            : stripos($haystack, $needle);
    }
}
