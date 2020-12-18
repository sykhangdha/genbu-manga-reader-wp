<?php
/**
 * Tamatebako Theme Options
 * 
 * Helper Class to easily create Advance Theme Options
 * Based on Option Framework by Devin Prince.
 *
 * @version 0.2.0 
 */

/* Kill the page if trying to access this template directly. */
if ( !function_exists( 'add_action' ) ) {
	echo "This Is Not The Page You are Looking For!  Move Along... Move Along...";
	exit;
}


/**
 * Primary Theme Options
 *
 * All primary theme options frontend functions
 *
 *
 * @since 0.2
 */

/* Generate Options - Theme Options Primary */
function gmroptions_options_create() {
	static $options = null;

	if (!$options)
		$options = array();

	/* set defaults - empty */
	$defaults = array();

	/* Allow plugins/themes to filter the arguments. */
	$options = apply_filters( 'gmr_options', $options );

	/* Merge the input arguments and the defaults. */
	$options = wp_parse_args( $options, $defaults );

	return $options;
}

/* Get Option - Theme Options Primary Helper function */
function gmr_get_option( $name, $default = false ) {
	$option_name = gmr_option_name();


	if ( get_option($option_name) ) {
		$options = get_option( $option_name );

		if ( isset( $options[$name] ) && ! isset( $option['id'] ) && ! isset( $option['std'] ) && ! isset( $option['type'] )){
			return $options[$name];
		}

		else {
			$options = gmroptions_options_create();
			foreach ( (array) $options as $option ) {
				if ( ! isset( $option['id'] ) ) continue;
				if ( ! isset( $option['std'] ) ) continue;
				if ( ! isset( $option['type'] ) ) continue;
				if ( $name == $option['id'] && has_filter( 'gmr_sanitize_' . $option['type'] ) )
					$options = apply_filters( 'gmr_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
	}

	else {

		$options = gmroptions_options_create();
		foreach ( (array) $options as $option ) {
			if ( ! isset( $option['id'] ) ) continue;
			if ( ! isset( $option['std'] ) ) continue;
			if ( ! isset( $option['type'] ) ) continue;
			if ( $name == $option['id'] && has_filter( 'gmr_sanitize_' . $option['type'] ) )
				$options = apply_filters( 'gmr_sanitize_' . $option['type'], $option['std'], $option );
		}

	}
	return $options;
}

/**
 * Set Primary Option Database name
 * Do Not Change This, Your site will explode!
 */
function gmr_option_name(){

	return apply_filters( 'gmr_option_name', 'gmr_dbname' );
}

