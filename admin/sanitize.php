<?php
/**
 * Theme Options Data Sanitization
 */

/* Kill the page if trying to access this template directly. */
if ( !function_exists( 'add_action' ) ) {
	echo "This Is Not The Page You are Looking For!  Move Along... Move Along...";
	exit;
}

/* Filters */
add_action('after_setup_theme','genbu_manga_options_sanitize',8);

function genbu_manga_options_sanitize(){
	add_filter( 'gmr_sanitize_text', 'sanitize_text_field' );
	add_filter( 'gmr_sanitize_textemail', 'gmr_sanitize_textemail' );
	add_filter( 'gmr_sanitize_texturl', 'gmr_sanitize_texturl' );
	add_filter( 'gmr_sanitize_textnumeric', 'gmr_sanitize_textnumeric' );
	add_filter( 'gmr_sanitize_textmultinumeric', 'gmr_sanitize_textmultinumeric' );
	add_filter( 'gmr_sanitize_textarea', 'gmr_sanitize_textarea' );
	add_filter( 'gmr_sanitize_textareascript', 'gmr_sanitize_textareascript' );
	add_filter( 'gmr_sanitize_select', 'gmr_sanitize_enum', 10, 2);
	add_filter( 'gmr_sanitize_radio', 'gmr_sanitize_enum', 10, 2);
	add_filter( 'gmr_sanitize_images', 'gmr_sanitize_enum', 10, 2);
	add_filter( 'gmr_sanitize_checkbox', 'gmr_sanitize_checkbox' );
	add_filter( 'gmr_sanitize_multicheck', 'gmr_sanitize_multicheck', 10, 2 );
	add_filter( 'gmr_sanitize_color', 'gmr_sanitize_hex' );
	add_filter( 'gmr_sanitize_upload', 'gmr_sanitize_upload' );
	add_filter( 'gmr_sanitize_editor', 'gmr_sanitize_editor' );
	add_filter( 'gmr_sanitize_info', 'gmr_sanitize_allowedposttags_nop' );
	add_filter( 'gmr_sanitize_background', 'gmr_sanitize_background' );
	add_filter( 'gmr_background_repeat', 'gmr_sanitize_background_repeat' );
	add_filter( 'gmr_background_position', 'gmr_sanitize_background_position' );
	add_filter( 'gmr_background_attachment', 'gmr_sanitize_background_attachment' );
	add_filter( 'gmr_sanitize_typography', 'gmr_sanitize_typography', 10, 2 );
	add_filter( 'gmr_font_size', 'gmr_sanitize_font_size' );
	add_filter( 'gmr_font_style', 'gmr_sanitize_font_style' );
	add_filter( 'gmr_font_face', 'gmr_sanitize_font_face' );
}

/* Text Email */
function gmr_sanitize_textemail( $input ){
	$input = trim( $input ); // trim whitespace
	if ( !is_email( $input ) ) $output = '';
	else $output = $input;
	return $output;
}

/* Text URL */
function gmr_sanitize_texturl( $input ){
	$input = trim( $input ); // trim whitespace
	$output = esc_url_raw( $input );
	return $output;
}

/* Text Numeric */
function gmr_sanitize_textnumeric( $input ){

	$input = trim( $input ); // trim whitespace
	if ( !is_numeric( $input ) ) $output = '0';
	else $output = $input;
	return $output;
}

/* Text Multi Numeric */
function gmr_sanitize_textmultinumeric( $input, $option ){
	$option = array();
	$input = trim( $input ); // trim whitespace
	// /^-?\d+(?:,\s?-?\d+)*$/ matches: -1 | 1 | -12,-23 | 12,23 | -123, -234 | 123, 234  | etc.
	$output = ( preg_match( '/^-?\d+(?:,\s?-?\d+)*$/' , $input ) == 1 ) ? $input : $option['std'] ;
	return $output;
}

/* Textarea */
function gmr_sanitize_textarea($input) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags);
	return $output;
}

/* Description */
function gmr_sanitize_desc($input) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags);
	return apply_filters( 'gmr_sanitize_desc', $output );
	return $output;
}

