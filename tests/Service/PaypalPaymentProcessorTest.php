<?php

namespace App\Tests\Service;

use App\Service\PaypalPaymentProcessor;
use PHPUnit\Framework\TestCase;

class PaypalPaymentProcessorTest extends TestCase
{
    public function testPaySuccess(): void
    {
        $paypalPaymentProcessor = new PaypalPaymentProcessor();

        $result = $paypalPaymentProcessor->pay(100);

        $this->assertTrue($result, 'Payment should be successful');
    }
}
