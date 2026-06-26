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
use Klarna\Kss\Model\Assignment\Helper\AppliedShippingTaxFactory;
use Klarna\Kss\Model\ShippingMethodGateway;
use Magento\Tax\Model\TaxDetails\AppliedTax;
use Magento\Tax\Model\TaxDetails\AppliedTaxRate;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Assignment\Helper\AppliedShippingTaxFactory
 */
class AppliedShippingTaxFactoryTest extends TestCase
{
    /**
     * @var AppliedShippingTaxFactory
     */
    private AppliedShippingTaxFactory $model;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private array $dependencyMocks;
    /**
     * @var ShippingMethodGateway
     */
    private ShippingMethodGateway $shippingMethodGateway;
    /**
     * @var MockFactory
     */
    private MockFactory $mockFactory;

    /**
     * @covers ::getAppliedTax
     */
    public function testGetAppliedTaxAddingResultToObject(): void
    {
        $appliedTax = $this->mockFactory->create(AppliedTax::class);
        $appliedTax->expects(static::once())
            ->method('setRates');
        $this->dependencyMocks['appliedTaxFactory']->method('create')
            ->willReturn($appliedTax);

        $appliedTaxRate = $this->mockFactory->create(AppliedTaxRate::class);
        $this->dependencyMocks['appliedTaxRateFactory']->method('create')
            ->willReturn($appliedTaxRate);

        $result = $this->model->getAppliedTax($this->shippingMethodGateway);
        static::assertSame($appliedTax, $result);
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(AppliedShippingTaxFactory::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->shippingMethodGateway = $this->mockFactory->create(ShippingMethodGateway::class);
    }
}
