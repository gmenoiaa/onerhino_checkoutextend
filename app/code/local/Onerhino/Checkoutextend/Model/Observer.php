<?php

/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Model_Observer {
	
	/**
	 * Add the route for the form to the available selections in configuration.
	 *
	 * @param Varien_Event_Observer $observer
	 *        	The dispatched observer
	 */
	public function addRecaptchaRouteToConfiguration(Varien_Event_Observer $observer) {
		$observer->getRoutes ()->add ( 'checkout_onepage_saveorder', 'Onepage Checkout' );
		$observer->getRoutes ()->add ( 'onestepcheckout_index_index', 'OneStepCheckout' );
	}
	
	/**
	 * Event dispatched when verifying the recaptcha for a given route.
	 *
	 * @param Varien_Event_Observer $observer        	
	 */
	public function onVerifyRecaptcha(Varien_Event_Observer $observer) {
		/** @var Studioforty9_Recaptcha_Helper_Response $response */
		$response = $observer->getResponse ();
		$route = $observer->getRoute ();
		
		if ((strtolower ( $route ) == 'checkout_onepage_saveorder' || strtolower ( $route ) == 'onestepcheckout_index_index') && ! Mage::helper ( 'onerhino_checkoutextend' )->isMaxFailedAttemptReached ()) {
			$response->setSuccess ( true );
		}
		
		return $observer;
	}
	
	/**
	 * Process a fail response.
	 *
	 * @param Varien_Event_Observer $observer        	
	 */
	public function onFailedRecaptcha(Varien_Event_Observer $observer) {
		/** @var Mage_Core_Controller_Front_Action $controller */
		$controller = $observer->getEvent ()->getControllerAction ();
		$route = $controller->getFullActionName ();
		
		if (strtolower ( $route ) == 'checkout_onepage_saveorder') {
			
			/** @var Studioforty9_Recaptcha_Helper_Response $response */
			$response = $observer->getEvent ()->getRecaptchaResponse ();
			
			$controller->getResponse ()->clearBody ();
			
			$redirectUrl = false;
			$headers = $controller->getResponse ()->getHeaders ();
			foreach ( $headers as $header ) {
				
				if ($header ['name'] == 'Location') {
					$redirectUrl = $header ['value'];
				}
			}
			
			$result = array ();
			$result ['success'] = false;
			$result ['error'] = true;
			
			if ($redirectUrl) {
				$result ['redirect'] = $redirectUrl;
			} else {
				$result ['error_messages'] = 'There was an error with the recaptcha code, please try again.';
			}
			
			$controller->getResponse ()->clearAllHeaders ();
			$controller->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
		}
	}
}