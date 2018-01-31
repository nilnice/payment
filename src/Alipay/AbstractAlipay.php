<?php

namespace Nilnice\Payment\Alipay;

use Illuminate\Config\Repository;
use Nilnice\Payment\Alipay\Traits\RequestTrait;
use Nilnice\Payment\Alipay\Traits\SecurityTrait;
use Nilnice\Payment\PaymentInterface;

abstract class AbstractAlipay implements PaymentInterface
{
    use SecurityTrait;
    use RequestTrait;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * AbstractAlipay constructor.
     *
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Check order arguments.
     *
     * @param array $order
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function check(array $order) : void
    {
        $required = ['out_trade_no', 'total_amount', 'subject'];
        foreach ($required as $key => $item) {
            if (! array_key_exists($item, $order)) {
                throw new \InvalidArgumentException("The {$item} field is required");
            }
        }
    }
}
