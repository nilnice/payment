<?php

namespace Nilnice\Payment\Alipay\Traits;

use GuzzleHttp\Client;
use Nilnice\Payment\Constant;
use Psr\Http\Message\ResponseInterface;

trait RequestTrait
{
    /**
     * Send a post request.
     *
     * @param string $gateway
     * @param mixed  $parameter
     * @param array  ...$options
     *
     * @return mixed
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
     * @throws \RuntimeException
     */
    public function request(
        string $method,
        string $gateway,
        array $options = []
    ) {
        if (property_exists($this, 'config')) {
            $baseuri = $this->config->get('env') === 'dev'
                ? Constant::ALI_PAY_DEV_URI
                : Constant::ALI_PAY_PRO_URI;
        }
        $baseuri = $baseuri ?? '';
        $timeout = property_exists($this, 'timeout') ? $this->timeout : 5.0;
        $config = ['base_uri' => $baseuri, 'timeout' => $timeout];

        $client = new Client($config);
        $response = $client->{$method}($gateway, $options);

        return $this->jsonResponse($response);
    }

    /**
     * Decodes a json/javascript/xml response contents.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
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
