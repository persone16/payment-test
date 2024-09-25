<?php

namespace App\Tests\Controller;

use App\Service\PaypalPaymentProcessor;
use App\Service\StripePaymentProcessor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductPricingControllerTest extends WebTestCase
{
    public function testCalculatePriceSuccess(): void
    {
        $client = static::createClient();

        $client->request('POST', '/calculate-price', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testPurchasePaypalSuccess(): void
    {
        $client = static::createClient();

        // Мокируем PayPal процессор, чтобы симулировать успешный платеж
        $paypalMock = $this->createMock(PaypalPaymentProcessor::class);
        $paypalMock->method('pay')->willReturn(true);
        self::$container->set(PaypalPaymentProcessor::class, $paypalMock);

        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'couponCode' => 'D15',
            'paymentProcessor' => 'paypal'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('Payment successful', $client->getResponse()->getContent());
    }

    public function testPurchaseStripeSuccess(): void
    {
        $client = static::createClient();

        // Мокируем Stripe процессор, чтобы симулировать успешный платеж
        $stripeMock = $this->createMock(StripePaymentProcessor::class);
        $stripeMock->method('processPayment')->willReturn(true);
        self::$container->set(StripePaymentProcessor::class, $stripeMock);

        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'IT12345678900',
            'couponCode' => 'D15',
            'paymentProcessor' => 'stripe'
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('Payment successful', $client->getResponse()->getContent());
    }

    public function testInvalidTaxNumber(): void
    {
        $client = static::createClient();

        $client->request('POST', '/purchase', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'product' => 1,
            'taxNumber' => 'INVALID123',
            'couponCode' => 'D15',
            'paymentProcessor' => 'paypal'
        ]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('error', $client->getResponse()->getContent());
    }
}
