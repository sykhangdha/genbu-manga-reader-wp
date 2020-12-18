<?php
/**
 * Plugin Name: Genbu Manga Reader WP
 * Plugin URI: skyha.unaux.com
 * Description: a simple manga reader plugin.
 * Version: 0.1.0
 * Author: SkyHa
 * Author URI: skyha.unaux.com
 *
 * With this plugin you can run an online Manga Website.
 *
 * This plugin is inspired by OMV - Online Manga Viewer.
 * by dotspiral http://dotspiral.com/manga-comic-viewer/
 *
 */

/**
 * Includes functions files
 * =====================================
 */
/* html output */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/output.php' );

/* filters manga page title and permalink */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/manga-page.php' );

/* current manga context */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/context.php' );

/* various helper functions */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/utility.php' );

/* manga custom post type, and filters functions */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/cpt.php' );

/* automatic updater */
require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/updater.php' );

/**
 * Load Updater
 * 
 * @since 0.1.0
 */
add_action( 'plugins_loaded', 'genbu_manga_load_updater' );
function genbu_manga_load_updater(){

	/* updater */
	add_filter( 'pre_set_site_transient_update_plugins', 'genbu_manga_wp_updater' );
	add_filter( 'plugins_api_result', 'genbu_manga_updater_api_call', 10, 3 );
}


/**
 * Updater
 * Requires Genbu Theme.
 * 
 * @since 0.2.3
 */
function genbu_manga_wp_updater( $checked_data ){
	$checked_data = gmr_wp_plugin_updater( basename( dirname( __FILE__ ) ), $checked_data );
	return $checked_data;
}

/* Updater API Call */
function genbu_manga_updater_api_call( $res, $action, $args ){
	$res = gmr_plugin_updater_api( basename( dirname( __FILE__ ) ), $res, $action, $args );
	return $res;
}

/**
 * Compats: themes and plugins support
 * =====================================
 */

/* Genbu Theme */
if ( 'genbu' == get_template() ){
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'compat/genbu-theme.php' );
}

/**
 * Plugin Settings Page and Metabox
 * =====================================
 */
add_action('plugins_loaded','genbu_manga_load_options',1);
function genbu_manga_load_options(){

	/* constant paths */
	define( 'GENBU_MANGA_VERSION', '0.1.0' );
	define( 'GENBU_MANGA_DIR', trailingslashit( plugin_dir_path( __FILE__) ) );
	define( 'GENBU_MANGA_URI', trailingslashit( plugins_url( '' , (__FILE__) ) ) );
	define( 'GENBU_MANGA_CSS', trailingslashit( GENBU_MANGA_URI ) . 'css' );
	define( 'GENBU_MANGA_JS', trailingslashit( GENBU_MANGA_URI ) . 'js' );
	define( 'GENBU_MANGA_CSS_DIR', trailingslashit( GENBU_MANGA_DIR ) . 'css' );
	define( 'GENBU_MANGA_JS_DIR', trailingslashit( GENBU_MANGA_DIR ) . 'js' );
	define( 'GENBU_MANGA_IMAGE', trailingslashit( GENBU_MANGA_URI ) . 'images' );

	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'includes/settings.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'admin/options.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'admin/admin.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'admin/sanitize.php' );
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'admin/interface.php' );
}

/**
 * Initiate Metabox Class
 * @since 0.1.0
 */
add_action( 'after_setup_theme', 'genbu_manga_meta_boxes_init',99 );
function genbu_manga_meta_boxes_init() {
	require_once( trailingslashit( plugin_dir_path( __FILE__) ) . 'mb/meta-box.php' );
}


/**
 * Rewrite and Query Variable
 * =====================================
 */
/* add_query var */
add_filter('query_vars','genbu_manga_add_query_variable');

/**
 * Add Query Variable
 * 
 * @link http://codex.wordpress.org/Custom_Queries#Custom_Archives
 * @since 0.1.0
 */
function genbu_manga_add_query_variable( $vars ){
	$vars[] = 'manga_title';
	$vars[] = 'manga_chapter';
	$vars[] = 'manga_page';
	return $vars;
}

/* add rewrite rule */
add_action('init','genbu_manga_rewrite_rule');

/**
 * Add Rewrite Rule
 * For pretty permalink
 * 
 * @since 0.1.0
 */
