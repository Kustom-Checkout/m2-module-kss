<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Test\Integration\Plugin;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Shipping\Handler;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klarna\Kss\Plugin\ShippingLinePlugin
 */
class ShippingLinePluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Parameter
     */
    private $parameter;

    /**
     * @var DataHolder
     */
    private $dataHolder;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $quoteGetter;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->parameter = $this->objectManager->create(Parameter::class);
        $this->dataHolder = $this->objectManager->create(DataHolder::class);
        $this->quoteGetter = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->handler = $this->objectManager->get(Handler::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testBeforeCollectPrePurchaseShouldNotRemoveShippingFeeWithKssAndFullCheckoutConfigOn(): void
    {
        $quoteId = '100000001';
        $quote = $this->quoteGetter->execute($quoteId);

        $expectedOrderLines = [
            [
                'type' => 'shipping_fee',
                'reference' => '123',
                'name' => 'Shipping',
                'quantity' => 1,
                'unit_price' => 7.99,
                'tax_rate' => 25,
                'total_amount' => 9.99,
                'total_tax_amount' => 1.99,
                'total_discount_amount' => 0,
            ],
        ];
        $expectedTotals = [
            'shipping' => 9.99,
        ];

        $this->initParameter();
        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $quote);

        $this->assertEquals($expectedOrderLines, $this->parameter->getOrderLines());
        $this->assertEquals($expectedTotals, $this->dataHolder->getTotals());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testBeforeCollectPrePurchaseShouldNotRemoveShippingFeeWithOnlyFullCheckoutConfigOn(): void
    {
        $quoteId = '100000001';
        $quote = $this->quoteGetter->execute($quoteId);

        $expectedOrderLines = [
            [
                'type' => 'shipping_fee',
                'reference' => '123',
                'name' => 'Shipping',
                'quantity' => 1,
                'unit_price' => 7.99,
                'tax_rate' => 25,
                'total_amount' => 9.99,
                'total_tax_amount' => 1.99,
                'total_discount_amount' => 0,
            ],
        ];
        $expectedTotals = [
            'shipping' => 9.99,
        ];

        $this->initParameter();
        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $quote);

        $this->assertEquals($expectedOrderLines, $this->parameter->getOrderLines());
        $this->assertEquals($expectedTotals, $this->dataHolder->getTotals());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testBeforeCollectPrePurchaseShouldNotRemoveShippingFeeWithBothConfigsOff(): void
    {
        $quoteId = '100000001';
        $quote = $this->quoteGetter->execute($quoteId);

        $expectedOrderLines = [
            [
                'type' => 'shipping_fee',
                'reference' => '123',
                'name' => 'Shipping',
                'quantity' => 1,
                'unit_price' => 7.99,
                'tax_rate' => 25,
                'total_amount' => 9.99,
                'total_tax_amount' => 1.99,
                'total_discount_amount' => 0,
            ],
        ];
        $expectedTotals = [
            'shipping' => 9.99,
        ];

        $this->initParameter();
        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $quote);

        $this->assertEquals($expectedOrderLines, $this->parameter->getOrderLines());
        $this->assertEquals($expectedTotals, $this->dataHolder->getTotals());
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testBeforeCollectPrePurchaseShouldRemoveShippingFeeWithOnlyKssConfigOn(): void
    {
        $quoteId = '100000001';
        $quote = $this->quoteGetter->execute($quoteId);

        $expectedOrderLines = [];
        $expectedTotals = [];

        $this->initParameter();
        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $quote);

        $this->assertEquals($expectedOrderLines, $this->parameter->getOrderLines());
        $this->assertEquals($expectedTotals, $this->dataHolder->getTotals());
    }

    /**
     * @return void
     */
    private function initParameter(): void
    {
        $this->parameter->resetOrderLines();
        $this->parameter->setShippingReference('123');
        $this->parameter->setShippingTitle('Shipping');
        $this->parameter->setShippingUnitPrice(7.99);
        $this->parameter->setShippingTaxRate(25.00);
        $this->parameter->setShippingTotalAmount(9.99);
        $this->parameter->setShippingTaxAmount(1.99);
        $this->parameter->setShippingDiscountAmount(0);
        $this->dataHolder->setTotals(['shipping' => 9.99]);
    }
}
