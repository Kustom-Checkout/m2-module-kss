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
use Klarna\Kss\Model\Assignment\ShippingMethodGateway;
use Magento\Framework\DataObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Klarna\Kss\Model\ShippingMethodGateway as ShippingMethodGatewayTable;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Assignment\ShippingMethodGateway
 */
class ShippingMethodGatewayTest extends TestCase
{
    /**
     * @var ShippingMethodGateway
     */
    private ShippingMethodGateway $model;
    /**
     * @var ShippingMethodGatewayTable
     */
    private ShippingMethodGatewayTable $shippingMethodGatewayTable;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private array $dependencyMocks;
    /**
     * @var MockFactory
     */
    private MockFactory $mockFactory;

    #[DataProvider('getShippingInformation')]
    /**
     * @covers ::assignByKlarnaInstance
     * @param array $shippingInformation
     * @dataProvider getShippingInformation
     */
    public function testAssignByKlarnaInstanceMethodIsPickupPoint(array $shippingInformation): void
    {
        $dataObject = $this->mockFactory->create(DataObject::class, [], ['getSelectedShippingOption']);

        $dataObject->method('getSelectedShippingOption')
            ->willReturn($shippingInformation);

        $this->dependencyMocks['dataConverter']->method('toShopFloat')
            ->willReturn(0.0);
        $this->shippingMethodGatewayTable->expects(static::once())
            ->method('setPickUpPointName')
            ->with($shippingInformation['name']);
        $result = $this->model->assignByKlarnaInstance($this->shippingMethodGatewayTable, $dataObject);

        static::assertSame($this->shippingMethodGatewayTable, $result);
    }

    #[DataProvider('getShippingInformation')]
    /**
     * @covers ::assignByKlarnaInstance
     * @param array $shippingInformation
     * @dataProvider getShippingInformation
     */
    public function testAssignByKlarnaInstanceMethodIsNoPickupPoint(array $shippingInformation): void
    {
        $shippingInformation['shipping_method'] = 'No Pickup point';
        $dataObject = $this->mockFactory->create(DataObject::class, [], ['getSelectedShippingOption']);

        $dataObject->method('getSelectedShippingOption')
            ->willReturn($shippingInformation);

        $this->dependencyMocks['dataConverter']->method('toShopFloat')
            ->willReturn(0.0);
        $this->shippingMethodGatewayTable->expects(static::never())
            ->method('setPickUpPointName')
            ->with($shippingInformation['name']);
        $result = $this->model->assignByKlarnaInstance($this->shippingMethodGatewayTable, $dataObject);

        static::assertSame($this->shippingMethodGatewayTable, $result);
    }

    /**
     * Getting back shipping information
     *
     * @return array
     */
    public static function getShippingInformation(): array
    {
        return [
            [
                [
                    'price' => 10,
                    'tax_amount' => 0.34,
                    'tax_rate' => 3.5,
                    'id' => 'any_method_id',
                    'name' => 'my name',
                    'shipping_method' => 'PickUpPoint',
                    'delivery_details' => [
                        'pickup_location' => [
                            'address' => [
                                'street' => 'my shipping information street'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(ShippingMethodGateway::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->shippingMethodGatewayTable = $this->mockFactory->create(ShippingMethodGatewayTable::class);
    }
}
