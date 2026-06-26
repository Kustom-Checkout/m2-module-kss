<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Model;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Model\ShippingMethodGateway;
use Klarna\Kss\Model\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Validator
     */
    private $model;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @covers ::isKssUsed()
     */
    public function testIsKssUsedAdminFlagIsDisabled(): void
    {
        static::assertFalse($this->model->isKssUsed($this->store, 'id'));
    }

    /**
     * @covers ::isKssUsed()
     */
    public function testIsKssUsedNoKssEntryInTheDatabase(): void
    {
        $this->dependencyMocks['configProvider']->method('isKssEnabled')
            ->willReturn(true);
        $this->dependencyMocks['shippingMethodGatewayRepository']->method('loadShipping')
            ->willthrowException(new NoSuchEntityException());
        static::assertFalse($this->model->isKssUsed($this->store, 'id'));
    }

    /**
     * @covers ::isKssUsed()
     */
    public function testIsKssUsedEntryIsDisabled(): void
    {
        $this->dependencyMocks['configProvider']->method('isKssEnabled')
            ->willReturn(true);
        $shipping = $this->mockFactory->create(ShippingMethodGateway::class);
        $this->dependencyMocks['shippingMethodGatewayRepository']->method('loadShipping')
            ->willReturn($shipping);
        static::assertFalse($this->model->isKssUsed($this->store, 'id'));
    }

    /**
     * @covers ::isKssUsed()
     */
    public function testIsKssUsedReturnsTrue(): void
    {
        $this->dependencyMocks['configProvider']->method('isKssEnabled')
            ->willReturn(true);
        $shipping = $this->mockFactory->create(ShippingMethodGateway::class);
        $shipping->method('isActive')
            ->willReturn(true);
        $this->dependencyMocks['shippingMethodGatewayRepository']->method('loadShipping')
            ->willReturn($shipping);
        static::assertTrue($this->model->isKssUsed($this->store, 'id'));
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(Validator::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->store           = $this->mockFactory->create(Store::class);
    }
}
