<?php
require_once Mage::getModuleDir ( 'controllers', 'Idev_OneStepCheckout' ) . DS . 'IndexController.php';

/**
 *
 * @author geiser
 *        
 */
class Onerhino_Checkoutextend_IndexController extends Idev_OneStepCheckout_IndexController {
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Idev_OneStepCheckout_IndexController::indexAction()
	 */
	public function indexAction() {
		parent::indexAction ();
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Mage_Core_Controller_Varien_Action::generateLayoutBlocks()
	 */
	public function generateLayoutBlocks() {
		parent::generateLayoutBlocks ();
		
		if (Mage::helper ( 'onerhino_checkoutextend' )->isMaxFailedAttemptReached ()) {
		
			$captchaBlock = $this->getLayout ()->createBlock ( 'studioforty9_recaptcha/explicit', 'studioforty9.recaptcha.explicit', array ( 'template' => 'studioforty9/recaptcha/explicit.phtml' ) );
			$jsBlock = $this->getLayout()->createBlock('onerhino_checkoutextend/onestepcheckout');
			
			$this->getLayout ()->getBlock ( 'content' )->append ( $captchaBlock );
			$this->getLayout ()->getBlock ( 'content' )->append ( $jsBlock );
			
		}
		
		return $this;
	}
}
