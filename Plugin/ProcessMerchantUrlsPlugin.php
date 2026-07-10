<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

declare(strict_types=1);

namespace Klarna\Kss\Plugin;

use Klarna\AdminSettings\Model\Configurations\Kco\Checkout;
use Klarna\Base\Api\BuilderInterface;
use Klarna\Kss\Model\KssConfigProvider;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @internal
 */
class ProcessMerchantUrlsPlugin
{
    /**
     * @var KssConfigProvider
     */
    private $config;

    /**
     * @var Checkout
     */
    private $checkoutConfig;

    /**
     * @param KssConfigProvider $config
     * @param Checkout $checkoutConfig
     */
    public function __construct(
        KssConfigProvider $config,
        Checkout $checkoutConfig
    ) {
        $this->config = $config;
        $this->checkoutConfig = $checkoutConfig;
    }

    /**
     * Remove shipping_option_update callback if KSS is enabled in favor of kco/api/updateKssStatus and
     * kco/api/updateKssDiscountOrder instead of kco/api/shippingMethodUpdate. Technically latter can now
     * also work with KSS, but it needs some work. For now this does not remove the callback with full checkout
     * turned on, since full checkout will not call the KSS related controllers. Which is why we need
     * the original callback to execute to support shipping method changes.
     *
     * @param BuilderInterface $subject
     * @param array $result
     * @param StoreInterface $store
     * @param array $urlParams
     *
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterProcessMerchantUrls(BuilderInterface $subject, $result, $store, $urlParams): array
    {
        if ($this->config->isKssEnabled($store) && !$this->checkoutConfig->isUseFullCheckout($store)) {
            unset($result['shipping_option_update']);
        }

        return $result;
    }
}
