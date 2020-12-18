<?php
/**
 * Settings Page For Genbu Manga Plugin
 * 
 * @since 0.1.0
 */


/* Hook to theme setup */
add_action( 'plugins_loaded', 'genbu_manga_plugin_options_setup' );

/**
 * Theme Options Setup functions
 * 
 * @since 2.1.2
 */
function genbu_manga_plugin_options_setup(){

	/* options */
	add_filter( 'gmr_options', 'genbu_manga_the_options' );
}

function genbu_manga_the_options( $options ){

	/* Select Page */
	$options_pages = array();
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages['0'] = 'Select page';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}
	/* heading */
	$heading = array(	'h1' => 'h1',
						'h2' => 'h2',
						'h3' => 'h3',
						'h4' => 'h4',
						'h5' => 'h5',
						'h6' => 'h6',
						'div' => 'div'
					);
	/**
	 * General
	 =============================================================================*/
	$options['general_tab'] = array( "name" => _x( 'General', 'settings', 'genbu-manga-reader' ),
						"type" => "heading");

	/* Manga Page */
	$options['manga_page'] = array( 
						"name" => "Select Manga Page",
						"desc" => _x('Select page to display manga.', 'settings', 'genbu-manga-reader'),
						"id" => "manga_page",
						"std" => '',
						"class" => "small",
						"type" => "select",
						"options" => $options_pages );

	/* upload folder */
	$options['upload_folder'] = array( 
						"name" => _x( 'Manga upload folder', 'settings', 'genbu-manga-reader' ),
						"desc" => _x( 'the upload folder in "uploads" directory', 'settings', 'genbu-manga-reader' ),
						"id" => "upload_folder",
						"class" => "small",
						"std" => "mangas",
						"type" => "text");


	/**
	 * Manga Index
	 =============================================================================*/
	$options['manga_index_tab'] = array( "name" => _x( 'Manga Index', 'settings', 'genbu-manga-reader' ),
						"type" => "heading");

	/* Top Ads */
	$options['manga_index_top_ads'] = array( 
						"name" => _x('Top Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads before manga index table, also will be displayed in manga start page.', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_index_top_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/* Bottom Ads */
	$options['manga_index_bottom_ads'] = array( 
						"name" => _x('Bottom Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads after manga index table', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_index_bottom_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/**
	 * Manga Title
	 =============================================================================*/
	$options['manga_title_tab'] = array( "name" => _x( 'Manga Title', 'settings', 'genbu-manga-reader' ),
						"type" => "heading");

	/* Manga CPT ads */
	$options['manga_title_cpt_ads'] = array( 
						"name" => _x('CPT Ads, after manga info', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads after manga info in Custom Post Type Query', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_title_cpt_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/* Top Ads */
	$options['manga_title_top_ads'] = array( 
						"name" => _x('Top Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads before manga chapter index table, also will be displayed in manga start page.', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_title_top_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/* Bottom Ads */
	$options['manga_title_bottom_ads'] = array( 
						"name" => _x('Bottom Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads after manga chapter index table', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_title_bottom_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/**
	 * Manga Reader
	 =============================================================================*/
	$options['manga_reader_tab'] = array( "name" => _x( 'Manga Reader', 'settings', 'genbu-manga-reader' ),
						"type" => "heading");

	/* Top Ads */
	$options['manga_reader_top_ads'] = array( 
						"name" => _x('Top Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads before manga image, also will be displayed in manga start page.', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_reader_top_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/* Bottom Ads */
	$options['manga_reader_bottom_ads'] = array( 
						"name" => _x('Bottom Ads', 'settings', 'genbu-manga-reader'),
						"desc" => _x( 'Ads after manga image', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_reader_bottom_ads",
						"std" => "",
						"settings" => array(
							'rows' => 5 
						),
						"type" => "textareascript");

	/**
	 * Genbu Theme
	 =============================================================================*/
	$options['genbu_theme_tab'] = array( "name" => _x( 'Genbu Theme', 'settings', 'genbu-manga-reader' ),
						"type" => "heading");

	/* image path */
	$imagepath = trailingslashit( GENBU_MANGA_IMAGE );

	/* Theme Layout */
	$layout = array(	'layout-3c-left' => $imagepath . 'layout-3c-left.png',
						'layout-3c-right' => $imagepath . 'layout-3c-right.png',
						'layout-3c-center' => $imagepath . 'layout-3c-center.png',
						'layout-1c-nosidebar' => $imagepath . 'layout-1c-nosidebar.png',
						'layout-1c-fullwidth' => $imagepath . 'layout-1c-fullwidth.png',
						'layout-3c-center-alt-right' => $imagepath . 'layout-3c-center-alt-right.png',
						'layout-3c-center-alt-left' => $imagepath . 'layout-3c-center-alt-left.png',
						'layout-3c-left-alt-right' => $imagepath . 'layout-3c-left-alt-right.png',
						'layout-3c-left-alt-center' => $imagepath . 'layout-3c-left-alt-center.png',
						'layout-3c-right-alt-left' => $imagepath . 'layout-3c-right-alt-left.png',
						'layout-3c-right-alt-center' => $imagepath . 'layout-3c-right-alt-center.png'
					);

	/* Manga Title Layout */
	$options['manga_title_layout'] = array( "name" => _x( 'Manga Title Layout', 'settings', 'genbu-manga-reader' ),
						"desc" => 'Layout for Manga Title Page',
						"id" => "manga_title_layout",
						"std" => "layout-3c-left",
						"type" => "images",
						"options" => $layout
						);

	/* Singular Header */
	$options['manga_title_header'] = array( "desc" => _x( 'Manga Title Page Header', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_title_header",
						"std" => "1",
						"type" => "checkbox");

	/* Manga Reader Layout */
	$options['manga_reader_layout'] = array( "name" => _x( 'Manga Reader Layout', 'settings', 'genbu-manga-reader' ),
						"desc" => 'Layout for Manga Reader Page',
						"id" => "manga_reader_layout",
						"std" => "layout-1c-fullwidth",
						"type" => "images",
						"options" => $layout
						);

	/* Singular Header */
	$options['manga_reader_header'] = array( "desc" => _x( 'Manga Reader Page Header', 'settings', 'genbu-manga-reader' ),
						"id" => "manga_reader_header",
						"std" => "1",
						"type" => "checkbox");

	/* Allow to add options */
	return apply_filters( 'genbu_manga_settings', $options );

}


/**
 * MetaBox
 * 
 * @since 0.1.0
 */
/* filter to create metabox */
add_filter( 'tmb_meta_boxes', 'genbu_manga_metabox' );

/**
 * Rich Snippet Rating Review Metabox
 * Input visible in Custom Field metabox.
 *
 * @since 0.1.0
 */
function genbu_manga_metabox( $meta_boxes ){

	/* mangas folder */
	$mangas = genbu_manga_get_mangas();

	/* options */
	$options = array();
	foreach ($mangas as $manga){
		$option = array();
		$option['name'] = $manga;
		$option['value'] = $manga;
		$options[0] = array( 'name' => 'None', 'value' => '', );
		$options[] = $option;
	}

	/* the metabox */
	$meta_boxes[] = array(
		'id'         => 'genbu_manga_folder',
		'title'      => 'Manga Info',
		'pages'      => array('gmr_manga'),
		'context'    => 'normal',
		'priority'   => 'low',
		'show_names' => true,
		'fields'     => array(
			array(
				'name'		=> 'Manga Folder',
				'id'		=> 'manga_folder',
				'type'		=> 'select',
				'desc'		=> 'Select Manga Folder to display this content in manga title page',
				'options' 	=> $options,
			),
			array(
				'name'	=> 'Released',
				'id'	=> 'manga_released',
				'type'	=> 'text_small',
				'desc'	=> 'Year of manga released.',
			),
			array(
				'name'	=> 'Alternative Name',
				'id'	=> 'manga_alt_title',
				'type'	=> 'text',
				'desc'	=> 'Alternative title for the manga.',
			),
		),
	);

	/* return the metabox */
	return $meta_boxes;
}


/* add flush and rewrite rule when saving or reseting theme options */
add_action('gmr_admin_save','genbu_manga_flush_when_save');
add_action('gmr_admin_reset','genbu_manga_flush_when_save');
function genbu_manga_flush_when_save(){
	global $wp_rewrite;
	genbu_manga_rewrite_rule();
	wp_cache_flush();
	$wp_rewrite->flush_rules();
}
