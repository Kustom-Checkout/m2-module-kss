<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Plugin\Provider\Base;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Plugin\Provider\Base\ConfigPlugin;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\AdminSettings\Model\Configurations\Kco\ShippingOptions;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\Provider\Base\ConfigPlugin
 */
class ConfigPluginTest extends TestCase
{
    /**
     * @var ConfigPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var ShippingOptions
     */
    private $config;
    /**
     * @var Store
     */
    private $store;

    /**
     * @covers ::afterIsShippingInIframe
     */
    public function testAfterIsShippingInIframeInputResultIsTrue(): void
    {
        $this->dependencyMocks['configProvider']->expects(static::never())
            ->method('isKssEnabled');

        static::assertTrue($this->model->afterIsShippingInIframe($this->config, true, $this->store));
    }

    /**
     * @covers ::afterIsShippingInIframe
     */
    public function testAfterIsShippingInIframeInputResultIsFalseAndKssDisabled(): void
    {
        $this->dependencyMocks['configProvider']->method('isKssEnabled')
            ->willReturn(false);

        static::assertFalse($this->model->afterIsShippingInIframe($this->config, false, $this->store));
    }

    /**
     * @covers ::afterIsShippingInIframe
     */
    public function testAfterIsShippingInIframeInputResultIsFalseAndKssEnabled(): void
    {
        $this->dependencyMocks['configProvider']->method('isKssEnabled')
            ->willReturn(true);

        static::assertTrue($this->model->afterIsShippingInIframe($this->config, false, $this->store));
    }

    protected function setUp(): void
    {
        $mockFactory                 = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(ConfigPlugin::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();

        $this->config = $mockFactory->create(ShippingOptions::class);
        $this->store = $mockFactory->create(Store::class);
    }
}
