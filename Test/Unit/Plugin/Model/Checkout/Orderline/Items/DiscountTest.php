<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin\Model\Checkout\Orderline\Items;

use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Plugin\Model\Checkout\Orderline\Items\Discount;
use Magento\Quote\Model\Quote;
use Klarna\Orderlines\Model\Items\Discount\Handler as OrderLineDiscount;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\Model\Checkout\Orderline\Items\Discount
 */
class DiscountTest extends TestCase
{
    /**
     * @var Discount
     */
    private $model;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var OrderLineDiscount
     */
    private $subject;
    /**
     * @var Parameter
     */
    private $parameter;
    /**
     * @var DataHolder
     */
    private $dataHolder;
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @covers ::beforeCollectPrePurchase
     */
    public function testBeforeCollectPrePurchaseShippingLineExistsWhenKsaIsNotUsed(): void
    {
        $this->dependencyMocks['config']->method('isKssEnabled')
            ->willReturn(false);
        $this->parameter->expects(static::never())->method('setShippingLineEnabled');

        $result = $this->model->beforeCollectPrePurchase(
            $this->subject,
            $this->parameter,
            $this->dataHolder,
            $this->quote
        );

        static::assertSame($this->parameter, $result[0]);
        static::assertSame($this->dataHolder, $result[1]);
        static::assertSame($this->quote, $result[2]);
    }

    /**
     * @covers ::beforeCollectPrePurchase
     */
    public function testBeforeCollectPrePurchaseShippingLineDoesNotExistsWhenKsaIsUsed(): void
    {
        $this->dependencyMocks['config']->method('isKssEnabled')
            ->willReturn(true);
        $this->parameter->method('setShippingLineEnabled')
            ->with(false);
        $result = $this->model->beforeCollectPrePurchase(
            $this->subject,
            $this->parameter,
            $this->dataHolder,
            $this->quote
        );

        static::assertSame($this->parameter, $result[0]);
        static::assertSame($this->dataHolder, $result[1]);
        static::assertSame($this->quote, $result[2]);
    }

    protected function setUp(): void
    {
        $mockFactory           = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(Discount::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $store = $mockFactory->create(Store::class);
        $this->quote = $mockFactory->create(Quote::class);
        $this->quote->method('getStore')->willReturn($store);

        $this->parameter = $mockFactory->create(Parameter::class);
        $this->dataHolder = $mockFactory->create(DataHolder::class);
        $this->subject = $mockFactory->create(OrderLineDiscount::class);
    }
}
