<?php

namespace Nilnice\Payment;

final class Constant
{
    public const VERSION = '0.1.0';

    public const ALI_PAY = 'alipay';
    public const ALI_PAY_PRO_URI = 'https://openapi.alipay.com/gateway.do';
    public const ALI_PAY_DEV_URI = 'https://openapi.alipaydev.com/gateway.do';

    /**
     * 支付宝公共 API 列表
     */
    public const ALI_PAY_REFUND = 'alipay.trade.refund'; // 统一收单交易退款接口
    public const ALI_PAY_REFUND_QUERY = 'alipay.trade.fastpay.refund.query'; // 统一收单交易退款查询接口
    public const ALI_PAY_QUERY = 'alipay.trade.query'; // 统一收单线下交易查询接口
    public const ALI_PAY_CLOSE = 'alipay.trade.close'; // 统一收单交易关闭接口
    public const ALI_PAY_BILL_QUERY = 'alipay.data.dataservice.bill.downloadurl.query'; // 查询对账单下载地址

    /**
     * 支付宝 Web 支付相关 API 列表
     */
    public const ALI_PAY_WEB = 'ali_web'; // 支付宝 Web 支付
    public const ALI_PAY_WEB_PRO_CODE = ['product_code' => 'FAST_INSTANT_TRADE_PAY'];
    public const ALI_PAY_WEB_PAY = 'alipay.trade.page.pay'; // 统一收单下单并支付页面接口

    /**
     * 支付宝 App 支付相关 API 列表
     */
    public const ALI_PAY_APP_PRO_CODE = ['product_code' => 'QUICK_MSECURITY_PAY'];
    public const ALI_PAY_APP_PAY = 'alipay.trade.app.pay'; // 统一收单下单并支付页面接口

    /**
     * 支付宝 Wap 支付相关 API 列表
     */
    public const ALI_PAY_WAP_PRO_CODE = ['product_code' => 'QUICK_WAP_WAY'];
    public const ALI_PAY_WAP_PAY = 'alipay.trade.wap.pay'; // 手机网页支付接口

    /**
     * 支付宝 Scan 支付相关 API 列表
     */
    public const ALI_PAY_SCAN_PRO_CODE = ['product_code' => ''];
    public const ALI_PAY_SCAN_PAY = 'alipay.trade.precreate'; // 统一收单线下交易预创建（扫码支付）

    /**
     * 支付宝 Bar 支付相关 API 列表
     */
    public const ALI_PAY_BAR_PRO_CODE = ['product_code' => 'FACE_TO_FACE_PAYMENT'];
    public const ALI_PAY_BAR_PAY = 'alipay.trade.pay'; // 统一收单交易支付接口（条码支付）

    /**
     * 异常代码
     */
    public const ALI_PAY_PUBLIC_KEY_INVALID = 1001;
    public const ALI_PAY_PRIVATE_KEY_INVALID = 1002;
}
