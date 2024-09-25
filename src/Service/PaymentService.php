<?php

namespace App\Service;

use Exception;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentService
{
    private StripePaymentProcessor $stripePaymentProcessor;
    private PaypalPaymentProcessor $paypalPaymentProcessor;

    public function __construct(
        PaypalPaymentProcessor $paypalPaymentProcessor,
        StripePaymentProcessor $stripePaymentProcessor
    ) {
        $this->paypalPaymentProcessor = $paypalPaymentProcessor;
        $this->stripePaymentProcessor = $stripePaymentProcessor;
    }

    /**
     * Выполняем платеж через PayPal.
     *
     * @param int $amount сумма в минимальных единицах (например, центы).
     * @return bool
     * @throws Exception
     */
    public function processPaypalPayment(int $amount): bool
    {
        try {
            $this->paypalPaymentProcessor->pay($amount);
            return true;
        } catch (Exception $e) {
            // Логируем ошибку или возвращаем информацию о проблеме
            // log($e->getMessage());
            return false;
        }
    }

    /**
     * Выполняем платеж через Stripe.
     *
     * @param float $amount сумма в стандартных единицах валюты (например, в евро).
     * @return bool true в случае успешного платежа, false в случае неудачи.
     */
    public function processStripePayment(float $amount): bool
    {
        return $this->stripePaymentProcessor->processPayment($amount);
    }
}

