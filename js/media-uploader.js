/*-----------------------------------------------------------------------------------*/
/* tmbFramework Media Library-driven AJAX File Uploader Module
/* JavaScript Functions (2010-11-05)
/*
/* The code below is designed to work as a part of the tmbFramework Media Library-driven
/* AJAX File Uploader Module. It is included only on screens where this module is used.
/*
/* Used with (very) slight modifications for Options Framework.
/* another (very) slight modifications for Tamatebako, so it only valid for images. 
/* .jpg|jpeg|png|gif|ico
/*-----------------------------------------------------------------------------------*/

(function ($) {

	tmboptions1MLU = {

/*-----------------------------------------------------------------------------------*/
/* Remove file when the "remove" button is clicked.
/*-----------------------------------------------------------------------------------*/

	removeFile: function () {

		$('.mlu_remove').live('click', function(event) { 
			$(this).hide();
			$(this).parents().parents().children('.upload').attr('value', '');
			$(this).parents('.screenshot').slideUp();
			$(this).parents('.screenshot').siblings('.of-background-properties').hide(); //remove background properties
			return false;
		});

		// Hide the delete button on the first row 
		$('a.delete-inline', "#option-1").hide();

	}, // End removeFile

/*-----------------------------------------------------------------------------------*/
/* Replace the default file upload field with a customised version.
/*-----------------------------------------------------------------------------------*/

	recreateFileField: function () {
    
		$('input.file').each(function(){
			var uploadbutton = '<input class="upload_file_button" type="button" value="Upload" />';
			$(this).wrap('<div class="file_wrap" />');
			$(this).addClass('file').css('opacity', 0); //set to invisible
			$(this).parent().append($('<div class="fake_file" />').append($('<input type="text" class="upload" />').attr('id',$(this).attr('id')+'_file')).val( $(this).val() ).append(uploadbutton));
 
			$(this).bind('change', function() {
				$('#'+$(this).attr('id')+'_file').val($(this).val());
			});
			$(this).bind('mouseout', function() {
				$('#'+$(this).attr('id')+'_file').val($(this).val());
			});
		});
      
    }, // End recreateFileField

/*-----------------------------------------------------------------------------------*/
/* Use a custom function when working with the Media Uploads popup.
/* Requires jQuery, Media Upload and Thickbox JavaScripts.
/*-----------------------------------------------------------------------------------*/

	mediaUpload: function () {

		jQuery.noConflict();

		$( 'input.upload_button' ).removeAttr('style');

		var formfield,
			formID,
			btnContent = true,
			tbframe_interval;
			// On Click
			$('input.upload_button').live("click", function () {
			formfield = $(this).prev('input').attr('id');
			formID = $(this).attr('rel');

			//Change "insert into post" to "Use this Button"

			tbframe_interval = setInterval(function() {jQuery('#TB_iframeContent').contents().find('.savesend .button').val('Use This Image');}, 2000);

			// Display a custom title for each Thickbox popup.
			var tmb_title = '';

			if ( $(this).parents('.section').find('.heading') ) { tmb_title = $(this).parents('.section').find('.heading').text(); } // End IF Statement

			tb_show( tmb_title, 'media-upload.php?post_id=0&type=image&TB_iframe=1' );
			return false;
		});

		window.original_send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html) {

			if (formfield) {

				//clear interval for "Use this Button" so button text resets
				clearInterval(tbframe_interval);

				// itemurl = $(html).attr('href'); // Use the URL to the main image.

				if ( $(html).html(html).find('img').length > 0 ) {

					itemurl = $(html).html(html).find('img').attr('src'); // Use the URL to the size selected.

				} else {

					// It's not an image. Get the URL to the file instead.

					var htmlBits = html.split("'"); 
					itemurl = ''; // Don't Input it

				} // End IF Statement

				var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;

				if (itemurl.match(image)) {
					btnContent = '<img src="'+itemurl+'" alt="" /><a href="#" class="mlu_remove button">Remove Image</a>';
				} else {

					// No output preview if it's not an image.
					btnContent = '';

					// Standard generic output if it's not an image.
					html = '';
					btnContent = '';
				}

				$('#' + formfield).val(itemurl);
				$('#' + formfield).siblings('.screenshot').slideDown().html(btnContent);
				$('#' + formfield).siblings('.of-background-properties').show(); //show background properties
				tb_remove();

			} else {
				window.original_send_to_editor(html);
			}

			formfield = '';
		}

	} // End mediaUpload

	}; // End tmboptions1MLU Object // Don't remove this, or the sky will fall on your head.

/*-----------------------------------------------------------------------------------*/
/* Execute the above methods in the tmboptions1MLU object.
/*-----------------------------------------------------------------------------------*/

	$(document).ready(function () {

		tmboptions1MLU.removeFile();
		tmboptions1MLU.recreateFileField();
		tmboptions1MLU.mediaUpload();

	});

})(jQuery);