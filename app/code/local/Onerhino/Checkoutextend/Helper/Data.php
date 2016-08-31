<?php

/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Helper_Data {
	const MAX_FAILED_ATTEMPTS = 3;
	
	/**
	 * Register the failted attempt to the customer session
	 */
	public function registerFailedAttempt() {
		$failedAttempts = Mage::getSingleton ( 'customer/session' )->getFailedAttempts ();
		$failedAttempts ++;
		
		Mage::log ( 'Registered failed attempt (' . $failedAttempts . '/' . self::MAX_FAILED_ATTEMPTS . ') for session ' . Mage::getSingleton ( 'customer/session' )->getSessionId (), null, 'checkoutextend.log' );
		
		Mage::getSingleton ( 'customer/session' )->setFailedAttempts ( $failedAttempts );
	}
	
	/**
	 * Check if the max failed attempts reached
	 *
	 * @return boolean
	 */
	public function isMaxFailedAttemptReached() {
		$failedAttempts = Mage::getSingleton ( 'customer/session' )->getFailedAttempts ();
		return $failedAttempts >= self::MAX_FAILED_ATTEMPTS;
	}
}