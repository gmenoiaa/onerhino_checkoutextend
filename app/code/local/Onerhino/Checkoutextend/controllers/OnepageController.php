<?php
require_once Mage::getModuleDir ( 'controllers', 'Mage_Checkout' ) . DS . 'OnepageController.php';

/**
 *
 * @author geiser
 *        
 */
class Onerhino_Checkoutextend_OnepageController extends Mage_Checkout_OnepageController {
	
	/**
	 * Create order action
	 */
	public function saveOrderAction() {
		if (! $this->_validateFormKey ()) {
			$this->_redirect ( '*/*' );
			return;
		}
		
		if ($this->_expireAjax ()) {
			return;
		}
		
		$result = array ();
		try {
			$requiredAgreements = Mage::helper ( 'checkout' )->getRequiredAgreementIds ();
			if ($requiredAgreements) {
				$postedAgreements = array_keys ( $this->getRequest ()->getPost ( 'agreement', array () ) );
				$diff = array_diff ( $requiredAgreements, $postedAgreements );
				if ($diff) {
					$result ['success'] = false;
					$result ['error'] = true;
					$result ['error_messages'] = $this->__ ( 'Please agree to all the terms and conditions before placing the order.' );
					$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
					return;
				}
			}
			
			$data = $this->getRequest ()->getPost ( 'payment', array () );
			if ($data) {
				$data ['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
				$this->getOnepage ()->getQuote ()->getPayment ()->importData ( $data );
			}
			
			$this->getOnepage ()->saveOrder ();
			
			$redirectUrl = $this->getOnepage ()->getCheckout ()->getRedirectUrl ();
			$result ['success'] = true;
			$result ['error'] = false;
		} catch ( Mage_Payment_Model_Info_Exception $e ) {
			$message = $e->getMessage ();
			if (! empty ( $message )) {
				$result ['error_messages'] = $message;
			}
			$result ['goto_section'] = 'payment';
			$result ['update_section'] = array (
					'name' => 'payment-method',
					'html' => $this->_getPaymentMethodsHtml () 
			);
		} catch ( Mage_Core_Exception $e ) {
			Mage::logException ( $e );
			Mage::helper ( 'checkout' )->sendPaymentFailedEmail ( $this->getOnepage ()->getQuote (), $e->getMessage () );
			$this->_registerFailedAttempt ();
			$result ['success'] = false;
			$result ['error'] = true;
			$result ['error_messages'] = $e->getMessage ();
			
			$gotoSection = $this->getOnepage ()->getCheckout ()->getGotoSection ();
			if ($gotoSection) {
				$result ['goto_section'] = $gotoSection;
				$this->getOnepage ()->getCheckout ()->setGotoSection ( null );
			}
			$updateSection = $this->getOnepage ()->getCheckout ()->getUpdateSection ();
			if ($updateSection) {
				if (isset ( $this->_sectionUpdateFunctions [$updateSection] )) {
					$updateSectionFunction = $this->_sectionUpdateFunctions [$updateSection];
					$result ['update_section'] = array (
							'name' => $updateSection,
							'html' => $this->$updateSectionFunction () 
					);
				}
				$this->getOnepage ()->getCheckout ()->setUpdateSection ( null );
			}
		} catch ( Exception $e ) {
			Mage::logException ( $e );
			Mage::helper ( 'checkout' )->sendPaymentFailedEmail ( $this->getOnepage ()->getQuote (), $e->getMessage () );
			$this->_registerFailedAttempt ();
			$result ['success'] = false;
			$result ['error'] = true;
			$result ['error_messages'] = $this->__ ( 'There was an error processing your order. Please contact us or try again later.' );
		}
		$this->getOnepage ()->getQuote ()->save ();
		/**
		 * when there is redirect to third party, we don't want to save order yet.
		 * we will save the order in return action.
		 */
		if (isset ( $redirectUrl )) {
			$result ['redirect'] = $redirectUrl;
		}
		
		$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $result ) );
	}
	
	/**
	 * Get order review step html
	 *
	 * @return string
	 */
	protected function _getReviewHtml() {
		$html = parent::_getReviewHtml ();
		return $html;
	}
	
	/**
	 * Registed the failted attempt to the customer session
	 */
	protected function _registerFailedAttempt() {
		Mage::helper ( 'onerhino_checkoutextend' )->registerFailedAttempt ();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Mage_Core_Controller_Varien_Action::_initLayoutMessages()
	 */
	protected function _initLayoutMessages($messagesStorage) {
		parent::_initLayoutMessages ( $messagesStorage );
		
		if (Mage::helper ( 'onerhino_checkoutextend' )->isMaxFailedAttemptReached ()) {
			
			$captchaBlock = $this->getLayout ()->createBlock ( 'studioforty9_recaptcha/explicit', 'studioforty9.recaptcha.explicit', array ( 'template' => 'studioforty9/recaptcha/explicit.phtml' ) );
			$jsBlock = $this->getLayout ()->createBlock ( 'onerhino_checkoutextend/onepage');
			
			$this->getLayout ()->getBlock ( 'content' )->append ( $captchaBlock );
			$this->getLayout ()->getBlock ( 'content' )->append ( $jsBlock );
		}
		
		return $this;
	}
}