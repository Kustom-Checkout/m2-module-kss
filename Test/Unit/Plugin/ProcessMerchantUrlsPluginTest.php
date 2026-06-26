<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kco\Model\Api\Builder\Kasper;
use Klarna\Kss\Plugin\ProcessMerchantUrlsPlugin;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\ProcessMerchantUrlsPlugin
 */
class ProcessMerchantUrlsPluginTest extends TestCase
{
    /**
     * @var Kasper|MockObject
     */
    private $kasper;
    /**
     * @var Store|MockObject
     */
    private $store;
    /**
     * @var ProcessMerchantUrlsPlugin
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::afterProcessMerchantUrls
     */
    public function testMerchantUrlsContainsShippingMethodUpdate(): void
    {
        $result = [
            'shipping_option_update' => 'some URL'
        ];
        $this->dependencyMocks['config']->expects($this->once())->method('isKssEnabled')->willReturn(false);
        $this->assertArrayHasKey(
            'shipping_option_update',
            $this->model->afterProcessMerchantUrls($this->kasper, $result, $this->store, [])
        );
    }

    /**
     * @covers ::afterProcessMerchantUrls
     */
    public function testMerchantUrlsDoesNotContainsShippingMethodUpdate(): void
    {
        $result = [
            'shipping_option_update' => 'some URL'
        ];
        $this->dependencyMocks['config']->expects($this->once())->method('isKssEnabled')->willReturn(true);
        $this->assertArrayNotHasKey(
            'shipping_option_update',
            $this->model->afterProcessMerchantUrls($this->kasper, $result, $this->store, [])
        );
    }

    public function setUp(): void
    {
        $mockFactory   = new MockFactory($this);
        $objectFactory = new TestObjectFactory('');

        $this->model           = $objectFactory->create(ProcessMerchantUrlsPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
        $this->store           = $this->createMock(Store::class);
        $this->kasper          = $this->createMock(Kasper::class);
    }
}
