<?php

namespace App\Service;

class StripePaymentProcessor
{
    public function processPayment(float $amount): bool
    {
        // Логика платежа через Stripe API
        // В реальной ситуации здесь было бы взаимодействие с Stripe SDK или API.

        // Для примера: допустим, что всегда успешно
        return true;
    }
}
