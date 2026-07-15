<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Model\Cart\ShippingMethodUpdate\UpdaterComponent;

use Klarna\Kco\Model\Cart\ShippingMethod\KlarnaRequestQuoteTransformer;
use Klarna\Kco\Model\Cart\ShippingMethodUpdate\UpdaterComponentInterface;
use Klarna\Kco\Model\Checkout\Kco\Initializer;
use Klarna\Kco\Model\WorkflowProvider;
use Klarna\Kss\Model\KssConfigProvider;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class ShippingServiceUpdater implements UpdaterComponentInterface
{
    public const STATE_CODE = 200;

    /**
     * @var WorkflowProvider
     */
    private WorkflowProvider $workflowProvider;

    /**
     * @var KlarnaRequestQuoteTransformer
     */
    private KlarnaRequestQuoteTransformer $quoteTransformer;

    /**
     * @var Initializer
     */
    private Initializer $kcoInitializer;

    /**
     * @var KssConfigProvider
     */
    private KssConfigProvider $kssConfigProvider;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param WorkflowProvider $workflowProvider
     * @param KlarnaRequestQuoteTransformer $quoteTransformer
     * @param Initializer $kcoInitializer
     * @param KssConfigProvider $kssConfigProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WorkflowProvider $workflowProvider,
        KlarnaRequestQuoteTransformer $quoteTransformer,
        Initializer $kcoInitializer,
        KssConfigProvider $kssConfigProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->workflowProvider = $workflowProvider;
        $this->quoteTransformer = $quoteTransformer;
        $this->kcoInitializer = $kcoInitializer;
        $this->kssConfigProvider = $kssConfigProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function isRelevant(): bool
    {
        return $this->kssConfigProvider->isKssEnabled($this->storeManager->getStore());
    }

    /**
     * Should ensure that this is executed before DefaultUpdater
     *
     * @inheritDoc
     */
    public function getSortOrder(): ?int
    {
        return 50;
    }

    /**
     * Main use case for this is full checkout, but this depends on KSS setting since that one follows full checkout
     * setting anyway, and eventually we could consider replacing kco/api/updateKssStatus and kco/api/updateKssDiscountOrder
     * with kco/api/shippingMethodUpdate, which calls to this logic. So KSS could also do the updates through this
     * logic, but this needs more work.
     *
     * @inheritDoc
     */
    public function executeByData(DataObject $data): int
    {
        $klarnaOrderId = (string) $data->getId();

        // TODO: Could replace workflow usage with some utility that just converts id to quote without jumping
        // through extra hoops like this
        $this->workflowProvider->setKlarnaOrderId($klarnaOrderId);
        $quote = $this->workflowProvider->getMagentoQuote();

        // Needed to support full checkout: the calls are done from external site so session info doesn't
        // carry over
        $this->kcoInitializer->getKcoSession()->setQuote($quote);
        $klarnaOrder = $this->kcoInitializer->getOrder($klarnaOrderId);

        $this->quoteTransformer->updateQuoteShippingMethod(
            $klarnaOrder,
            $klarnaOrderId
        );

        return self::STATE_CODE;
    }
}
