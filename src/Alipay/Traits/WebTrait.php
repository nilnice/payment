<?php

namespace Nilnice\Payment\Alipay\Traits;

trait WebTrait
{
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
     * @return void
     * @throws \InvalidArgumentException
     */
    public function check(array $order) : void
    {
        $required = ['out_trade_no', 'total_amount', 'subject'];
        foreach ($required as $key => $item) {
            if (! array_key_exists($item, $order)) {
                throw new \InvalidArgumentException("The {$item} field is required");
            }
        }
    }
}