function genbu_manga_rewrite_rule(){
	global $post;

	if ( gmr_get_option('manga_page') != '' ){

		$pageid = gmr_get_option('manga_page');
		$id = get_post( $pageid );
		$slug = $id->post_name;

		add_rewrite_rule( $slug.'/([^/]+)/?$','index.php?pagename='.$slug.'&manga_title=$matches[1]','top');

		add_rewrite_rule( $slug.'/([^/]+)/([^/]+)/?$','index.php?pagename='.$slug.'&manga_title=$matches[1]&manga_chapter=$matches[2]','top');

		add_rewrite_rule( $slug.'/([^/]+)/([^/]+)/([^/]+)/?$','index.php?pagename='.$slug.'&manga_title=$matches[1]&manga_chapter=$matches[2]&manga_page=$matches[3]','top');
	}
}

/**
 * Scripts and Style
 * =====================================
 */
add_action('plugins_loaded','genbu_manga_script_plugins_loaded');
function genbu_manga_script_plugins_loaded(){

	/* main css */
	add_action( 'wp_enqueue_scripts', 'genbu_manga_plugin_stylesheet' ,7 );

	/* print script in footer */
	add_action('wp_footer','gmr_print_dropdown_script');
	add_action('wp_footer','gmr_print_key_nav_script');

}

/**
 * Register and enqueue style.
 * 
 * @since 0.1.0
 */
function genbu_manga_plugin_stylesheet(){

	/* only in manga page */
	if (is_page(gmr_get_option('manga_page'))){

		/* vars */
		$updated = date( '-Y.m.d-H:i:s', filemtime( plugin_dir_path( __FILE__ ) . 'css/manga.css' ) );
		$css = plugins_url('css/manga.css', __FILE__);

		/* minified version */
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'manga.min.css' ) ){

			$updated = date( '-Y.m.d', filemtime( plugin_dir_path( __FILE__ ) . 'manga.min.css' ) );
			$css = plugins_url('manga.min.css', __FILE__);
		}

		wp_enqueue_style( 'genbu-manga', $css, null ,'genbu-manga.'.GENBU_MANGA_VERSION . $updated );
	}
}

/**
 * Javascript Needed to select manga
 *
 * @since 0.1.0
 */
function gmr_print_dropdown_script(){

	/* in manga reader page */
	$manga_chapter_query = get_query_var('manga_chapter');

	if ( $manga_chapter_query ){

		/* globalize wp_rewrite object */
		global $wp_rewrite;

		/* permalink of the page */
		$permalink = home_url() . '?page_id=' . gmr_get_option('manga_page');

		/* default permalink */
		$manga_title = "&manga_title=";
		$manga_chapter = "&manga_chapter=";
		$manga_page = "&manga_page=";

		/* if using pretty permalink */
		if ( $wp_rewrite->using_permalinks() ){
			$pageid = gmr_get_option('manga_page');
			$id = get_post( $pageid );
			$slug = $id->post_name;
			$permalink = home_url() . '/' . $slug;
			$manga_title = "/";
			$manga_chapter = "/";
			$manga_page = "/";
		}

		/* the script */
		$script = '<script type="text/javascript">
		function change_manga(manga) {
			if (manga != 0) {
				document.location = "' . $permalink . '" + "' . $manga_title . '" + manga;
			}
		}
		function change_chapter(manga, chapter) {
			if (manga != 0) {
				document.location = "' . $permalink . '" + "' . $manga_title . '" + manga + "' . $manga_chapter . '" + chapter + "' . $manga_page . '" + 1;
			}
		}
		function change_page(manga, chapter, page) {
			if (manga != 0) {
				document.location = "' . $permalink . '" + "' . $manga_title . '" + manga + "' . $manga_chapter . '" + chapter + "' . $manga_page . '" + page;
			}
		}
		</script>';


		echo $script;
	}
}

/**
 * Javascript for keyboard navigation
 * 
 * @since 0.1.0
 */
