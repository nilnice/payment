<?php

namespace Nilnice\Payment\Test;

use Nilnice\Payment\Exception\GatewayException;
use Nilnice\Payment\Exception\InvalidKeyException;
use Nilnice\Payment\GatewayInterface;
use Nilnice\Payment\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testAlipay()
    {
        $alipay = Payment::alipay(['foo' => 'bar']);
        self::assertInstanceOf(GatewayInterface::class, $alipay);
    }

    public function testAlipayWithWeb()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => 0.01,
            'subject'      => '测试订单',
        ];
        $this->expectException(InvalidKeyException::class);
        Payment::alipay(['foo' => 'bar', 'env' => 'dev'])
               ->web($order);
    }

    public function testGatewayException()
    {
        $this->expectException(GatewayException::class);

        Payment::undefined([]);
    }
}
