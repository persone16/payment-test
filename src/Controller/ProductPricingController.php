<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductPricingController extends AbstractController
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @Route("/calculate-price", name="calculate_price", methods={"POST"})
     */
    public function calculatePrice(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Валидация запроса (символически, здесь нужна полная реализация)
        $productId = $data['product'] ?? null;
        $taxNumber = $data['taxNumber'] ?? null;
        $couponCode = $data['couponCode'] ?? null;

        // Логика поиска продукта и купона
        $product = $this->getDoctrine()->getRepository(Product::class)->find($productId);
        $coupon = $this->getDoctrine()->getRepository(Coupon::class)->findOneBy(['code' => $couponCode]);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 400);
        }

        $price = $product->getPrice();

        // Применение купона
        if ($coupon) {
            if ($coupon->isPercentage()) {
                $price -= ($price * $coupon->getDiscount() / 100);
            } else {
                $price -= $coupon->getDiscount();
            }
        }

        // Расчет налога на основе taxNumber
        $price += $this->calculateTax($price, $taxNumber);

        return new JsonResponse(['price' => $price], 200);
    }

    private function calculateTax(float $price, string $taxNumber): float
    {
        $taxRate = 0;
        if (preg_match('/^DE\d{9}$/', $taxNumber)) {
            $taxRate = 0.19;
        } elseif (preg_match('/^IT\d{11}$/', $taxNumber)) {
            $taxRate = 0.22;
        } elseif (preg_match('/^FR[A-Z]{2}\d{9}$/', $taxNumber)) {
            $taxRate = 0.20;
        } elseif (preg_match('/^GR\d{9}$/', $taxNumber)) {
            $taxRate = 0.24;
        }

        return $price * $taxRate;
    }

    /**
     * @Route("/purchase", name="purchase", methods={"POST"})
     */
    public function purchase(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Валидация и получение данных (упрощенная логика)
        $productId = $data['product'] ?? null;
        $taxNumber = $data['taxNumber'] ?? null;
        $paymentProcessor = $data['paymentProcessor'] ?? null;

        $product = $this->getDoctrine()->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 400);
        }

        $price = $product->getPrice();
        $price += $this->calculateTax($price, $taxNumber);

        // Выбор платежного процессора
        switch ($paymentProcessor) {
            case 'paypal':
                $isPaymentSuccessful = $this->paymentService->processPaypalPayment($price * 100); // В центах
                break;
            case 'stripe':
                $isPaymentSuccessful = $this->paymentService->processStripePayment($price); // В евро
                break;
            default:
                return new JsonResponse(['error' => 'Invalid payment processor'], 400);
        }

        if ($isPaymentSuccessful) {
            return new JsonResponse(['message' => 'Payment successful'], 200);
        } else {
            return new JsonResponse(['error' => 'Payment failed'], 400);
        }
    }
}
