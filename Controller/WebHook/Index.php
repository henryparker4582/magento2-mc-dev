<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/23/17 3:36 PM
 * @file: Index.php
 */

namespace Ebizmarts\MailChimp\Controller\WebHook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends Action{
    const WEBHOOK__PATH = 'mailchimp/webhook/index';
    /**
     * @var ResultFactory
     */
    private $_ressultFactory;
    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    private $_helper;
    /**
     * @var \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory
     */
    private $_chimpWebhookRequestFactory;
    private $_remoteAddress;

    /**
     * Index constructor.
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory $chimpWebhookRequestFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Ebizmarts\MailChimp\Helper\Data $helper,
        \Ebizmarts\MailChimp\Model\MailChimpWebhookRequestFactory $chimpWebhookRequestFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    )
    {
        parent::__construct($context);
        $this->_ressultFactory              = $resultFactory;
        $this->_helper                      = $helper;
        $this->_chimpWebhookRequestFactory  = $chimpWebhookRequestFactory;
        $this->_remoteAddress               = $remoteAddress;
    }

    public function execute()
    {
        $requestKey = $this->getRequest()->getParam('wkey');
        if (!$requestKey) {
            $this->_helper->log('No wkey parameter from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result = $this->_ressultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(403);
            return $result;
        }
        $key = $this->_helper->getWebhooksKey();
        if ($key!=$requestKey) {
            $this->_helper->log('wkey parameter is invalid from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result = $this->_ressultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(403);
            return $result;
        }
        if ($this->getRequest()->getPost('type')) {
            $request = $this->getRequest()->getPost();
            $chimpRequest = $this->_chimpWebhookRequestFactory->create();
            $chimpRequest->setType($request['type']);
            $chimpRequest->setFiredAt($request['fired_at']);
            $chimpRequest->setDataRequest(serialize($request['data']));
            $chimpRequest->setProcessed(false);
            $chimpRequest->getResource()->save($chimpRequest);
        }
        else {
            $this->_helper->log('An empty request comes from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result = $this->_ressultFactory->create(ResultFactory::TYPE_RAW)->setHttpResponseCode(403);
            return $result;
        }
    }
}