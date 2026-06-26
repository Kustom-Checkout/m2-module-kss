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
use Klarna\Orderlines\Model\ItemGenerator;
use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kco\Model\Api\Builder\Kasper;
use Klarna\Kss\Plugin\KasperPlugin;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\KasperPlugin
 */
class KasperPluginTest extends TestCase
{
    /**
     * @var KasperPlugin
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
     * @var array
     */
    private $request;

    /**
     * @covers ::afterGenerateCreateRequest
     */
    public function testAfterGenerateCreateRequestIsKssDisabledInstantReturn(): void
    {
        $this->checkUpdateValuesIsKssDisabledInstantReturn(
            'afterGenerateCreateRequest'
        );
    }

    /**
     * @covers ::afterGenerateUpdateRequest
     */
    public function testAfterGenerateUpdateRequestIsKssDisabledInstantReturn(): void
    {
        $this->checkUpdateValuesIsKssDisabledInstantReturn(
            'afterGenerateUpdateRequest'
        );
    }

    private function checkUpdateValuesIsKssDisabledInstantReturn(string $method)
    {
        $store = $this->mockFactory->create(Store::class);

        $subject = $this->mockFactory->create(Kasper::class);
        $result = $this->mockFactory->create(Kasper::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(false);

        $subject
            ->expects(static::never())
            ->method('getParameter');

        static::assertInstanceOf(
            Kasper::class,
            call_user_func([$this->model, $method], $subject, $result, $quote)
        );
    }

    /**
     * @covers ::afterGenerateCreateRequest
     */
    public function testAfterGenerateCreateRequestAmountsModified(): void
    {
        $this->checkUpdateValuesAmountsModified(
            'afterGenerateCreateRequest',
            []
        );

        static::assertEquals(
            800,
            $this->request['order_amount']
        );
        static::assertEquals(
            180,
            $this->request['order_tax_amount']
        );
    }

    /**
     * @covers ::afterGenerateCreateRequest
     */
    public function testAfterGenerateCreateRequestDiscountLineExists(): void
    {
        $orderLines = [
            [
                'type' => ItemGenerator::ITEM_TYPE_DISCOUNT,
                'total_tax_amount' => -10
            ]
        ];
        $this->checkUpdateValuesAmountsModified(
            'afterGenerateCreateRequest',
            $orderLines
        );

        static::assertEquals(
            800,
            $this->request['order_amount']
        );
        static::assertEquals(
            170,
            $this->request['order_tax_amount']
        );
    }

    /**
     * @covers ::afterGenerateUpdateRequest
     */
    public function testAfterGenerateUpdateRequestAmountsModified(): void
    {
        $this->checkUpdateValuesAmountsModified(
            'afterGenerateUpdateRequest',
            []
        );

        static::assertEquals(
            800,
            $this->request['order_amount']
        );
        static::assertEquals(
            180,
            $this->request['order_tax_amount']
        );
    }

    /**
     * @covers ::afterGenerateUpdateRequest
     */
    public function testAfterGenerateUpdateRequestDiscountLineExists(): void
    {
        $orderLines = [
            [
                'type' => ItemGenerator::ITEM_TYPE_DISCOUNT,
                'total_tax_amount' => -10
            ]
        ];
        $this->checkUpdateValuesAmountsModified(
            'afterGenerateUpdateRequest',
            $orderLines
        );

        static::assertEquals(
            800,
            $this->request['order_amount']
        );
        static::assertEquals(
            170,
            $this->request['order_tax_amount']
        );
    }

    private function checkUpdateValuesAmountsModified(string $method, $orderLines)
    {
        $this->request['order_amount'] = 1000;
        $this->request['order_tax_amount'] = 200;
        $this->request['order_lines'] = $orderLines;

        $store = $this->mockFactory->create(Store::class);
        $shippingAddress = $this->mockFactory->create(Address::class, [], [
            'getBaseShippingInclTax',
            'getBaseShippingTaxAmount'
        ]);

        $shippingAddress
            ->expects(self::once())
            ->method('getBaseShippingInclTax');

        $shippingAddress
            ->expects(self::once())
            ->method('getBaseShippingTaxAmount');

        $subject = $this->createSubject();
        $result = $this->mockFactory->create(Kasper::class);
        $quote = $this->mockFactory->create(Quote::class);
        $quote
            ->method('getStore')
            ->willReturn($store);
        $quote
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $this->dependencyMocks['config']
            ->method('isKssEnabled')
            ->willReturn(true);

        $this->dependencyMocks['dataConverter']
            ->method('toApiFloat')
            ->willReturnOnConsecutiveCalls(200, 20);

        static::assertInstanceOf(
            Kasper::class,
            call_user_func([$this->model, $method], $subject, $result, $quote)
        );
    }

    private function createSubject()
    {
        $subject = $this->mockFactory->create(Kasper::class);
        $parameter = $this->mockFactory->create(Parameter::class);

        $subject
            ->method('getParameter')
            ->willReturn($parameter);

        $parameter
            ->method('getRequest')
            ->willReturn($this->request);

        $parameter
            ->method('setRequest')
            ->willReturnCallback(function ($request) {
                $this->request = $request;
            });

        return $subject;
    }

    protected function setUp(): void
    {
        $this->mockFactory           = new MockFactory($this);
        $objectFactory               = new TestObjectFactory('');
        $this->model                 = $objectFactory->create(KasperPlugin::class);
        $this->dependencyMocks       = $objectFactory->getDependencyMocks();

        $this->request = [];
    }
}
