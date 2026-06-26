<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin;

use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Shipping\Handler;
use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Plugin\ShippingLinePlugin;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\ShippingLinePlugin
 */
class ShippingLinePluginTest extends TestCase
{
    /**
     * @var Parameter|MockObject
     */
    private $parameter;
    /**
     * @var TestObjectFactory
     */
    private $objectFactory;
    /**
     * @var Quote|MockObject
     */
    private $quote;
    /**
     * @var Shipping|MockObject
     */
    private $shipping;
    /**
     * @var DataHolder|MockObject
     */
    private $dataHolder;
    /**
     * @var ShippingLinePlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var array
     */
    private $totals;

    /**
     * @covers ::beforeCollectPrePurchase()
     */
    public function testOrderLinesHasShippingLine(): void
    {
        $this->dependencyMocks['config']->expects($this->once())->method('isKssEnabled')->willReturn(false);
        $this->model->beforeCollectPrePurchase($this->shipping, $this->parameter, $this->dataHolder, $this->quote);
        self::assertArrayHasKey('shipping', $this->totals);
    }

    /**
     * @covers ::beforeCollectPrePurchase()
     */
    public function testOrderLinesDoesNotHaveShippingLine(): void
    {
        $this->dependencyMocks['config']->expects($this->once())->method('isKssEnabled')->willReturn(true);
        $this->model->beforeCollectPrePurchase($this->shipping, $this->parameter, $this->dataHolder, $this->quote);
        self::assertArrayNotHasKey('shipping', $this->totals);
    }

    private function createDataHolder()
    {
        $dataHolder = $this->createMock(DataHolder::class);

        $dataHolder
            ->method('getTotals')
            ->willReturnCallback(function () {
                return $this->totals;
            });

        $dataHolder
            ->method('setTotals')
            ->willReturnCallback(function ($totals) {
                $this->totals = $totals;
            });

        return $dataHolder;
    }

    public function setUp(): void
    {
        $mockFactory         = new MockFactory($this);
        $this->objectFactory = new TestObjectFactory('');

        $this->model           = $this->objectFactory->create(ShippingLinePlugin::class);
        $this->dependencyMocks = $this->objectFactory->getDependencyMocks();
        $this->dataHolder      = $this->createDataHolder();
        $this->shipping        = $this->createMock(Handler::class);
        $this->quote           = $this->createMock(Quote::class);
        $this->parameter       = $this->createMock(Parameter::class);
        $store                 = $this->createMock(Store::class);
        $this->quote->method('getStore')->willReturn($store);

        $this->totals = [
            'shipping' => ''
        ];
    }
}
