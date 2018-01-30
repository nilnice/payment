<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nilnice\Payment\Alipay\Traits\RequestTrait;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Exception\GatewayException;
use Nilnice\Payment\Exception\InvalidKeyException;
use Nilnice\Payment\Exception\InvalidSignException;
use Nilnice\Payment\PaymentInterface;

abstract class AbstractAlipay implements PaymentInterface
{
    use RequestTrait;

    public const E_GB2312 = 'GB2312';
    public const E_UTF8 = 'UTF-8';

    /**
     * @param array  $array
     * @param string $key
     *
     * @return \Illuminate\Support\Collection
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function send(array $array, string $key) : Collection
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

    /**
     * Generate signature.
     *
     * @param array $array
     * @param null  $key
     *
     * @return string
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     */
    public static function generateSign(array $array, $key = null) : string
    {
        if ($key === null) {
            throw new InvalidKeyException(
                'Invalid Alipay [private key] configuration.',
                Constant::ALI_PAY_PRIVATE_KEY_INVALID
            );
        }

        if (Str::endsWith($key, '.pem')) {
            $key = openssl_pkey_get_private($key);
        } else {
            $key = self::getKey($key, false);
        }

        $data = self::getSignContent($array);
        openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * Get signature content.
     *
     * @param array $array
     * @param bool  $isVerify
     *
     * @return string
     */
    public static function getSignContent(
        array $array,
        $isVerify = false
    ) : string {
        $to = $array['charset'] ?? self::E_GB2312;
        $array = self::toEncoding($array, $to);
        ksort($array);

        $string = '';
        foreach ($array as $key => $val) {
            if ($isVerify && $key !== 'sign' && $key !== 'sign_type') {
                $string .= $key . '=' . $val . '&';
            }
            if (! $isVerify
                && $val !== ''
                && null !== $val
                && $key !== 'sign'
                && 0 !== strpos($val, '@')
            ) {
                $string .= $key . '=' . $val . '&';
            }
        }

        return rtrim($string, '&');
    }

    /**
     * Verify signature.
     *
     * @param array       $array
     * @param string|null $key
     * @param bool        $isSync
     * @param string|null $sign
     *
     * @return bool
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     */
    public static function verifySign(
        array $array,
        $key = null,
        $isSync = false,
        $sign = null
    ) : bool {
        if ($key === null) {
            throw new InvalidKeyException(
                'Invalid Alipay [public key] configuration.',
                Constant::ALI_PAY_PUBLIC_KEY_INVALID
            );
        }

        if (Str::endsWith($key, '.pem')) {
            $key = openssl_pkey_get_public($key);
        } else {
            $key = self::getKey($key);
        }

        $sign = $sign ?? Arr::get($array, 'sign');
        $data = $isSync
            ? mb_convert_encoding(
                json_encode($array, JSON_UNESCAPED_UNICODE),
                self::E_GB2312,
                'UTF-8'
            )
            : self::getSignContent($array, true);
        $sign = base64_decode($sign);
        $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA256) === 1;

        return $result === 1;
    }

    /**
     * To encoding.
     *
     * @param array  $array
     * @param string $to
     * @param string $from
     *
     * @return array
     */
    public static function toEncoding(
        array $array,
        $to = self::E_GB2312,
        $from = self::E_UTF8
    ) : array {
        $data = [];
        foreach ($array as $key => $val) {
            if (\is_array($val)) {
                $data[$key] = self::toEncoding((array)$val, $to, $from);
            } else {
                $data[$key] = mb_convert_encoding($val, $to, $from);
            }
        }

        return $data;
    }

    /**
     * Get key.
     *
     * @param string $key
     * @param bool   $isPublicKey
     *
     * @return string
     */
    public static function getKey(
        string $key,
        bool $isPublicKey = true
    ) : string {
        $char = $isPublicKey ? 'PUBLIC' : 'RSA PRIVATE';
        $format = "-----BEGIN %s KEY-----\n%s\n-----END %s KEY-----";
        $key = wordwrap($key, 64, PHP_EOL, true);
        $string = sprintf($format, $char, $key, $char);

        return $string;
    }

    /**
     * Check order arguments.
     *
     * @param array $order
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function check(array $order) : void
    {
        $required = ['out_trade_no', 'total_amount', 'subject'];
        foreach ($required as $key => $item) {
            if (! array_key_exists($item, $order)) {
                throw new \InvalidArgumentException("The {$item} field is required");
            }
        }
    }
}
