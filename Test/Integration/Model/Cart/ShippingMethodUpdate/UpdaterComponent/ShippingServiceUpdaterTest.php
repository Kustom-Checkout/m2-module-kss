<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Test\Integration\Cart\ShippingMethodUpdate\UpdaterComponent;

use Klarna\Kco\Model\Api\Rest\Service\Checkout as CheckoutApi;
use Klarna\Kco\Model\Cart\ShippingMethodUpdate\UpdaterComponent\DefaultUpdater;
use Klarna\Kco\Model\Cart\ShippingMethodUpdateInterface;
use Klarna\Kss\Model\Cart\ShippingMethodUpdate\UpdaterComponent\ShippingServiceUpdater;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_intersect_key;

/**
 * @covers \Klarna\Kss\Model\Cart\ShippingMethodUpdate\UpdaterComponent\ShippingServiceUpdater
 */
class ShippingServiceUpdaterTest extends TestCase
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CheckoutApi|MockObject
     */
    private $checkoutApiMock;

    /**
     * @var DataObject
     */
    private $data;

    /**
     * @var ShippingMethodUpdateInterface
     */
    private $methodUpdater;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->checkoutApiMock = $this->createMock(CheckoutApi::class);
        $this->objectManager->addSharedInstance($this->checkoutApiMock, CheckoutApi::class);

        $this->quoteGetter = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->checkoutSession = $this->objectManager->get(CheckoutSession::class);

        $this->data = $this->objectManager->create(DataObject::class);
        $this->methodUpdater = $this->objectManager->get(ShippingMethodUpdateInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kco/active 1
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 1
     * @magentoConfigFixture current_store klarna/api/debug 1
     * @magentoConfigFixture current_store general/region/state_required ''
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testUpdateByDataShouldSuccessfullyUpdateShippingCostsOnCartBasedOnApiDataWithFullCheckoutConfig(): void
    {
        $quoteId = '100000001';
        $kcoOrderId = '123456-1234-1234-1234-1234567890';
        $expectedResult = ShippingServiceUpdater::STATE_CODE;
        $expectedShippingData = [
            'subtotal' => '10.0000',
            'shipping_amount' => '7.9900',
            'shipping_tax_amount' => '1.9980',
            'shipping_incl_tax' => '9.9900',
            'grand_total' => '19.9900',
        ];

        $this->data->addData([
            'id' => $kcoOrderId,
        ]);
        $this->checkoutApiMock->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn([
                'id' => $kcoOrderId,
                'is_successful' => true,
                'order_id' => $kcoOrderId,
                'selected_shipping_option' => [
                    'id' => '019f46f5-4769-7b29-bbe9-a2b179d21424',
                    'name' => 'DHL Express',
                    'price' => 999,
                    'tax_amount' => 200,
                    'tax_rate' => 2500,
                    'shipping_method' => 'Home',
                    'delivery_details' => [
                        'carrier' => 'dhl-express',
                        'class' => 'standard',
                    ],
                ],
            ]);

        $quote = $this->quoteGetter->execute($quoteId);
        $this->checkoutSession->replaceQuote($quote);

        $result = $this->methodUpdater->updateByData($this->data);
        $this->assertEquals($expectedResult, $result);

        $quote = $this->quoteGetter->execute($quoteId);
        $shippingData = array_intersect_key($quote->getShippingAddress()->getData(), $expectedShippingData);
        $this->assertEquals($expectedShippingData, $shippingData);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kco/active 1
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 1
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoConfigFixture current_store klarna/api/debug 1
     * @magentoConfigFixture current_store general/region/state_required ''
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testUpdateByDataShouldSuccessfullyUpdateShippingCostsOnCartBasedOnApiDataKssConfig(): void
    {
        $quoteId = '100000001';
        $kcoOrderId = '123456-1234-1234-1234-1234567890';
        $expectedResult = ShippingServiceUpdater::STATE_CODE;
        $expectedShippingData = [
            'subtotal' => '10.0000',
            'shipping_amount' => '7.9900',
            'shipping_tax_amount' => '1.9980',
            'shipping_incl_tax' => '9.9900',
            'grand_total' => '19.9900',
        ];

        $this->data->addData([
            'id' => $kcoOrderId,
        ]);
        $this->checkoutApiMock->expects($this->atLeastOnce())->method('getOrder')
            ->willReturn([
                'id' => $kcoOrderId,
                'is_successful' => true,
                'order_id' => $kcoOrderId,
                'selected_shipping_option' => [
                    'id' => '019f46f5-4769-7b29-bbe9-a2b179d21424',
                    'name' => 'DHL Express',
                    'price' => 999,
                    'tax_amount' => 200,
                    'tax_rate' => 2500,
                    'shipping_method' => 'Home',
                    'delivery_details' => [
                        'carrier' => 'dhl-express',
                        'class' => 'standard',
                    ],
                ],
            ]);

        $quote = $this->quoteGetter->execute($quoteId);
        $this->checkoutSession->replaceQuote($quote);

        $result = $this->methodUpdater->updateByData($this->data);
        $this->assertEquals($expectedResult, $result);

        $quote = $this->quoteGetter->execute($quoteId);
        $shippingData = array_intersect_key($quote->getShippingAddress()->getData(), $expectedShippingData);
        $this->assertEquals($expectedShippingData, $shippingData);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store payment/klarna_kco/active 1
     * @magentoConfigFixture current_store payment/klarna_kss/enabled 0
     * @magentoConfigFixture current_store checkout/klarna_kco/use_full_checkout 0
     * @magentoConfigFixture current_store klarna/api/debug 1
     * @magentoConfigFixture current_store general/region/state_required ''
     * @magentoDataFixture Klarna_Base::Test/Integration/_files/fixtures/quote_setup1_single_simple_product.php
     */
    public function testUpdateByDataShouldExecuteDefaultFlowWithKssAndFullCheckoutOff(): void
    {
        $quoteId = '100000001';
        $kcoOrderId = '123456-1234-1234-1234-1234567890';
        $expectedResult = DefaultUpdater::STATE_CODE;
        $expectedShippingData = [
            'subtotal' => '10.0000',
            'shipping_amount' => '5.0000',
            'shipping_tax_amount' => '0.0000',
            'shipping_incl_tax' => '5.0000',
            'grand_total' => '15.0000',
        ];

        $this->data->addData([
            'id' => $kcoOrderId,
            'selected_shipping_option' => [
                'id' => 'flatrate_flatrate',
            ],
        ]);
        $this->checkoutApiMock->expects($this->never())->method('getOrder');

        $quote = $this->quoteGetter->execute($quoteId);
        $this->checkoutSession->replaceQuote($quote);

        $result = $this->methodUpdater->updateByData($this->data);
        $this->assertEquals($expectedResult, $result);

        $quote = $this->quoteGetter->execute($quoteId);
        $shippingData = array_intersect_key($quote->getShippingAddress()->getData(), $expectedShippingData);
        $this->assertEquals($expectedShippingData, $shippingData);
    }
}
