<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Model\Checkout;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Model\Checkout\Update;
use Klarna\Kss\Model\ShippingMethodGateway;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\Checkout\Update
 */
class UpdateTest extends TestCase
{
    /**
     * @var Update
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
    /**
     * @var DataObject
     */
    private $request;
    /**
     * @var ShippingMethodGateway
     */
    private $shippingMethodGateway;
    /**
     * @var Store
     */
    private $store;

    /**
     * @covers ::disableKss
     */
    public function testDisableKssHasNoShippingIdDoNothing(): void
    {
        $gateway = $this->mockFactory->create(ShippingMethodGateway::class);
        $gateway->method('getId')->willReturn(null);

        $gateway
            ->expects(static::never())
            ->method('setIsActive');
        $this->dependencyMocks['repository']
            ->expects(static::never())
            ->method('save');

        static::assertInstanceOf(
            ShippingMethodGateway::class,
            $this->model->disableKss($gateway)
        );
    }

    /**
     * @covers ::disableKss
     */
    public function testDisableKssHasShippingIdDisableKss(): void
    {
        $gateway = $this->mockFactory->create(ShippingMethodGateway::class);
        $gateway->method('getId')->willReturn(1);

        $gateway
            ->expects(static::once())
            ->method('setIsActive')
            ->with(false);
        $this->dependencyMocks['repository']
            ->expects(static::once())
            ->method('save')
            ->with($gateway);

        static::assertInstanceOf(
            ShippingMethodGateway::class,
            $this->model->disableKss($gateway)
        );
    }

    /**
     * @covers ::updateByCallbackRequest
     * @doesNotPerformAssertions
     */
    public function testUpdateByCallbackRequestKsaOnApiButNotInTheShopEnabled(): void
    {
        $requestData = [
            'selected_shipping_option' => [
                'shipping_method' => 'shipping method name'
            ]
        ];
        $this->request->method('getData')
            ->willReturn($requestData);

        $this->dependencyMocks['kssConfigProvider']->method('isKssEnabled')
            ->willReturn(false);
        $this->dependencyMocks['logger']->method('warning')
            ->with('KSA is enabled on the API but disabled in the shop. This will lead to errors.');

        $this->model->updateByCallbackRequest($this->request, $this->shippingMethodGateway, $this->store);
    }

    protected function setUp(): void
    {
        $this->mockFactory           = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(Update::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();

        $this->request = $this->mockFactory->create(DataObject::class);
        $this->shippingMethodGateway = $this->mockFactory->create(ShippingMethodGateway::class);
        $this->store = $this->mockFactory->create(Store::class);
    }
}
