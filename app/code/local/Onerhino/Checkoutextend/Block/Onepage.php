<?php

/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Block_Onepage extends Mage_Core_Block_Template {
	
	/**
	 * Render block HTML
	 *
	 * @return string
	 */
	protected function _toHtml() {
		$message = $this->__('Please check the reCAPTCHA');
		$html = <<<JS
<script type="text/javascript">
// move captcha to review step
var recaptcha = $$('.recaptcha').first();
if(recaptcha !== undefined) { 
    var formLastColumn = $('checkout-step-review');
    formLastColumn.insert(recaptcha);
}
// override save button action
var _bkpReviewSave = Review.prototype.save;
Review.prototype.save = function() {
	var recaptcha = $('g-recaptcha-response').value;				
	if(!recaptcha) {
		alert('$message');
		return;
	}
	if($(payment.form).adjacent('input[name= g-recapctha-response]').length == 0) {
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", "g-recaptcha-response");
		input.setAttribute("value", recaptcha);
		$(payment.form).firstDescendant().insert(input);
	}
    var reviewResponse = _bkpReviewSave.apply(this, arguments);
    return reviewResponse;
};
</script>	
JS;
		return $html;
	}
}