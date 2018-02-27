<?php

namespace Nilnice\Payment\Wechat;

use Nilnice\Payment\Constant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class WapPayment extends AbstractWechat
{
    /**
     * Use wap(H5) to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload) : Response
    {
        $payload['trade_type'] = Constant::WX_PAY_WAP_TYPE;
        $object = $this->prepare(Constant::WX_PAY_PREPARE, $payload);
        $returnUrl = $this->config->get('return_url');
        $mwebUrl = $object->get('mweb_url');

        if (null === $returnUrl) {
            $url = $mwebUrl;
        } else {
            $url = $mwebUrl . '&redirect_url=' . urlencode($returnUrl);
        }

        return RedirectResponse::create($url);
    }
}
