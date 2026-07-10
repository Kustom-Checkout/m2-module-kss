<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Model;

use Klarna\AdminSettings\Model\Configurations\Kco\Checkout;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Providing configuration values
 *
 * @internal
 */
class KssConfigProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Checkout
     */
    private $checkoutConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Checkout|null $checkoutConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ?Checkout $checkoutConfig = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutConfig = $checkoutConfig ?: ObjectManager::getInstance()->get(Checkout::class);
    }

    /**
     * Returns true if KSS is enabled in the admin or if full checkout is in use. Full checkout is technically
     * the same as using the iframe, but it's just located on external site, so we need to consider that as
     * having KSS turned on as well.
     *
     * @param StoreInterface $store
     *
     * @return bool
     */
    public function isKssEnabled(StoreInterface $store): bool
    {
        if ($this->checkoutConfig->isUseFullCheckout($store)) {
            return true;
        }

        return $this->scopeConfig->isSetFlag(
            'payment/klarna_kss/enabled',
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * There are various pieces of logic that are relevant to execute when KSS is turned on, but not when
     * full checkout is turned on. This is intended for helping do that check without duplicating this.
     *
     * @param StoreInterface $store
     *
     * @return bool
     */
    public function isKssAdjustmentsRelevant(StoreInterface $store): bool
    {
        return $this->isKssEnabled($store) && !$this->checkoutConfig->isUseFullCheckout($store);
    }
}
