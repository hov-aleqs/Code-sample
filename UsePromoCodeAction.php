<?php

namespace App\Services\Payment\Subscription\Actions;

use App\Repositories\Read\Admin\PromoCode\PromoCodeReadRepositoryInterface;
use App\Repositories\Read\Payment\Plan\PlanReadRepositoryInterface;

class UsePromoCodeAction
{
    const DISCOUNTED_AMOUNT = 'discounted_amount';
    const PAY_AMOUNT = 'pay_amount';

    protected PlanReadRepositoryInterface $planReadRepository;
    protected PromoCodeReadRepositoryInterface $promoCodeReadRepository;

    public function __construct(
        PlanReadRepositoryInterface $planReadRepository,
        PromoCodeReadRepositoryInterface $promoCodeReadRepository
    ) {
        $this->planReadRepository = $planReadRepository;
        $this->promoCodeReadRepository = $promoCodeReadRepository;
    }

    public function run(string $planId, int $cycleCount, string $promoCode): array
    {
        $plan = $this->planReadRepository->getById($planId);
        $promoCode = $this->promoCodeReadRepository->getByCode($promoCode);
        $amount = $plan->getPrice() * $cycleCount;

        $discountedAmount = $amount * $promoCode->getDiscountValue() / 100;
        $payAmount = $amount - $discountedAmount;

        return [
            self::DISCOUNTED_AMOUNT => $discountedAmount,
            self::PAY_AMOUNT => $payAmount
        ];
    }
}
