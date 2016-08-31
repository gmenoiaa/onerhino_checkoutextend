<?php

/**
 * 
 * @author geiser
 *
 */
class Onerhino_Checkoutextend_Block_Onestepcheckout extends Mage_Core_Block_Template {
	
	/**
	 * Render block HTML
	 *
	 * @return string
	 */
	protected function _toHtml() {
		$html = <<<JS

<script type="text/javascript">
    document.observe("dom:loaded", function () {

		var recaptcha = $$('.recaptcha').first();
		if(recaptcha !== undefined) { 
		    var formLastColumn = $$('.onestepcheckout-column-padleft').first();
		    formLastColumn.insert(recaptcha);
		}

    });
</script>	
JS;
		return $html;
	}
}