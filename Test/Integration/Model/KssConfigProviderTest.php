<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\kSS\Test\Integration\Model;

use Klarna\Kss\Model\KssConfigProvider;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klarna\Kss\Model\KssConfigProvider
 */
class KssConfigProviderTest extends TestCase
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
     * @var KssConfigProvider
     */
    private $configProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->configProvider = $this->objectManager->get(KssConfigProvider::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     */
    public function testIsKssEnabledShouldReturnTrueWithKssAndFullCheckoutOn(): void
    {
        $this->assertTrue($this->configProvider->isKssEnabled($this->storeManager->getStore()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     */
    public function testIsKssEnabledShouldReturnTrueWithOnlyFullCheckoutOn(): void
    {
        $this->assertTrue($this->configProvider->isKssEnabled($this->storeManager->getStore()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     */
    public function testIsKssEnabledShouldReturnTrueWithOnlyKssOn(): void
    {
        $this->assertTrue($this->configProvider->isKssEnabled($this->storeManager->getStore()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     */
    public function testIsKssEnabledShouldReturnFalseWithBothConfigsOff(): void
    {
        $this->assertFalse($this->configProvider->isKssEnabled($this->storeManager->getStore()));
    }
}
