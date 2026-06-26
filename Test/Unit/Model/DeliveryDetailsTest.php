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
use Klarna\Kss\Model\DeliveryDetails;
use Klarna\Kss\Model\ShippingMethodGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\DeliveryDetails
 */
class DeliveryDetailsTest extends TestCase
{
    /**
     * @var DeliveryDetails
     */
    private $model;
    /**
     * @var ShippingMethodGateway|MockObject
     */
    private $shippingMethodGateway;
    /**
     * @var array
     */
    private $dependencyMocks;

    /**
     * @covers ::getDeliveryDataByKlarnaSessionId
     */
    public function testGetDeliveryDataByKlarnaSessionId(): void
    {
        $expected = '{"carrier:"dhl"}';
        $this->dependencyMocks['shippingMethodGatewayRepository']
            ->method('loadShipping')
            ->willReturn($this->shippingMethodGateway);
        $this->shippingMethodGateway
            ->method('getDeliveryDetails')
            ->willReturn($expected);

        $this->assertEquals(
            $expected,
            $this->model->getDeliveryDataByKlarnaSessionId('c7197f7a-1cef-6642-9ba6-83724e7c693f')
        );
    }

    protected function setUp(): void
    {
        $mockFactory                 = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(DeliveryDetails::class);
        $this->shippingMethodGateway = $mockFactory->create(ShippingMethodGateway::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();
    }
}
