<?php

namespace App\Tests\Service;

use App\Service\StripePaymentProcessor;
use PHPUnit\Framework\TestCase;

class StripePaymentProcessorTest extends TestCase
{
    public function testProcessPaymentSuccess(): void
    {
        $stripePaymentProcessor = new StripePaymentProcessor();

        $result = $stripePaymentProcessor->processPayment(100);

        $this->assertTrue($result, 'Payment should be successful');
    }
}
