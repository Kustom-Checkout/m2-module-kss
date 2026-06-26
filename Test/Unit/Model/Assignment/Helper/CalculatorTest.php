<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Model\Assignment\Helper;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Model\Assignment\Helper\Calculator;
use Klarna\Kss\Model\ShippingMethodGateway;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Address;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Assignment\Helper\Calculator
 */
class CalculatorTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private MockFactory $mockFactory;
    /**
     * @var Calculator
     */
    private Calculator $model;
    /**
     * @var ShippingMethodGateway
     */
    private ShippingMethodGateway $shippingMethodGateway;
    /**
     * @var Quote
     */
    private Quote $quote;

    /**
     * @covers ::getShippingAmountExclusiveTax
     */
    public function testGetShippingAmountExclusiveTaxCalculatesNotNegativeValue(): void
    {
        $this->shippingMethodGateway->method('getShippingAmount')
            ->willReturn(10.0);
        $this->shippingMethodGateway->method('getTaxAmount')
            ->willReturn(0.34);

        $result = $this->model->getShippingAmountExclusiveTax($this->shippingMethodGateway);
        static::assertSame(9.66, $result);
    }

    /**
     * @covers ::getShippingAmountExclusiveTax
     */
    public function testGetShippingAmountExclusiveTaxCalculatesNegativeValue(): void
    {
        $this->shippingMethodGateway->method('getShippingAmount')
            ->willReturn(0.0);
        $this->shippingMethodGateway->method('getTaxAmount')
            ->willReturn(0.34);

        $result = $this->model->getShippingAmountExclusiveTax($this->shippingMethodGateway);
        static::assertSame(-0.34, $result);
    }

    /**
     * @covers ::getDiscountTaxCompensationAmount
     */
    public function testGetDiscountTaxCompensationAmountCalculatesNotNegativeValue(): void
    {
        $this->shippingMethodGateway->method('getTaxAmount')
            ->willReturn(0.34);

        $result = $this->model->getDiscountTaxCompensationAmount($this->shippingMethodGateway, 0.1);
        static::assertSame(round(0.24, 2), round($result, 2));
    }

    /**
     * @covers ::getDiscountTaxCompensationAmount
     */
    public function testGetDiscountTaxCompensationAmountCalculatesNegativeValue(): void
    {
        $this->shippingMethodGateway->method('getTaxAmount')
            ->willReturn(0.34);

        $result = $this->model->getDiscountTaxCompensationAmount($this->shippingMethodGateway, 0.4);
        static::assertSame(-0.06, $result);
    }

    /**
     * @covers ::getSubtractedDiscountShippingTax
     */
    public function testGetSubtractedDiscountShippingTaxCalculatesNotNegativeValue(): void
    {
        $this->shippingMethodGateway->method('getTaxRate')
            ->willReturn(25.0);
        $this->shippingMethodGateway->method('getShippingAmount')
            ->willReturn(25.0);

        $shippingAddress = $this->mockFactory->create(Address::class, [], ['getShippingDiscountAmount']);
        $shippingAddress->method('getShippingDiscountAmount')
            ->willReturn(2.5);
        $this->quote->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $result = $this->model->getSubtractedDiscountShippingTax($this->shippingMethodGateway, $this->quote);
        static::assertSame(4.5, $result);
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(Calculator::class);

        $this->shippingMethodGateway = $this->mockFactory->create(ShippingMethodGateway::class);
        $this->quote = $this->mockFactory->create(Quote::class);
    }
}
