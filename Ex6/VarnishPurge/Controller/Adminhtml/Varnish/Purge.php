<?php
declare(strict_types=1);

namespace Ex6\VarnishPurge\Controller\Adminhtml\Varnish;

use Ex6\VarnishPurge\Model\VarnishPurger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Purge extends Action
{
    public const string ADMIN_RESOURCE = 'Ex6_VarnishPurge::varnish_purge';

    public function __construct(
        Context $context,
        private readonly VarnishPurger $varnishPurger,
        private readonly JsonFactory $jsonFactory,
    ) {
        parent::__construct($context);
    }

    #[\Override]
    public function execute()
    {
        $result = $this->varnishPurger->purge();

        return $this->jsonFactory->create()->setData([
            'success' => $result->success,
            'message' => $result->message,
        ]);
    }
}
