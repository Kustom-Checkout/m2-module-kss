<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Model\Assignment;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Model\Assignment\Tax;
use Klarna\Kss\Model\Carrier;
use Klarna\Kss\Model\ShippingMethodGateway;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;
use Magento\Tax\Model\TaxDetails\ItemDetails;
use Magento\Tax\Model\TaxDetails\AppliedTax;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Assignment\Tax
 */
class TaxTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private MockFactory $mockFactory;
    /**
     * @var Tax
     */
    private Tax $model;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private array $dependencyMocks;
    /**
     * @var ItemDetails
     */
    private ItemDetails $itemDetails;
    /**
     * @var Quote
     */
    private Quote $quote;

    /**
     * @covers ::canUpdateValues
     */
    public function testCanUpdateValuesTypeIsNotShipping(): void
    {
        $this->itemDetails->method('getType')
            ->willReturn('no shipping type');
        static::assertFalse($this->model->canUpdateValues($this->itemDetails, $this->quote));
    }

    /**
     * @covers ::canUpdateValues
     */
    public function testCanUpdateValuesQuoteIsVirtual(): void
    {
        $this->itemDetails->method('getType')
            ->willReturn('shipping');
        $this->quote->method('isVirtual')
            ->willReturn(true);
        static::assertFalse($this->model->canUpdateValues($this->itemDetails, $this->quote));
    }

    /**
     * @covers ::canUpdateValues
     */
    public function testCanUpdateValuesQuoteIsNotSavedToTheDatabase(): void
    {
        $this->itemDetails->method('getType')
            ->willReturn('shipping');
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $this->quote->method('getId')
            ->willReturn(null);
        static::assertFalse($this->model->canUpdateValues($this->itemDetails, $this->quote));
    }

    /**
     * @covers ::canUpdateValues
     */
    public function testCanUpdateValuesReturnsTrue(): void
    {
        $this->itemDetails->method('getType')
            ->willReturn('shipping');
        $this->quote->method('isVirtual')
            ->willReturn(false);
        $this->quote->method('getId')
            ->willReturn(1);
        static::assertTrue($this->model->canUpdateValues($this->itemDetails, $this->quote));
    }

    /**
     * @covers ::assignToTaxInstance
     */
    public function testAssignToTaxInstanceUpdatingTheAppliedTaxes(): void
    {
        $appliedTaxes = $this->mockFactory->create(AppliedTax::class);
        $this->dependencyMocks['factory']->method('getAppliedTax')
            ->willReturn($appliedTaxes);
        $this->itemDetails->expects(static::once())
            ->method('setAppliedTaxes')
            ->with([
                Carrier::GATEWAY_KEY => $appliedTaxes
            ]);

        $shippingMethodGateway = $this->mockFactory->create(ShippingMethodGateway::class);
        $result = $this->model->assignToTaxInstance($this->itemDetails, $shippingMethodGateway, $this->quote);
        static::assertSame($this->itemDetails, $result);
    }

    /**
     * @covers ::assignByKlarnaInstance
     */
    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(Tax::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->itemDetails = $this->mockFactory->create(ItemDetails::class);
        $this->quote = $this->mockFactory->create(Quote::class);
    }
}
