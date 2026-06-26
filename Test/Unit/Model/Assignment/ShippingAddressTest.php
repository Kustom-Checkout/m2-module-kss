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
use Klarna\Kss\Model\Assignment\ShippingAddress;
use Klarna\Kss\Model\ShippingMethodGateway;
use Magento\Framework\DataObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Assignment\ShippingAddress
 */
class ShippingAddressTest extends TestCase
{
    /**
     * @var ShippingAddress
     */
    private ShippingAddress $model;
    /**
     * @var MockFactory
     */
    private MockFactory $mockFactory;

    #[DataProvider('getShippingInformation')]
    /**
     * @covers ::addKssAddressToShippingAddress
     * @param array $shippingInformation
     * @dataProvider getShippingInformation
     */
    public function testAddKssAddressToShippingAddressNoPickupLocationGiven(array $shippingInformation): void
    {
        unset($shippingInformation['delivery_details']['pickup_location']);

        $shippingAddress = [
            'postcode' => '12345'
        ];
        $klarnaRequest = $this->mockFactory->create(DataObject::class, [], [
            'getShippingAddress',
            'setShippingAddress',
            'getSelectedShippingOption'
        ]);
        $klarnaRequest->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $klarnaRequest->method('getSelectedShippingOption')
            ->willReturn($shippingInformation);

        $klarnaRequest->expects(static::once())
            ->method('setShippingAddress')
            ->with($shippingAddress);

        $result = $this->model->addKssAddressToShippingAddress($klarnaRequest);
        static::assertSame($klarnaRequest, $result);
    }

    #[DataProvider('getShippingInformation')]
    /**
     * @covers ::addKssAddressToShippingAddress
     * @param array $shippingInformation
     * @dataProvider getShippingInformation
     */
    public function testAddKssAddressToShippingAddressPickupLocationIsGiven(array $shippingInformation): void
    {
        $shippingAddress = [
            'postcode' => '12345'
        ];
        $klarnaRequest = $this->mockFactory->create(DataObject::class, [], [
            'getShippingAddress',
            'setShippingAddress',
            'getSelectedShippingOption'
        ]);
        $klarnaRequest->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $klarnaRequest->method('getSelectedShippingOption')
            ->willReturn($shippingInformation);

        $expected = array_merge(
            $shippingAddress,
            $shippingInformation['delivery_details']['pickup_location']['address']
        );
        $klarnaRequest->expects(static::once())
            ->method('setShippingAddress')
            ->with($expected);

        $result = $this->model->addKssAddressToShippingAddress($klarnaRequest);
        static::assertSame($klarnaRequest, $result);
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
        $this->model           = $objectFactory->create(ShippingAddress::class);
    }
}
