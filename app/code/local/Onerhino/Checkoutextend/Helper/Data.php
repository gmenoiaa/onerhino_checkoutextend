<?php

/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Helper_Data {
	const MAX_FAILED_ATTEMPTS = 2;
	
	/**
	 * Register the failted attempt to the customer session
	 */
	public function registerFailedAttempt() {
		$failedAttempts = Mage::getSingleton ( 'customer/session' )->getFailedAttempts ();
		$failedAttempts ++;
		Mage::getSingleton ( 'customer/session' )->setFailedAttempts ( $failedAttempts );
	}
	
	/**
	 * Check if the max failed attempts reached
	 *
	 * @return boolean
	 */
	public function isMaxFailedAttemptReached() {
		$failedAttempts = Mage::getSingleton ( 'customer/session' )->getFailedAttempts ();
		return $failedAttempts > self::MAX_FAILED_ATTEMPTS;
	}
}