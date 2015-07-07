<?php
/**
| WebPay Magento Module
| Copyright (C) 2015 Igor Gambit
| http://www.webmaster-gambit.ru/
+========================================================*
| Filename: app/code/local/Gambit/Webpay/Block/Redirect.php
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

class Gambit_Webpay_Block_Redirect extends Mage_Core_Block_Abstract {
    protected function _toHtml() {
        $webpay = Mage::getModel('webpay/checkout');
        $form = new Varien_Data_Form();
        $form->setAction($webpay->getWebpayUrl())
            ->setId('Webpay')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($webpay->getWebpayCheckoutFormFields() as $field=>$value) {
             $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $html = '<html><body>';
        $html.= $this->__('Redirect to www.webpay.by, please, wait!');
        $html.= '<hr>';
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("Webpay").submit();</script>';
        $html.= '</body></html>';
        return $html;
    }
}
