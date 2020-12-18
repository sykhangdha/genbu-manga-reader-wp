<?php
/*
 * Tamatebako Theme Options - Primary
 * Primary Options
 *
 */

/* Kill the page if trying to access this template directly. */
if ( !function_exists( 'add_action' ) ) {
	echo "This Is Not The Page You are Looking For!  Move Along... Move Along...";
	exit;
}


/* Theme Options Setup */
add_action( 'admin_menu', 'genbu_manga_options_setup' );
function genbu_manga_options_setup(){

	/* Register Style */
	add_action( 'admin_enqueue_scripts', 'genbu_manga_options_register_style',1 );

	/* Register Script */
	add_action( 'admin_enqueue_scripts', 'tgenbu_manga_options_register_script',1 );
}

/* Register Style */
function genbu_manga_options_register_style(){

	/* options css */
	$css_updated = date( '-Y.m.d', filemtime( GENBU_MANGA_CSS_DIR . '/tmboptions.css' ) );
	wp_register_style('gmroptions', trailingslashit( GENBU_MANGA_CSS ) .'tmboptions.css', array(), 'gmr-op-'. GENBU_MANGA_VERSION . $css_updated );

	/* color picker css */
	$color_updated = date( '-Y.m.d', filemtime( GENBU_MANGA_CSS_DIR . '/colorpicker.css' ) );
	wp_register_style('gmroptions-color-picker', trailingslashit( GENBU_MANGA_CSS ) .'colorpicker.css', array(), 'gmr-op-'. GENBU_MANGA_VERSION . $color_updated );
}

/* Register Script */
function tgenbu_manga_options_register_script(){

	/* color picker script */
	$color_updated = date( '-Y.m.d', filemtime( GENBU_MANGA_JS_DIR . '/colorpicker.js' ) );
	wp_register_script('gmroptions-color-picker', trailingslashit( GENBU_MANGA_JS ) .'colorpicker.js', array('jquery'), 'gmr-op-'. GENBU_MANGA_VERSION . $color_updated );

	/* media upload script */
	$uploader_updated = date( '-Y.m.d', filemtime( GENBU_MANGA_JS_DIR . '/media-uploader.js' ) );
	wp_register_script( 'gmr-medialibrary-uploader', trailingslashit( GENBU_MANGA_JS ) .'media-uploader.js', array( 'jquery', 'thickbox' ), 'gmr-op-'. GENBU_MANGA_VERSION . $uploader_updated );

	/* custom script */
	$custom_updated = date( '-Y.m.d', filemtime( GENBU_MANGA_JS_DIR . '/options-custom.js' ) );
	wp_register_script( 'gmroptions-options-custom', trailingslashit( GENBU_MANGA_JS ) .'options-custom.js', array('jquery'), 'gmr-op-'. GENBU_MANGA_VERSION . $custom_updated );
}

/* Loads the javascript */
function gmroptions_load_scripts () {
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'gmroptions-color-picker' );
	wp_enqueue_script( 'gmroptions-options-custom' );
}

/* Loads the CSS */
function gmroptions_load_styles() {
	wp_enqueue_style( 'gmroptions' );
	wp_enqueue_style( 'gmroptions-color-picker' );
}

/* Helper Functions: Double Role Check */
function gmroptions_page_capability( $capability ) {
	return 'manage_options';
}

/**
 * Check user role before.
 */
add_action('init', 'gmroptions_role_check' );
function gmroptions_role_check(){
	if ( current_user_can( 'manage_options' ) ) {
		add_action( 'admin_init', 'gmroptions_init' );
		add_action( 'admin_menu', 'gmroptions_add_page');
	}
}

/**
 * Creates the settings in the database by looping through the array
 */
function gmroptions_init() {

	/* Settings ID in Database */
	$option_name = gmr_option_name();

	/* Load default value if there's no data. */
	if ( ! get_option( $option_name ) ) {
		gmroptions_setdefaults();
	}

	/* Registers the settings fields and callback */
	register_setting( 'gmroptions', $option_name, 'gmroptions_validate' );

	/* Change the capability required to save the 'gmroptions' options group. */
	add_filter( 'option_page_capability_gmroptions', 'gmroptions_page_capability' );
}

/**
 * Adds default options to the database if they aren't already present.
 */
function gmroptions_setdefaults() {

	/* Settings ID in Database */
	$option_name = gmr_option_name();

	/* Get default value */
	$values = gmr_get_default_values();

	/* If the options haven't been added to the database add it with default */
	if ( isset( $values ) ) {
		add_option( $option_name, $values );
	}

}

/**
 * Add a subpage to the appearance menu.
 */
