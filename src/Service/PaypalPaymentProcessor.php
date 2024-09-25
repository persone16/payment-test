<?php

namespace App\Service;

class PaypalPaymentProcessor
{
    public function pay(float $amount): bool
    {
        // Логика платежа через PayPal API
        // В реальности здесь будет взаимодействие с PayPal SDK или API.

        // Для примера: допустим, что всегда успешно
        return true;
    }
}
