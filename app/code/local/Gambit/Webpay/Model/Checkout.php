<?php
/**
| WebPay Magento Module
| Copyright (C) 2015 Igor Gambit
| http://www.webmaster-gambit.ru/
+========================================================*
| Filename: app/code/local/Gambit/Webpay/Model/Checkout.php
| Author: Igor Gambit (Gambit)
| Url: http://webmaster-gambit.ru
+========================================================*
| This module is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+========================================================*/

class Gambit_Webpay_Model_Checkout extends Mage_Payment_Model_Method_Abstract {
    protected $_code          = 'webpay';
    protected $_formBlockType = 'webpay/form';
    protected $_infoBlockType = 'webpay/info';
    const     webpay_id = 'payment/webpay/webpay_webpayid';
    const     test      = 'payment/webpay/webpay_test';
    const     cryptkey  = 'payment/webpay/webpay_cryptkey';
    const     currency  = 'payment/webpay/webpay_currency';
    const     version   = 'payment/webpay/webpay_version';
    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('webpay/redirect', array('_secure' => true));
    }
    public function getWebpayUrl() {
        $url = '';
        if( Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::test) == '1')
            $url = 'https://securesandbox.webpay.by/';
        elseif(Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::test) == '0')
            $url = 'https://payment.webpay.by/';
        return $url;
    }
    public function getLocale() {
        return Mage::app()->getLocale()->getLocaleCode();
    }
    public function getWebpayCheckoutFormFields() {

        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        if ($order->getBillingAddress()->getEmail()) {
            $email = $order->getBillingAddress()->getEmail();
        } else {
            $email = $order->getCustomerEmail();
        }
        $wsb_seed = time();
        $wsb_storeid = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::webpay_id);
        $wsb_order_num = 'ORDER-'.$order_id;
        $wsb_test = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::test);
        $wsb_currency_id = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::currency);
        $wsb_total = trim(round($order->getGrandTotal(), 2));
        $SecretKey = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::cryptkey);
        $wsb_version = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::version);
        if($wsb_version != 1) $wsb_signature = sha1($wsb_seed.$wsb_storeid.$wsb_order_num.$wsb_test.$wsb_currency_id.$wsb_total.$SecretKey);
        else $wsb_signature = md5($wsb_seed.$wsb_storeid.$wsb_order_num.$wsb_test.$wsb_currency_id.$wsb_total.$SecretKey);
        $params = array(
           '*scart'                 => '',
            'wsb_version'           => $wsb_version,
            'wsb_storeid'           => $wsb_storeid,
            'wsb_order_num'         => $wsb_order_num,
            'wsb_test'              => $wsb_test,
            'wsb_currency_id'       => $wsb_currency_id,
            'wsb_seed'              => $wsb_seed,
            'wsb_return_url'        => Mage::getUrl('webpay/redirect/success', array('transaction_id' => $order_id)),
            'wsb_cancel_return_url' => Mage::getUrl('webpay/redirect/cancel', array('transaction_id' => $order_id)),
                'wsb_notify_url'        => Mage::getUrl('webpay/redirect/notify'),
            'wsb_email'             => $email,
            'wsb_tax' => $order->getData('tax_amount'),
            'wsb_shipping_name' => $order->getData('shipping_description'),
            'wsb_shipping_price' => $order->getData('shipping_amount'),
            'wsb_total'             => $wsb_total,
            'wsb_signature'         => $wsb_signature,
        );
        $productIds = array();
        foreach ($order->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addIdFilter($productIds)
            ->load();
        $i = 0;
        foreach ($order->getAllItems() as $key=>$item) {
            if ($productCollection->getItemById($item->getProductId())) {
                $paramss[$key] =  array(
                    'wsb_invoice_item_name['.$key.']'=>$item->getName(),
                    'wsb_invoice_item_quantity['.$key.']'=> $item->getQtyOrdered(),
                    'wsb_invoice_item_price['.$key.']'=>$item->getPrice(),
                );
            }
            $i++;
        }
        foreach($paramss as $item) {
            $params = $params+$item;
        }
        return $params;
    }
    public function initialize($paymentAction, $stateObject)    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }
}