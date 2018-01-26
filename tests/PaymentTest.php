<?php

namespace Nilnice\Payment\Test;

use Nilnice\Payment\Exception\GatewayException;
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

    public function testGatewayException()
    {
        $this->expectException(GatewayException::class);

        Payment::undefined([]);
    }
}
