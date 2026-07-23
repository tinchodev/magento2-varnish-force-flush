<?php
declare(strict_types=1);

namespace Nx6\VarnishPurge\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PurgeButton extends Field
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_template = 'Nx6_VarnishPurge::system/config/purge_button.phtml';
    }

    #[\Override]
    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->addData([
            'button_label' => __('Purge Varnish Cache'),
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('nx6_varnish/varnish/purge'),
        ]);

        return $this->_toHtml();
    }
}
