<?php

namespace Blackbox\EpaceImport\Model\Payment;

class Epace extends Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'epace_payment';

    protected $_code = self::CODE;

    protected $_canUseCheckout = false;
}