/* Textarea Script */
function gmr_sanitize_textareascript($input) {
	global $allowedposttags;
	$script_tags = $allowedposttags; //duplicate $allowed tags
	$script_tags["iframe"] = array( //add new allowed tags
		"src" => array(),
		"height" => array(),
		"width" => array(),
		"frameborder" => array(),
		"allowfullscreen" => array()
	);
	$script_tags["object"] = array(
		"height" => array(),
		"width" => array()
	);
	$script_tags["param"] = array(
		"name" => array(),
		"value" => array()
	);
	$script_tags["script"] = array(
		"src" => array(),
		"type" => array()
	);
	$script_tags["embed"] = array(
		"src" => array(),
		"type" => array(),
		"allowfullscreen" => array(),
		"allowscriptaccess" => array(),
		"height" => array(),
		"width" => array()
	);
	$output = wp_kses( $input, $script_tags);
	return $output;
}

/* Checkbox */
function gmr_sanitize_checkbox( $input ) {
	if ( $input ) $output = '1';
	else $output = '0';
	return $output;
}

/* Multicheck */
function gmr_sanitize_multicheck( $input, $option ) {
	$output = '';
	if ( is_array( $input ) ) {
		foreach( $option['options'] as $key => $value ) {
			$output[$key] = "0";
		}
		foreach( $input as $key => $value ) {
			if ( array_key_exists( $key, $option['options'] ) && $value ) {
				$output[$key] = "1";
			}
		}
	}
	return $output;
}

/* Uploader */
function gmr_sanitize_upload( $input ) {
	$output = '';
	$filetype = wp_check_filetype($input);
	if ( $filetype["ext"] ) $output = $input;
	$output = esc_url_raw( $input );
	return $output;
}

/* Editor */
function gmr_sanitize_editor($input) {
	global $allowedtags;
	if ( current_user_can( 'unfiltered_html' ) ) $output = stripslashes( wp_filter_post_kses( addslashes($input) ) );
	else $output = wp_kses( $input, $allowedtags);
	return $output;
}

/* Allowed Tags */
function gmr_sanitize_allowedtags($input) {
	global $allowedtags;
	$output = wpautop(wp_kses( $input, $allowedtags));
	return $output;
}

/* Allowed Post Tags */
function gmr_sanitize_allowedposttags($input) {
	global $allowedposttags;
	$output = wpautop(wp_kses( $input, $allowedposttags));
	return $output;
}

/* Allowed Post Tags no auto P */
function gmr_sanitize_allowedposttags_nop($input) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags);
	return $output;
}

/* Check that the key value sent is valid */
function gmr_sanitize_enum( $input, $option ) {
	$output = '';
	if ( array_key_exists( $input, $option['options'] ) ) $output = $input;
	return $output;
}

/* Background */
function gmr_sanitize_background( $input ) {
	$output = wp_parse_args( $input, array(
		'color' => '',
		'image'  => '',
		'repeat'  => 'repeat',
		'position' => 'top center',
		'attachment' => 'scroll'
	) );
	$output['color'] = apply_filters( 'gmr_sanitize_hex', $input['color'] );
	$output['image'] = apply_filters( 'gmr_sanitize_upload', $input['image'] );
	$output['repeat'] = apply_filters( 'gmr_background_repeat', $input['repeat'] );
	$output['position'] = apply_filters( 'gmr_background_position', $input['position'] );
	$output['attachment'] = apply_filters( 'gmr_background_attachment', $input['attachment'] );
	return $output;
}