function gmr_print_key_nav_script(){

	/* Chapter Sorting */
	$genbu_manga_chapters_sorting = genbu_manga_chapter_sort_order();

	/* default value */
	$manga = null;
	$manga_escaped = null;
	$chapters = null;
	$chapter = null;
	$chapter_number = null;
	$chapter_number_escaped = null;
	$previous_chapter = null;
	$next_chapter = null;
	$pages = null;
	$page = null;

	/* get mangas */
	$mangas = genbu_manga_get_mangas();

	/* get mangas query variable */
	$manga_title_query = get_query_var('manga_title');
	$manga_chapter_query = get_query_var('manga_chapter');
	$manga_page_query = get_query_var('manga_page');

	/* if manga is in query */
	if ( $manga_title_query ) {

		/* decode the manga_title query var back to original folder */
		$manga_title = genbu_manga_decode( $manga_title_query );

		/* only load if exist. */
		if ( in_array( $manga_title, $mangas ) ) {

			/* current manga title folder */
			$manga = $manga_title;

			/* encoded manga title  */
			$manga_escaped = $manga_title_query;
		}
	}

	/* if manga is found */
	if ( $manga ) {

		/* get the chapters of current mangas */
		$chapters = genbu_manga_get_chapters( $manga );

		/* if manga chapter is selected */
		if ( $manga_chapter_query ) {

			/* decode chapter to original ch. folder, important for non numeric folder */
			$chapter_number = genbu_manga_decode( $manga_chapter_query );

			/* get chapter index, for numeric folder */
			$index = genbu_manga_get_chapter_index( $chapters, $chapter_number );

			/* if it's not negative */
			if ( $index != -1 ) {

				/* chapter is chapter index */
				$chapter = $chapters[$index];

				/* decoded chapter */
				$chapter_number_escaped = $manga_chapter_query;

				/* if sorting is Ascending */
				if ( $genbu_manga_chapters_sorting == SORT_ASC ) {

					/* get previous chapter */
					if ( $index > 0 ) {
						$previous_chapter = $chapters[$index - 1];
					}

					/* get next chapter */
					if ( $index < ( count($chapters) -  1 ) ) {
						$next_chapter = $chapters[$index + 1];
					}
				}
				/* if sorting is descending */
				else {

					/* get previous chapter */
					if ( $index < (count( $chapters ) -  1 ) ) {
						$previous_chapter = $chapters[$index + 1];
					}

					/* get next chapter */
					if ( $index > 0 ) {
						$next_chapter = $chapters[$index - 1];
					}
				}
			}
		}
		/* if no manga chapter is selected, get next and previous chapter */
		else {
			$chapter = $chapters[0];
			$chapter_number = $chapters[0]["number"];
			$chapter_number_escaped = genbu_manga_encode( $chapter_number );

			if ( count( $chapters) > 1 ) {
				if ( $genbu_manga_chapters_sorting == SORT_ASC ) {
					$next_chapter = $chapters[1];
				} else {
					$previous_chapter = $chapters[1];
				}
			}
		}

		/* if chapters found */
		if ( $chapter ) {

			/* get the pages of the current chapter */
			$pages = genbu_manga_get_pages( $manga, $chapter["folder"] );

			/* if manga page is selected */
			if ( $manga_page_query ) {
				$_page = intval( $manga_page_query );
				if ( ( $_page >= 1 ) && ( $_page <= count( $pages ) ) ) {
					$page = $_page;
				}
			} else if ( count($pages) > 0 ) {
				$page = 1;
			}
		}
	}

	/* prev path */
	$prev_page_path = genbu_manga_get_previous_page($manga_escaped, $chapter_number_escaped, $page, $previous_chapter);

	/* next path */
	$next_page_path = genbu_manga_get_next_page($manga_escaped, $chapter_number_escaped, $page, count($pages), $next_chapter);

	/* pretty permalink */
	$prev_page_path = genbu_manga_permalink( $prev_page_path );
	$next_page_path = genbu_manga_permalink( $next_page_path );

	/* the script */
	$script  = '';
	
	/* only load if chapter is requested */
	if ( $manga_title_query ) {
		$script .= '<script type="text/javascript">';
		$script .= 'function omvKeyPressed(e) {
		var keyCode = 0;
		
		if (navigator.appName == "Microsoft Internet Explorer") {
			if (!e) {
				var e = window.event;
			}
			if (e.keyCode) {
				keyCode = e.keyCode;
				if ((keyCode == 37) || (keyCode == 39)) {
					window.event.keyCode = 0;
				}
			} else {
				keyCode = e.which;
			}
		} else {
			if (e.which) {
				keyCode = e.which;
			} else {
				keyCode = e.keyCode;
			}
		}
		
		switch (keyCode) {';
		if ( $prev_page_path ) {
			$script .= '
				case 37:
				window.location = "' . $prev_page_path . '";
				return false;';
		}
		if ( $next_page_path ) {
			$script .= '
				case 39:
				window.location = "' . $next_page_path . '";
				return false;';
		}
		$script .= '
			default:
			return true;
		}
		}
		document.onkeydown = omvKeyPressed;';
		$script .= '</script>';
	}

	/* need to check the page */
	echo $script;
}
