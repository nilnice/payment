<?php

namespace Nilnice\Payment\Test;

use Nilnice\Payment\Alipay;
use Nilnice\Payment\Exception\GatewayException;
use Nilnice\Payment\Exception\InvalidKeyException;
use Nilnice\Payment\GatewayInterface;
use Nilnice\Payment\Wechat;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testAlipay()
    {
        $alipay = new Alipay(['foo' => 'bar']);
        self::assertInstanceOf(GatewayInterface::class, $alipay);
    }

    public function testAlipayWithWeb()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => 0.01,
            'subject'      => '支付宝-测试订单',
        ];
        $this->expectException(InvalidKeyException::class);
        $alipay = new Alipay(['foo' => 'bar', 'env' => 'dev']);
        $alipay->web($order);
    }

    public function testWechat()
    {
        $wechat = new Wechat(['foo' => 'bar']);

        self::assertInstanceOf(GatewayInterface::class, $wechat);
    }

    public function testWechatWithWap()
    {
        $order = [
            'out_trade_no' => time(),
            'total_fee'    => 1,
            'body'         => '微信支付-测试订单',
        ];
        $this->expectException(InvalidKeyException::class);
        $wechat = new Wechat(['foo' => 'bar', 'env' => 'dev']);
        $wechat->wap($order);
    }

    public function testGatewayException()
    {
        $this->expectException(GatewayException::class);

        (new Alipay([]))->test([]);
    }
}
