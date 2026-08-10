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
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klarna\Kss\Plugin\ProcessMerchantUrlsPlugin
 */
class ProcessMerchantUrlsPluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Kasper
     */
    private $builder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->builder = $this->objectManager->get(Kasper::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     */
    public function testProcessMerchantUrlsShouldIncludeShippingOptionUrlWithKssAndFullCheckoutOn(): void
    {
        $expectedShippingOptionUrl = 'http://localhost/index.php/kco/api/shippingMethodUpdate/id/{checkout.order.id}';
        $result = $this->builder->processMerchantUrls($this->storeManager->getStore(), []);
        $this->assertEquals($expectedShippingOptionUrl, $result['shipping_option_update']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     */
    public function testProcessMerchantUrlsShouldIncludeShippingOptionUrlWithOnlyFullCheckoutOn(): void
    {
        $expectedShippingOptionUrl = 'http://localhost/index.php/kco/api/shippingMethodUpdate/id/{checkout.order.id}';
        $result = $this->builder->processMerchantUrls($this->storeManager->getStore(), []);
        $this->assertEquals($expectedShippingOptionUrl, $result['shipping_option_update']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     */
    public function testProcessMerchantUrlsShouldRemoveShippingOptionUrlWithOnlyKssOn(): void
    {
        $result = $this->builder->processMerchantUrls($this->storeManager->getStore(), []);
        $this->assertFalse(array_key_exists('shipping_option_update', $result));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     */
    public function testProcessMerchantUrlsShouldIncludeShippingOptionUrlWithBothConfigsOff(): void
    {
        $expectedShippingOptionUrl = 'http://localhost/index.php/kco/api/shippingMethodUpdate/id/{checkout.order.id}';
        $result = $this->builder->processMerchantUrls($this->storeManager->getStore(), []);
        $this->assertEquals($expectedShippingOptionUrl, $result['shipping_option_update']);
    }
}
