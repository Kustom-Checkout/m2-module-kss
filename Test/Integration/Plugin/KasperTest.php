<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Test\Integration\Plugin;

use Klarna\Kco\Model\Api\Builder\Kasper;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klarna\Kss\Plugin\KasperPlugin
 */
class KasperTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $quoteGetter;

    /**
     * @var Kasper
     */
    private $kasper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteGetter = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->kasper = $this->objectManager->get(Kasper::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testAfterGenerateUpdateRequestShouldIncludeShippingCostWithKssAndFullCheckoutOn(): void
    {
        $quoteId = '100000001';
        $expectedOrderAmount = 1500;

        $quote = $this->quoteGetter->execute($quoteId);
        $result = $this->kasper->generateUpdateRequest($quote)->getParameter()->getRequest();
        $this->assertEquals($expectedOrderAmount, $result['order_amount']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testAfterGenerateUpdateRequestShouldIncludeShippingCostWithOnlyFullCheckoutOn(): void
    {
        $quoteId = '100000001';
        $expectedOrderAmount = 1500;

        $quote = $this->quoteGetter->execute($quoteId);
        $result = $this->kasper->generateUpdateRequest($quote)->getParameter()->getRequest();
        $this->assertEquals($expectedOrderAmount, $result['order_amount']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testAfterGenerateUpdateRequestShouldNotIncludeShippingCostWithOnlyKssOn(): void
    {
        $quoteId = '100000001';
        $expectedOrderAmount = 1000;

        $quote = $this->quoteGetter->execute($quoteId);
        $result = $this->kasper->generateUpdateRequest($quote)->getParameter()->getRequest();
        $this->assertEquals($expectedOrderAmount, $result['order_amount']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testAfterGenerateUpdateRequestShouldIncludeShippingCostWithBothConfigsOff(): void
    {
        $quoteId = '100000001';
        $expectedOrderAmount = 1500;

        $quote = $this->quoteGetter->execute($quoteId);
        $result = $this->kasper->generateUpdateRequest($quote)->getParameter()->getRequest();
        $this->assertEquals($expectedOrderAmount, $result['order_amount']);
    }
}
