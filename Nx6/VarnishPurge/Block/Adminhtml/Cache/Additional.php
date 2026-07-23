<?php
declare(strict_types=1);

namespace Nx6\VarnishPurge\Block\Adminhtml\Cache;

use Magento\Backend\Block\Cache\Additional as CoreAdditional;
use Magento\Backend\Block\Template\Context;
use Magento\PageCache\Model\Config as PageCacheConfig;

class Additional extends CoreAdditional
{
    public function __construct(
        Context $context,
        private readonly PageCacheConfig $pageCacheConfig,
        array $data = [],
    ) {
        parent::__construct($context, $data);
    }

    public function isVarnishEnabled(): bool
    {
        return $this->pageCacheConfig->getType() === PageCacheConfig::VARNISH;
    }

    public function hasAccessToVarnishPurge(): bool
    {
        return $this->getAuthorization()->isAllowed('Nx6_VarnishPurge::varnish_purge');
    }

    public function getVarnishPurgeUrl(): string
    {
        return $this->getUrl('nx6_varnish/cache/purge');
    }
}
