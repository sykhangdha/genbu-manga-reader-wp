/**
 * Metabox Color Picker
 */
jQuery(document).ready(function ($) {
	'use strict';

	/**
	 * Initialize color picker
	 */
	$('input:text.tmb_colorpicker').each(function (i) {
		$(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
		$('#picker-' + i).hide().farbtastic($(this));
	})
	.focus(function() {
		$(this).next().show();
	})
	.blur(function() {
		$(this).next().hide();
	});
});