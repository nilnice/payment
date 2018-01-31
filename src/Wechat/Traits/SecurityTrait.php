<?php

namespace Nilnice\Payment\Wechat\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Exception\InvalidKeyException;

trait SecurityTrait
{
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
                'Invalid Wechat [key] configuration.',
                Constant::WX_PAY_KEY_INVALID
            );
        }

        ksort($array);
        $string = md5(self::getSignContent($array) . '&key=' . $key);
        $string = strtoupper($string);

        return $string;
    }

    /**
     * Get signature content.
     *
     * @param array $array
     *
     * @return string
     */
    public static function getSignContent(array $array) : string
    {
        $string = '';
        foreach ($array as $key => $val) {
            if ($key !== 'sign' && $val !== '' && ! \is_array($val)) {
                $string .= $key . '=' . $val;
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
                'GB2312',
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
        $to = 'GB2312',
        $from = 'UTF-8'
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
}
