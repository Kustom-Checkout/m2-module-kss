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
     * @param Checkout $checkoutConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Checkout $checkoutConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutConfig = $checkoutConfig;
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
}
