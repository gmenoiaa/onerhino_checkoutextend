<?php
/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Block_Checkout extends Idev_OneStepCheckout_Block_Checkout {
	protected function _saveOrder() {
		
		// Hack to fix weird Magento payment behaviour
		$payment = $this->getRequest ()->getPost ( 'payment', false );
		if ($payment) {
			$payment = $this->filterPaymentData ( $payment );
			$this->getOnepage ()->getQuote ()->getPayment ()->importData ( $payment );
			
			$ccSaveAllowedMethods = array (
					'ccsave' 
			);
			$method = $this->getOnepage ()->getQuote ()->getPayment ()->getMethodInstance ();
			
			if (in_array ( $method->getCode (), $ccSaveAllowedMethods )) {
				$info = $method->getInfoInstance ();
				$info->setCcNumberEnc ( $info->encrypt ( $info->getCcNumber () ) );
			}
		}
		
		try {
			
			if (! $this->getOnepage ()->getQuote ()->isVirtual () && ! $this->getOnepage ()->getQuote ()->getShippingAddress ()->getShippingDescription ()) {
				Mage::throwException ( Mage::helper ( 'checkout' )->__ ( 'Please choose a shipping method' ) );
			}
			
			if (! Mage::helper ( 'customer' )->isLoggedIn ()) {
				$this->getOnepage ()->getQuote ()->setTotalsCollectedFlag ( false )->collectTotals ();
			}
			$order = $this->getOnepage ()->saveOrder ();
		} catch ( Exception $e ) {
			// need to activate
			$this->getOnepage ()->getQuote ()->setIsActive ( true );
			// need to recalculate
			$this->getOnepage ()->getQuote ()->getShippingAddress ()->setCollectShippingRates ( true )->collectTotals ();
			$error = $e->getMessage ();
			$this->formErrors ['unknown_source_error'] = $error;
			Mage::logException ( $e );
			Mage::helper ( 'checkout' )->sendPaymentFailedEmail ( $this->getOnepage ()->getQuote (), $error );
			$this->_registerFailedAttempt ();
			return;
			// die('Error: ' . $e->getMessage());
		}
		
		$this->afterPlaceOrder ();
		
		$redirectUrl = $this->getOnepage ()->getCheckout ()->getRedirectUrl ();
		
		if ($redirectUrl) {
			$redirect = $redirectUrl;
		} else {
			$this->getOnepage ()->getQuote ()->setIsActive ( false );
			$this->getOnepage ()->getQuote ()->save ();
			$redirect = $this->getUrl ( 'checkout/onepage/success' );
			// $this->_redirect('checkout/onepage/success', array('_secure'=>true));
		}
		$response = Mage::app ()->getResponse ();
		Mage::app ()->getFrontController ()->setNoRender ( true );
		return $response->setRedirect ( $redirect );
	}
	
	/**
	 * Registed the failted attempt to the customer session
	 */
	protected function _registerFailedAttempt() {
		Mage::helper ( 'onerhino_checkoutextend' )->registerFailedAttempt ();
	}
}