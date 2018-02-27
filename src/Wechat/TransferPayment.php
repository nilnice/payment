<?php

namespace Nilnice\Payment\Wechat;

use Illuminate\Support\Collection;
use Nilnice\Payment\Constant;
use Nilnice\Payment\Log;

class TransferPayment extends AbstractWechat
{
    /**
     * Use transfer to pay for order.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \InvalidArgumentException
     * @throws \Nilnice\Payment\Exception\GatewayException
     * @throws \Nilnice\Payment\Exception\InvalidKeyException
     * @throws \Nilnice\Payment\Exception\InvalidSignException
     * @throws \RuntimeException
     */
    public function toPay(string $gateway, array $payload) : Collection
    {
        if (isset($payload['type'])) {
            $type = $payload['type'] . ($payload['type'] === 'app' ?: '_')
                . 'id';
        } else {
            $type = 'app_id';
        }
        $payload['mch_appid'] = $this->config->get($type, '');
        $payload['mchid'] = $payload['mch_id'];
        $payload['spbill_create_ip'] = $this->getClientIp();

        unset(
            $payload['appid'],
            $payload['mch_id'],
            $payload['trade_type'],
            $payload['notify_url'],
            $payload['type']
        );
        $gateway .= Constant::WX_PAY_TRANSFER;
        $key = $this->config->get('key');
        $certClient = $this->config->get('cert_client');
        $certKey = $this->config->get('cert_key');
        $payload['sign'] = self::generateSign($payload, $key);

        Log::debug('Transfer order:', [$gateway, $payload]);

        return $this->send($gateway, $payload, $key, $certClient, $certKey);
    }
}
