<?php

namespace Nilnice\Payment;

final class Constant
{
    public const VERSION = '0.1.0';

    public const ALI_PAY = 'alipay';
    public const ALI_PAY_PRO_URI = 'https://openapi.alipay.com/gateway.do';
    public const ALI_PAY_DEV_URI = 'https://openapi.alipaydev.com/gateway.do';
    public const ALI_PAY_PRO_CODE = ['product_code' => 'FAST_INSTANT_TRADE_PAY'];

    /**
     * 支付宝 Web 支付相关 API 列表
     */
    public const ALI_PAY_WEB = 'ali_web'; // 支付宝 Web 支付
    public const ALI_PAY_WEB_PAY = 'alipay.trade.page.pay'; // 统一收单下单并支付页面接口
    public const ALI_PAY_WEB_REFUND = 'alipay.trade.refund'; // 统一收单交易退款接口
    public const ALI_PAY_WEB_REFUND_QUERY = 'alipay.trade.fastpay.refund.query'; // 统一收单交易退款查询接口
    public const ALI_PAY_WEB_QUERY = 'alipay.trade.query'; // 统一收单线下交易查询接口
    public const ALI_PAY_WEB_CLOSE = 'alipay.trade.close'; // 统一收单交易关闭接口
    public const ALI_PAY_WEB_BILL_DOWNLOAD = 'alipay.data.dataservice.bill.downloadurl.query'; // 查询对账单下载地址

    /**
     * 异常代码
     */
    public const ALI_PAY_PUBLIC_KEY_INVALID = 1001;
    public const ALI_PAY_PRIVATE_KEY_INVALID = 1002;
}
