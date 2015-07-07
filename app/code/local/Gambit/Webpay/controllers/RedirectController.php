<?php
/**
| WebPay Magento Module
| Copyright (C) 2015 Igor Gambit
| http://www.webmaster-gambit.ru/
+========================================================*
| Filename: app/code/local/Gambit/Webpay/controllers/RedirectController.php
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

class Gambit_Webpay_RedirectController extends Mage_Core_Controller_Front_Action {
	protected function _getCheckout()	{
		return Mage::getSingleton('checkout/session');
	}
	public function indexAction()	{
		$this->getResponse()->setHeader('Content-type', 'text/html; charset=utf8')->setBody($this->getLayout()->createBlock('webpay/redirect')->toHtml());
	}
	public function successAction()	{
		$event = $this->getRequest()->getParams();
		$helper = Mage::helper("webpay");
		$helper->log('Success');
		$helper->log($event['wsb_tid']);
		$transaction_id = $event['transaction_id'];
		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($transaction_id);
		Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
		$this->_redirect('checkout/onepage/success', array('_secure' => true));
	}
	public function cancelAction()	{
		$event = $this->getRequest()->getParams();
		$order_id = $event['transaction_id'];
		$this->_getCheckout()->addError(Mage::helper('webpay')->__('The order has been cancelled. Order #').$order_id);
		$this->_redirect('checkout/cart');
	}
	public function notifyAction()	{
		$helper = Mage::helper("webpay");
		//запилить проверку подлинности данных по сигнатуре
		$event = $this->getRequest()->getParams();
		$helper->log('Отстук от webpay:');
		$helper->log($event);
		$batch_timestamp = $event['batch_timestamp'];
		$currency_id = $event['currency_id'];
		$amount = $event['amount'];
		$payment_method = $event['payment_method'];
		$order_id = $event['order_id'];
		$site_order_id = $event['site_order_id'];
		$transaction_id = $event['transaction_id'];
		$payment_type = $event['payment_type'];
		$rrn = $event['rrn'];
		$wsb_signature = $event['wsb_signature'];
		$skey = Mage::getStoreConfig(Gambit_Webpay_Model_Checkout::cryptkey);
		$webpay_signature = md5($batch_timestamp.$currency_id.$amount.$payment_method.$order_id.$site_order_id.$transaction_id.$payment_type.$rrn.$skey);
		if ($webpay_signature == $event['wsb_signature'])
		{
			//обновление статуса заказа
			$tid = str_replace('ORDER-','',$event['site_order_id']);
			$order = Mage::getModel('sales/order')->loadByIncrementId($tid);
				if ($order->canInvoice())	{
					try	{
						$invoice = $order->prepareInvoice();
						$invoice->register();
						$invoice->getOrder()->setIsInProcess(true);
						$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($invoice)
							->addObject($invoice->getOrder())
							->save();
						$helper->log('Оплата заказа '.$site_order_id.' проведена успешно.');
						$this->getResponse()->setHeader('HTTP/1.0','200 OK')->sendResponse();
						exit;
					} catch (Mage_Core_Exception $e) {
						$helper->log('Ошибка: '.$e);
					}
				}
		}
		return true;
	}
}
?>