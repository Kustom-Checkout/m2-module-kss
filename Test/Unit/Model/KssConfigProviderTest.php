<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kss\Test\Unit\Model;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kss\Model\KssConfigProvider;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Model\KssConfigProvider
 */
class KssConfigProviderTest extends TestCase
{
    /**
     * @var KssConfigProvider
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
     * @covers ::isKssEnabled
     */
    public function testIsKssEnabled(): void
    {
        $store = $this->mockFactory->create(Store::class);
        $this->dependencyMocks['scopeConfig']->method('isSetFlag')
            ->with('payment/klarna_kss/enabled', 'stores', $store)
            ->willReturn(true);

        static::assertTrue($this->model->isKssEnabled($store));
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(KssConfigProvider::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
