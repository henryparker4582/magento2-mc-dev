<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/5/17 3:40 PM
 * @file: GetAccountDetails.php
 */

namespace Ebizmarts\MailChimp\Controller\Adminhtml\Ecommerce;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Getaccountdetails extends Action
{
    /**
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $_helper;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * GetAccountDetails constructor.
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        \Ebizmarts\MailChimp\Helper\Data $helper
    )
    {
        parent::__construct($context);
        $this->_resultFactory       = $resultFactory;
        $this->_helper                  = $helper;
    }
    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $apiKey = $param['apikey'];
        $store  = $param['store'];
        $this->_helper->log($apiKey);
        $this->_helper->log($store);

        $api = $this->_helper->getApiByApiKey($apiKey);
        $apiInfo = $api->root->info();
        $options = [];
        if(isset($apiInfo['account_name'])) {
            $options['username'] = ['label' => __('User name:'), 'value' => $apiInfo['account_name']];
            $options['total_subscribers'] = ['label'=> __('Total Subscribers:'), 'value' => $apiInfo['total_subscribers']];
            if($store != -1) {
                $options['subtitle'] = ['label'=> __('Ecommerce Data uploaded to MailChimp:'), 'value' =>''];
                $totalCustomers = $api->ecommerce->customers->getAll($store, 'total_items');
                $options['total_customers'] = ['label' => __('Total customers:'), 'value' => $totalCustomers['total_items']];
                $totalProducts = $api->ecommerce->products->getAll($store, 'total_items');
                $options['total_products'] = ['label'=> __('Total products:'), 'value' =>$totalProducts['total_items']];
                $totalOrders = $api->ecommerce->orders->getAll($store, 'total_items');
                $options['total_orders'] = ['label'=> __('Total orders:'), 'value' =>$totalOrders['total_items']];
                $totalCarts = $api->ecommerce->carts->getAll($store, 'total_items');
                $options['total_carts'] = ['label'=> __('Total Carts:'), 'value' =>$totalCarts['total_items']];

            }
        }


        $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($options);
        return $resultJson;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ebizmarts_MailChimp::config_mailchimp');
    }
}