<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kss\Test\Unit\Plugin\Model\Cart\Validations;

use Klarna\Orderlines\Model\ItemGenerator;
use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kco\Model\Cart\Validations\ShippingAmount;
use Klarna\Kss\Plugin\Model\Cart\Validations\ShippingAmountPlugin;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Kss\Plugin\Model\Checkout\Validations\ShippingAmountPlugin
 */
class ShippingAmountPluginTest extends TestCase
{
    /**
     * @var ShippingAmountPlugin
     */
    private $model;
    /**
     * @var array
     */
    private $dependencyMocks;
    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var ShippingAmount
     */
    private $subject;
    /**
     * @var DataObject
     */
    private $request;

    public function testAfterGetShippingAmountKsaIsDisabled(): void
    {
        $originalResult = 2500;
        $calculatedResult = $this->model->afterGetShippingAmount(
            $this->subject,
            $originalResult,
            $this->request,
            $this->quote
        );

        static::assertEquals($originalResult, $calculatedResult);
    }

    public function testAfterGetShippingAmountKsaEnabledNoDiscountLine(): void
    {
        $this->dependencyMocks['config']->method('isKssEnabled')
            ->willReturn(true);
        $this->request->method('getOrderLines')
            ->willReturn([]);

        $originalResult = 2500;
        $calculatedResult = $this->model->afterGetShippingAmount(
            $this->subject,
            $originalResult,
            $this->request,
            $this->quote
        );

        static::assertEquals($originalResult, $calculatedResult);
    }

    public function testAfterGetShippingAmountKsaEnabledDiscountLineExists(): void
    {
        $this->dependencyMocks['config']->method('isKssEnabled')
            ->willReturn(true);
        $orderLines = [
            [
                'type' => ItemGenerator::ITEM_TYPE_DISCOUNT,
                'total_amount' => -250
            ]
        ];
        $this->request->method('getOrderLines')
            ->willReturn($orderLines);

        $originalResult = 2500;
        $calculatedResult = $this->model->afterGetShippingAmount(
            $this->subject,
            $originalResult,
            $this->request,
            $this->quote
        );

        static::assertEquals(2250, $calculatedResult);
    }

    protected function setUp(): void
    {
        $mockFactory           = new MockFactory($this);
        $objectFactory         = new TestObjectFactory('');
        $this->model           = $objectFactory->create(ShippingAmountPlugin::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $store = $mockFactory->create(Store::class);
        $this->quote = $mockFactory->create(Quote::class);
        $this->quote->method('getStore')->willReturn($store);

        $this->subject = $mockFactory->create(ShippingAmount::class);
        $this->request = $mockFactory->create(DataObject::class, [], [
            'getOrderLines'
        ]);
    }
}
