<?php

namespace Nilnice\Payment;

final class Constant
{
    // 版本
    public const VERSION = '0.2.0';

    /**
     * 支付宝公共 API 列表
     */

    // 支付宝
    public const ALI_PAY_NAME = 'alipay';

    // 正式环境
    public const ALI_PAY_PRO_URI = 'https://openapi.alipay.com/gateway.do';

    // 沙箱环境
    public const ALI_PAY_DEV_URI = 'https://openapi.alipaydev.com/gateway.do';

    // 统一收单交易退款查询
    public const ALI_PAY_REFUND_QUERY = 'alipay.trade.fastpay.refund.query';

    // 统一收单交易结算接口
    public const ALI_PAY_SETTLE = 'alipay.trade.order.settle';

    // 统一收单交易关闭接口
    public const ALI_PAY_CLOSE = 'alipay.trade.close';

    // 统一收单交易撤销接口
    public const ALI_PAY_CANCEL = 'alipay.trade.cancel';

    // 统一收单交易退款接口
    public const ALI_PAY_REFUND = 'alipay.trade.refund';

    // 统一收单线下交易预创建
    public const ALI_PAY_PRECREATE = 'alipay.trade.precreate';

    // 统一收单交易创建接口
    public const ALI_PAY_CREATE = 'alipay.trade.create';

    // 统一收单交易支付接口
    public const ALI_PAY_PAY = 'alipay.trade.pay';

    // 统一收单线下交易查询
    public const ALI_PAY_QUERY = 'alipay.trade.query';

    // 查询对账单下载地址
    public const ALI_PAY_BILL_QUERY = 'alipay.data.dataservice.bill.downloadurl.query';

    /**
     * 支付宝电脑网站支付 - 用户在商家网站上完成付款
     */

    // 支付宝 Web 支付
    public const ALI_PAY_WEB = 'ali_web';

    public const ALI_PAY_WEB_PRO_CODE = ['product_code' => 'FAST_INSTANT_TRADE_PAY'];

    // 统一收单下单并支付页面接口
    public const ALI_PAY_WEB_PAY = 'alipay.trade.page.pay';

    /**
     * 支付宝手机网站支付 - 用户在商家手机网站进行付款
     */
    public const ALI_PAY_WAP_PRO_CODE = ['product_code' => 'QUICK_WAP_WAY'];
    public const ALI_PAY_WAP_PAY = 'alipay.trade.wap.pay'; // 手机网页支付接口

    /**
     * 支付宝 APP 支付 - 用户在商家 app 内进行付款
     */
    public const ALI_PAY_APP_PRO_CODE = ['product_code' => 'QUICK_MSECURITY_PAY'];
    public const ALI_PAY_APP_PAY = 'alipay.trade.app.pay'; // 统一收单下单并支付页面接口


    /**
     * 支付宝当面付 - 用户扫描商家的二维码完成付款
     */
    public const ALI_PAY_SCAN_PRO_CODE = ['product_code' => ''];
    public const ALI_PAY_SCAN_PAY = 'alipay.trade.precreate'; // 统一收单线下交易预创建（扫码支付）

    /**
     * 支付宝当面付 - 商家扫描用户的付款码完成付款
     */
    public const ALI_PAY_BAR_PRO_CODE = ['product_code' => 'FACE_TO_FACE_PAYMENT'];
    public const ALI_PAY_BAR_PAY = 'alipay.trade.pay'; // 统一收单交易支付接口（条码支付）

    /**
     * 支付宝转账
     */

    // 单笔转账到支付宝账户接口
    public const ALI_PAY_TRANSFER = 'alipay.fund.trans.toaccount.transfer';

    // 查询转账订单接口
    public const ALI_PAY_TRANSFER_QUERY = 'alipay.fund.trans.order.query';

    public const ALI_PAY_TRANSFER_PRO_CODE = ['product_code' => ''];

    /**
     * 微信支付公共 API 列表
     */

    // 国内接入点
    public const WX_PAY_PRO_URI = 'https://api.mch.weixin.qq.com/';

    // 仿真接入点
    public const WX_PAY_DEV_URI = 'https://api.mch.weixin.qq.com/sandboxnew/';

    // 东南亚接入点
    public const WX_PAY_PRO_HK_URI = 'https://apihk.mch.weixin.qq.com/';

    // 刷卡支付接口
    public const WX_PAY_POS = 'pay/micropay';

    // 统一下单接口
    public const WX_PAY_PREPARE = 'pay/unifiedorder';

    // 查询订单接口
    public const WX_PAY_QUERY = 'pay/orderquery';

    // 关闭订单
    public const WX_PAY_CLOSE = 'pay/closeorder';

    // 撤销订单接口
    public const WX_PAY_REVERSE = 'pay/reverse';

    // 申请退款接口
    public const WX_PAY_REFUND = 'secapi/pay/refund';

    // 查询退款接口
    public const WX_PAY_REFUND_QUERY = 'pay/refundquery';

    // 下载对账单接口
    public const WX_PAY_DOWNLOAD_BILL = 'pay/downloadbill';

    // 交易保障接口
    public const WX_PAY_REPORT = 'payitil/report';

    // 转换短链接接口
    public const WX_PAY_SHORT_URL = 'tools/shorturl';

    // 授权码查询 openid 接口
    public const WX_PAY_AUTH_OPENID = 'tools/authcodetoopenid';

    // 拉取订单评价数据接口
    public const WX_PAY_QUERY_COMMENT = 'billcommentsp/batchquerycomment';

    // 企业付款接口
    public const WX_PAY_TRANSFER = 'mmpaymkttransfers/promotion/transfers';

    /**
     * 微信刷卡支付 - 用户打开微信钱包的刷卡的界面，商户扫码后提交完成支付
     */
    public const WX_PAY_BAR_PAY = '';

    /**
     * 微信扫码支付 - 用户打开微信扫一扫，扫描商户的二维码后完成支付
     */
    public const WX_PAY_SCAN_TYPE = 'NATIVE';

    /**
     * 微信公众号支付 - 用户在微信内进入商家 H5 页面，页面内调用 JSSDK 完成支付
     */
    public const WX_PAY_PUB_TYPE = 'NATIVE';

    /**
     * 微信 APP 支付 - 商户 APP 中集成微信 SDK，用户点击后跳转到微信内完成支付
     */
    public const WX_PAY_APP_TYPE = 'APP';

    /**
     * 微信 Wap(H5) 支付 - 用户在微信以外的手机浏览器请求微信支付的场景唤起微信支付
     */
    public const WX_PAY_WAP_TYPE = 'MWEB';

    /**
     * 微信小程序支付 - 用户在微信小程序中使用微信支付的场景
     */
    public const WX_PAY_XCX_TYPE = 'MWEB';

    /**
     * 异常代码
     */
    public const ALI_PAY_PUBLIC_KEY_INVALID = 10001;
    public const ALI_PAY_PRIVATE_KEY_INVALID = 10002;
    public const ALI_PAY_SUCCESS = 10200;
    public const WX_PAY_KEY_INVALID = 11001;
    public const WX_PAY_SUCCESS = 11200;
}
