<?php
/**
 * Automatic Updater
 * 
 * Enable automatic updates for self hosted plugins
 * 
 * @since 0.1.0
 */

/* API */
function gmr_update_api(){
	$api = esc_url( trailingslashit( 'push.genbu.info' ) );
	return $api;
}

/**
 * Intercept WordPress Updater
 * 
 * @since 0.1.0
 */
function gmr_wp_plugin_updater( $plugin_slug, $checked_data ){

	/* globalize wp_version */
	global $wp_version;

	/* check the data */
	if ( empty( $checked_data->checked ) )
		return $checked_data;

	/* get API data */
	$tmb_api = gmr_update_api();

	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
			'body' => array(
				'action' => 'basic_check', 
				'request' => serialize( $args ),
				'api-key' => md5( get_bloginfo( 'url' ) )
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);

	/* get update data */
	$raw_response = wp_remote_post( $tmb_api, $request_string );

	/* error check */
	if ( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) )
		$response = unserialize( $raw_response['body'] );

	/* feed data to updater */
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;

	/* close sesame */
	return $checked_data;
}

/**
 * Intercept WordPress Updater API
 * 
 * @since 0.1.0
 */
function gmr_plugin_updater_api( $plugin_slug, $res, $action, $args ) {

	/* globalize wp_version */
	global $wp_version;

	/* get API data */
	$tmb_api = gmr_update_api();

	/* only in this plugin */
	if ( $args->slug == $plugin_slug ){

		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
		$args->version = $current_version;

		$request_string = array(
				'body' => array(
					'action' => $action, 
					'request' => serialize( $args ),
					'api-key' => md5( get_bloginfo('url') )
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		$request = wp_remote_post( $tmb_api, $request_string );
		
		if ( is_wp_error( $request ) ) {
			$res = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', 'genbu' ), $request->get_error_message() );
		}
		else {
			$res = unserialize( $request['body'] );

			if ( $res === false )
				$res = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred' ), $request['body']);
		}
	}

	return $res;
}