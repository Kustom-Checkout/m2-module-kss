<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin\Model\Cart;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kco\Model\Cart\FullUpdate;
use Klarna\Kss\Plugin\Model\Cart\FullUpdatePlugin;
use Klarna\Kss\Plugin\Model\Checkout\AddressPlugin;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\Model\Cart\FullUpdatePlugin
 */
class FullUpdatePluginTest extends TestCase
{
    /**
     * @var FullUpdatePlugin
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

    public function testAfterUpdateQuoteByRequestObjectReturnWithOneBillingAddress(): void
    {
        $address = $this->mockFactory->create(FullUpdate::class);
        $checkout = $this->mockFactory->create(DataObject::class, [], [
            'getBillingAddress',
            'getShippingAddress'
        ]);
        $quote = $this->mockFactory->create(Quote::class);

        $checkout
            ->method('getBillingAddress')
            ->willReturn(['one_entry']);

        $checkout
            ->method('getShippingAddress')
            ->willReturn([]);

        $this->dependencyMocks['update']
            ->expects(static::never())
            ->method('hasKssShippingStructure');

        static::assertEquals(
            $quote,
            $this->model->afterUpdateByKlarnaRequestObject(
                $address,
                $quote,
                $checkout,
                '1',
                $quote,
            )
        );
    }

    public function testAfterUpdateQuoteByRequestObjectReturnWithOneShippingAddress(): void
    {
        $address = $this->mockFactory->create(FullUpdate::class);
        $checkout = $this->mockFactory->create(DataObject::class, [], [
            'getBillingAddress',
            'getShippingAddress'
        ]);
        $quote = $this->mockFactory->create(Quote::class);

        $checkout
            ->method('getBillingAddress')
            ->willReturn([]);

        $checkout
            ->method('getShippingAddress')
            ->willReturn(['one_entry']);

        $this->dependencyMocks['update']
            ->expects(static::never())
            ->method('hasKssShippingStructure');

        static::assertEquals(
            $quote,
            $this->model->afterUpdateByKlarnaRequestObject(
                $address,
                $quote,
                $checkout,
                '1',
                $quote,
            )
        );
    }

    public function testAfterUpdateQuoteByRequestObjectHasKssShippingStructureCallUpdate(): void
    {
        $address = $this->mockFactory->create(FullUpdate::class);
        $checkout = $this->mockFactory->create(DataObject::class, [], [
            'getBillingAddress',
            'getShippingAddress'
        ]);

        $store = $this->mockFactory->create(Store::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote->method('getStore')
            ->willReturn($store);

        $checkout
            ->method('getBillingAddress')
            ->willReturn([]);

        $checkout
            ->method('getShippingAddress')
            ->willReturn([]);

        $this->dependencyMocks['update']
            ->method('hasKssShippingStructure')
            ->with($checkout)
            ->willReturn(true);

        $this->dependencyMocks['update']
            ->expects(static::once())
            ->method('updateByCallbackRequest');

        static::assertEquals(
            $quote,
            $this->model->afterUpdateByKlarnaRequestObject(
                $address,
                $quote,
                $checkout,
                '1',
                $quote,
            )
        );
    }

    public function testAfterUpdateQuoteByRequestObjectHasActiveKlarnaShippingGatewayDisableKss(): void
    {
        $address = $this->mockFactory->create(FullUpdate::class);
        $checkout = $this->mockFactory->create(DataObject::class, [], [
            'getBillingAddress',
            'getShippingAddress'
        ]);
        $quote = $this->mockFactory->create(Quote::class);

        $checkout
            ->method('getBillingAddress')
            ->willReturn([]);

        $checkout
            ->method('getShippingAddress')
            ->willReturn([]);

        $this->dependencyMocks['kcoSession']
            ->method('hasActiveKlarnaShippingGatewayInformation')
            ->willReturn(true);

        $this->dependencyMocks['update']
            ->expects(static::once())
            ->method('disableKss');

        static::assertEquals(
            $quote,
            $this->model->afterUpdateByKlarnaRequestObject(
                $address,
                $quote,
                $checkout,
                '1',
                $quote,
            )
        );
    }

    protected function setUp(): void
    {
        $this->mockFactory           = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(FullUpdatePlugin::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();
    }
}
