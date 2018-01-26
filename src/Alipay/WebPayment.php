<?php

namespace Nilnice\Payment\Alipay;

use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Nilnice\Payment\Constant;

class WebPayment extends AbstractAlipay
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * WebPayment constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * To pay.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return string
     * @throws \Nilnice\Payment\Exception\InvalidConfigException|\InvalidArgumentException
     */
    public function toPay(string $gateway, array $payload)
    {
        $key = $this->config->get('private_key');
        $content = array_merge(
            Arr::get($payload, 'biz_content'),
            Constant::ALI_PAY_PRO_CODE
        );
        $this->check($content);
        $payload['method'] = Constant::ALI_PAY_WEB_PAY;
        $payload['biz_content'] = json_encode($content);
        $payload['sign'] = self::generateSign($payload, $key);

        $body = $this->buildRequestForm($gateway, $payload);

        return new Response(200, [], $body);
    }

    /**
     * Build request form.
     *
     * @param string $gateway
     * @param array  $payload
     *
     * @return string
     */
    public function buildRequestForm(string $gateway, array $payload) : string
    {
        $format
            = <<<HTML
<form id="alipaysubmit" name="alipaysubmit" action="%s" method="post">
    %s
    <input type="submit" value="ok" style="display: none">
    <script>document.forms['alipaysubmit'].submit()</script>
</form>
HTML;
        $input = '';
        foreach ($payload as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $input .= "<input type='hidden' name='{$key}' value='{$val}'>";
        }
        $html = sprintf($format, $gateway, $input);

        return $html;
    }

    /**
     * Check order arguments.
     *
     * @param array $order
     *
     * @throws \InvalidArgumentException
     */
    private function check(array $order)
    {
        $required = ['out_trade_no', 'total_amount', 'subject'];
        foreach ($required as $key => $item) {
            if (! array_key_exists($item, $order)) {
                throw new \InvalidArgumentException("The {$item} field is required");
            }
        }
    }
}
