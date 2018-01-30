<?php

namespace Nilnice\Payment\Alipay\Traits;

trait FormTrait
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
}
