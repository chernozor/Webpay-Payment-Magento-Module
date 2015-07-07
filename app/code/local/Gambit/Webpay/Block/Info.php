<?php
/**
| WebPay Magento Module
| Copyright (C) 2015 Igor Gambit
| http://www.webmaster-gambit.ru/
+========================================================*
| Filename: app/code/local/Gambit/Webpay/Block/Info.php
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

class Gambit_Webpay_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('webpay/info.phtml');
    }
}