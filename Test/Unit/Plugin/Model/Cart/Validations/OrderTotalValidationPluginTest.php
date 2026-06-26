<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin\Model\Cart\Validations;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kco\Model\Cart\Validations\OrderTotal;
use Klarna\Kss\Plugin\Model\Cart\Validations\OrderTotalValidationPlugin;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Address;

/**
 * @coversDefaultClass \Klarna\Kss\Test\Unit\Plugin\Model\Cart\Validations\OrderTotalValidationPlugin
 */
class OrderTotalValidationPluginTest extends TestCase
{
    /**
     * @var OrderTotalValidationPlugin
     */
    private $model;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var MockFactory
     */
    private $mockFactory;

    public function testAfterGetQuoteTotalKssDisabledReturnsInputTotal(): void
    {
        $store = $this->mockFactory->create(Store::class);

        $subject = $this->mockFactory->create(OrderTotal::class);
        $quoteTotal = 123;
        $request = $this->mockFactory->create(DataObject::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(false);

        static::assertEquals(
            123,
            $this->model->afterGetQuoteTotal(
                $subject,
                $quoteTotal,
                $request,
                $quote
            )
        );
    }

    public function testAfterGetQuoteTotalVirtualQuoteReturnsInputTotal(): void
    {
        $store = $this->mockFactory->create(Store::class);

        $subject = $this->mockFactory->create(OrderTotal::class);
        $quoteTotal = 123;
        $request = $this->mockFactory->create(DataObject::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(true);

        $quote
            ->method('isVirtual')
            ->willReturn(true);

        static::assertEquals(
            123,
            $this->model->afterGetQuoteTotal(
                $subject,
                $quoteTotal,
                $request,
                $quote
            )
        );
    }

    public function testAfterGetQuoteTotalShippingAmountReducesTotal(): void
    {
        $shippingAddress = $this->mockFactory->create(Address::class, [], [
            'getBaseShippingInclTax'
        ]);
        $store = $this->mockFactory->create(Store::class);

        $subject = $this->mockFactory->create(OrderTotal::class);
        $quoteTotal = 123;
        $request = $this->mockFactory->create(DataObject::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);

        $quote
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(true);

        $quote
            ->method('isVirtual')
            ->willReturn(false);

        $this->dependencyMocks['dataConverter']
            ->method('toApiFloat')
            ->willReturn(50);

        static::assertEquals(
            73,
            $this->model->afterGetQuoteTotal(
                $subject,
                $quoteTotal,
                $request,
                $quote
            )
        );
    }

    public function testAfterGetQuoteTotalIsShippingFeeInRequestReturnInputTotal(): void
    {
        $shippingAddress = $this->mockFactory->create(Address::class, [], [
            'getBaseShippingInclTax'
        ]);
        $store = $this->mockFactory->create(Store::class);

        $subject = $this->mockFactory->create(OrderTotal::class);
        $quoteTotal = 123;
        $request = $this->mockFactory->create(DataObject::class);
        $request
            ->method('getData')
            ->with('order_lines')
            ->willReturn([
                ['type' => 'shipping_fee']
            ]);

        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);

        $quote
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(true);

        $quote
            ->method('isVirtual')
            ->willReturn(false);

        $this->dependencyMocks['dataConverter']
            ->method('toApiFloat')
            ->willReturn(50);

        static::assertEquals(
            123,
            $this->model->afterGetQuoteTotal(
                $subject,
                $quoteTotal,
                $request,
                $quote
            )
        );
    }

    protected function setUp(): void
    {
        $this->mockFactory           = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(OrderTotalValidationPlugin::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();
    }
}