function gmroptions_add_page() {

	$menu_title = apply_filters( 'gmr_menu_title', 'Genbu Manga Reader' );

	/* Add the theme options page */
	$gmroptions_page = add_options_page( $menu_title, $menu_title, 'manage_options', 'gmr-options','gmroptions_page');

	/* Load the required CSS and javascript */
	add_action( 'admin_print_scripts-settings_page_gmr-options', 'gmroptions_load_scripts' );
	add_action( 'admin_print_styles-settings_page_gmr-options', 'gmroptions_load_styles' );

	/* Inline scripts from interface.php */
	add_action('admin_head', 'gmroptions_admin_head');

	add_action( 'admin_print_styles-settings_page_gmr-options', 'gmroptions_mlu_css', 0 );
	add_action( 'admin_print_scripts-settings_page_gmr-options', 'gmroptions_mlu_js', 0 );


}
/* Add action hook to admin settings page to add custom scripts */
function gmroptions_admin_head() {
	do_action( 'gmroptions_custom_scripts' );
}

/**
 * Builds out the options panel.
 */
function gmroptions_page() {

	/* Settings Page Title */
	$icon = get_screen_icon('options-general');
	$page_title  = $icon;
	$page_title .= '<h2>Genbu Manga Reader</h2>';

	/* Notice and Text Filters */
	$save = 'Save Options';
	$restore = 'Restore Defaults';
	$restore_notice = 'Click OK to reset. Any theme settings will be lost!';
	?>
	<?php
	// action hook when saving and reseting theme options
	if ( isset ( $_POST['reset'] ) ): 
		do_action('gmr_admin_reset');
	elseif ( isset( $_GET['settings-updated'] ) ):
		do_action('gmr_admin_save');
	endif; 
	?>
	<div class="wrap">
	<?php echo $page_title; ?>
    <?php //settings_errors(); ?>
	<h3 class="nav-tab-wrapper"><?php echo gmroptions_page_tabs(); ?></h3>
		<div class="metabox-holder">
			<div id="tmboptions" class="postbox">
				<form action="options.php" method="post">
					<?php settings_fields('gmroptions'); ?>
					<?php gmroptions_page_fields(); /* Settings */ ?>
					<div id="tmboptions-submit">
						<input type="submit" class="button-primary" name="update" value="<?php echo esc_attr( $save ); ?>" />
						<input type="submit" class="reset-button button-secondary" name="reset" value="<?php echo esc_attr( $restore ); ?>" onclick="return confirm( '<?php print esc_js( $restore_notice ); ?>' );" />
						<div class="clear"></div>
					</div>
				</form>
			</div> <!-- #gmroptions  -->
		</div><!-- .metabox-holder -->
	</div> <!-- .wrap -->
<?php
}

/** 
 * Validate Options.
 */
function gmroptions_validate( $input ) {

	/* Restore Defaults when user clicked "Restore Defaults" button. */
	if ( isset( $_POST['reset'] ) ) {
		$restore_notice = 'Default options restored.';
		add_settings_error( 'gmr-options', 'restore_defaults', $restore_notice, 'error fade' );
		return gmr_get_default_values();
	}
	/*
	 * Update Settings
	 *
	 * This used to check for $_POST['update'], but has been updated
	 * to be compatible with the theme customizer introduced in WordPress 3.4
	 */
	$clean = array();
	$options = gmroptions_options_create();
	foreach ( $options as $option ) {

		if ( ! isset( $option['id'] ) ) {
			continue;
		}

		if ( ! isset( $option['type'] ) ) {
			continue;
		}

		$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

		// Set checkbox to false if it wasn't sent in the $_POST
		if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
			$input[$id] = false;
		}

		// Set each item in the multicheck to false if it wasn't sent in the $_POST
		if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
			foreach ( $option['options'] as $key => $value ) {
				$input[$id][$key] = false;
			}
		}

		// For a value to be submitted to database it must pass through a sanitization filter
		if ( has_filter( 'gmr_sanitize_' . $option['type'] ) ) {
			$clean[$id] = apply_filters( 'gmr_sanitize_' . $option['type'], $input[$id], $option );
		}
	}

	$save_notice = 'Options saved.';
	add_settings_error( 'gmr-options', 'save_options', $save_notice, 'updated fade' );

	return $clean;
}

/**
 * Get Default Value and Format Configuration Array.
 */
function gmr_get_default_values() {
	$output = array();
	$config = gmroptions_options_create();
	foreach ( (array) $config as $option ) {
		if ( ! isset( $option['id'] ) ) continue;
		if ( ! isset( $option['std'] ) ) continue;
		if ( ! isset( $option['type'] ) ) continue;
		if ( has_filter( 'gmr_sanitize_' . $option['type'] ) )
			$output[$option['id']] = apply_filters( 'gmr_sanitize_' . $option['type'], $option['std'], $option );
	}
	return $output;
}



/**
 * Generates the tabs that are used in the options menu
 */
function gmroptions_page_tabs() {
	return gmroptions_tabs( gmroptions_options_create() );
}

/**
 * Generates the options fields that are used in the form.
 */
function gmroptions_page_fields() {
	return gmroptions_fields( gmr_option_name(), gmroptions_options_create() );
}