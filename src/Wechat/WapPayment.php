<?php

namespace Nilnice\Payment\Wechat;

use Nilnice\Payment\Constant;

class WapPayment extends AbstractWechat
{
    /**
     * To pay.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload)
    {
        $payload['trade_type'] = Constant::WX_PAY_WAP_TYPE;
        $object = $this->prepare(Constant::WX_PAY_PREPARE_URI, $payload);
        $returnUrl = $this->config->get('return_url');

        if (null === $returnUrl) {
            $url = $object->url;
        } else {
            $url = $object->url . '&redirect_url=' . urlencode($returnUrl);
        }
        echo $url;
    }
}