/* Background Repeat */
function gmr_sanitize_background_repeat( $value ) {
	$recognized = gmr_recognized_background_repeat();
	if ( array_key_exists( $value, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_background_repeat', current( $recognized ) );
}

/* Background Position */
function gmr_sanitize_background_position( $value ) {
	$recognized = gmr_recognized_background_position();
	if ( array_key_exists( $value, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_background_position', current( $recognized ) );
}

/* Background Attachment */
function gmr_sanitize_background_attachment( $value ) {
	$recognized = gmr_recognized_background_attachment();
	if ( array_key_exists( $value, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_background_attachment', current( $recognized ) );
}

/* Typography */
function gmr_sanitize_typography( $input, $option ) {
	$output = wp_parse_args( $input, array(
		'size'  => '',
		'face'  => '',
		'style' => '',
		'color' => ''
	) );
	if ( isset( $option['options']['faces'] ) && isset( $input['face'] ) ) {
		if ( !( array_key_exists( $input['face'], $option['options']['faces'] ) ) ) $output['face'] = '';
	}
	else {
		$output['face']  = apply_filters( 'gmr_font_face', $output['face'] );
	}
	$output['size']  = apply_filters( 'gmr_font_size', $output['size'] );
	$output['style'] = apply_filters( 'gmr_font_style', $output['style'] );
	$output['color'] = apply_filters( 'gmr_color', $output['color'] );
	return $output;
}

/* Typography: Font Size */
function gmr_sanitize_font_size( $value ) {
	$recognized = gmr_recognized_font_sizes();
	$value_check = preg_replace('/px/','', $value);
	if ( in_array( (int) $value_check, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_font_size', $recognized );
}

/* Typography: Font Style */
function gmr_sanitize_font_style( $value ) {
	$recognized = gmr_recognized_font_styles();
	if ( array_key_exists( $value, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_font_style', current( $recognized ) );
}

/* Typography: Font Face */
function gmr_sanitize_font_face( $value ) {
	$recognized = gmr_recognized_font_faces();
	if ( array_key_exists( $value, $recognized ) ) return $value;
	return apply_filters( 'gmr_default_font_face', current( $recognized ) );
}


/**
 * Get recognized background repeat settings
 * @return   array
 */
function gmr_recognized_background_repeat() {
	$default = array(
		'no-repeat' => 'No Repeat',
		'repeat-x'  => 'Repeat Horizontally',
		'repeat-y'  => 'Repeat Vertically',
		'repeat'    => 'Repeat All',
		);
	return apply_filters( 'gmr_recognized_background_repeat', $default );
}

/**
 * Get recognized background positions
 * @return   array
 */
function gmr_recognized_background_position() {
	$default = array(
		'top left'      => 'Top Left',
		'top center'    => 'Top Center',
		'top right'     => 'Top Right',
		'center left'   => 'Middle Left',
		'center center' => 'Middle Center',
		'center right'  => 'Middle Right',
		'bottom left'   => 'Bottom Left',
		'bottom center' => 'Bottom Center',
		'bottom right'  => 'Bottom Right'
		);
	return apply_filters( 'gmr_recognized_background_position', $default );
}

/**
 * Get recognized background attachment
 * @return   array
 */
function gmr_recognized_background_attachment() {
	$default = array(
		'scroll' => 'Scroll Normally',
		'fixed'  => 'Fixed in Place'
		);
	return apply_filters( 'gmr_recognized_background_attachment', $default );
}

/**
 * Sanitize a color represented in hexidecimal notation.
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @param    string    The value that this function should return if it cannot be recognized as a color.
 * @return   string
 *
 */
function gmr_sanitize_hex( $hex, $default = '' ) {
	if ( gmr_validate_hex( $hex ) ) {
		return $hex;
	}
	return $default;
}

/**
 * Get recognized font sizes.
 *
 * Returns an indexed array of all recognized font sizes.
 * Values are integers and represent a range of sizes from
 * smallest to largest.
 *
 * @return   array
 */
function gmr_recognized_font_sizes() {
	$sizes = range( 9, 71 );
	$sizes = apply_filters( 'gmr_recognized_font_sizes', $sizes );
	$sizes = array_map( 'absint', $sizes );
	return $sizes;
}

/**
 * Get recognized font faces.
 *
 * Returns an array of all recognized font faces.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 *
 */
function gmr_recognized_font_faces() {
	$default = array(
		'arial'     => 'Arial',
		'verdana'   => 'Verdana, Geneva',
		'trebuchet' => 'Trebuchet',
		'georgia'   => 'Georgia',
		'times'     => 'Times New Roman',
		'tahoma'    => 'Tahoma, Geneva',
		'palatino'  => 'Palatino',
		'helvetica' => 'Helvetica*'
		);
	return apply_filters( 'gmr_recognized_font_faces', $default );
}

/**
 * Get recognized font styles.
 *
 * Returns an array of all recognized font styles.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 *
 */
function gmr_recognized_font_styles() {
	$default = array(
		'normal'      => 'Normal',
		'italic'      => 'Italic',
		'bold'        => 'Bold',
		'bold italic' => 'Bold Italic'
		);
	return apply_filters( 'gmr_recognized_font_styles', $default );
}

/**
 * Is a given string a color formatted in hexidecimal notation?
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @return   bool
 *
 */

function gmr_validate_hex( $hex ) {
	$hex = trim( $hex );
	/* Strip recognized prefixes. */
	if ( 0 === strpos( $hex, '#' ) ) {
		$hex = substr( $hex, 1 );
	}
	elseif ( 0 === strpos( $hex, '%23' ) ) {
		$hex = substr( $hex, 3 );
	}
	/* Regex match. */
	if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return false;
	}
	else {
		return true;
	}
}