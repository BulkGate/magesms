<?php

namespace BulkGate\Magesms\Plugin;

use BulkGate\Magesms\Bulkgate\DIContainer;
use BulkGate\Magesms\Extensions\Json;
use BulkGate\Magesms\Extensions\Compress;

/**
 * Class PluginBefore
 * @package BulkGate\Magesms\Plugin\PluginBefore
 */
class PluginBefore
{
    /**
     * @var DIContainer
     */
    private DIContainer $dIContainer;

    public function __construct(DIContainer $dIContainer)
    {
        $this->dIContainer = $dIContainer;
    }

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        if($this->_request->getFullActionName() == 'sales_order_view'){
            $order = $context->getOrder();
            if (!$order) {
                return;
            }
            $shipping = $order->getShippingAddress();
            if (empty($shipping)) {
                $shipping = $order->getBillingAddress();
            }
            if ($shipping) {
                $countryId = $shipping->getCountryId();
                $phone = $shipping->getTelephone();

                if ($countryId && $phone) {
                    $settings = $this->dIContainer->getSettings();
                    $data = [
                        'appId' => $settings->load('static:application_id', ''),
                        'lang' => $settings->load('main:language', 'en'),
                        'authUrl' => $context->getUrl('magesms/index/ajax').'?isAjax=true',
                        'salt' => Compress::compress(""),
                        'id' => preg_replace('/[ -\/()]+/', '', $phone),
                        'key' => $countryId,
                    ];

                    $buttonList->add(
                        'magesmsSendSms',
                        [
                            'label' => __('Send SMS'),
                            'data_attribute' => [
                                'params' => Json::encode($data)
                            ],
                            'class' => 'magesms',
                            'id' => 'magesms-order-sendsms'
                        ],
                        -1
                    );
                }
            }

        }
    }
}
