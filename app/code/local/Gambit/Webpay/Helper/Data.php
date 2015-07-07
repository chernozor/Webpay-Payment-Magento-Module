<?php
/**
| WebPay Magento Module
| Copyright (C) 2015 Igor Gambit
| http://www.webmaster-gambit.ru/
+========================================================*
| Filename: app/code/local/Gambit/Webpay/Helper/Data.php
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

class Gambit_Webpay_Helper_Data extends Mage_Core_Helper_Abstract {
	public function log($message)	{
		$file = $this->getLogFileName();
		if (is_array($message))	{
			Mage::dispatchEvent('webpay_log_file_write_before', $message);
			$forLog = array();
			foreach ($message as $answerKey => $answerValue)
			{
				$forLog[] = $answerKey.": ".$answerValue;
			}
			$forLog[] = '***************************';
			$message = implode("\r\n", $forLog);
		}
		Mage::log($message, Zend_Log::DEBUG, $file, true);
		return true;
	}
	public function arrayToRawData($array)
	{
		foreach ($array as $key => $value)
		{
			$newArray[] = $key.": ".$value;
		}
		$raw = implode("\r\n", $newArray);
		return $raw;
	}
	public function getLogFileName()	{
		return 'gambit_webpay.log';
	}
}