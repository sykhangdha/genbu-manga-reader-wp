<?php
/**
 * Manga Custom Post Type
 * 
 * Register Custom Post Type, Taxonomy, and functions related to CPT.
 * 
 * @since 0.1.0
 * @package GMR
 */

/* add image size */
add_image_size( 'manga-thumb', 250, 9999 );

/* add register post type to init hook */
add_action( 'init', 'gmr_manga_post_type' );

/**
 * Register "Manga" Post Type
 * for child theme listing.
 */
function gmr_manga_post_type() {

	/* post type label */
	$labels = array(
		'name' => _x('Manga', 'post type general name'),
		'singular_name' => _x('Manga', 'post type singular name'),
		'add_new' => _x('Add New', 'and new post type'),
		'add_new_item' => __('Add New Manga'),
		'edit_item' => __('Edit Manga'),
		'new_item' => __('New Manga'),
		'all_items' => __('All Manga'),
		'view_item' => __('View Manga'),
		'search_items' => __('Search Manga'),
		'not_found' =>  __('No Manga found'),
		'not_found_in_trash' => __('No Manga found in Trash'), 
		'parent_item_colon' => '',
		'menu_name' => 'Manga'
	);
 
	/* post type args */
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => array( 'slug' => 'manga-archive', 'with_front' => false ),
		'capability_type' => 'page',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 5,
		'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' )
	);

	/* register */
	register_post_type('gmr_manga',$args);
}


/**
 * Taxonomy
 * ===========================
**/

/* add register taxonomy to init hook */
add_action( 'init', 'genbu_manga_register_taxonomy' );

function genbu_manga_register_taxonomy() {

	/* mangaka */
	$mangaka_labels = array(
		'name' => _x( 'Mangaka', 'taxonomy general name' ),
		'singular_name' => _x( 'Mangaka', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Mangaka' ),
		'all_items' => __( 'All Mangaka' ),
		'parent_item' => __( 'Parent Mangaka' ),
		'parent_item_colon' => __( 'Parent Mangaka:' ),
		'edit_item' => __( 'Edit Mangaka' ), 
		'update_item' => __( 'Update Mangaka' ),
		'add_new_item' => __( 'Add New Mangaka' ),
		'new_item_name' => __( 'New Mangaka Name' ),
		'menu_name' => __( 'Mangaka' ),
	);

	register_taxonomy( 'mangaka', array('gmr_manga'), array(
		'hierarchical' => true,
		'labels' => $mangaka_labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'mangaka' ),
	));

	/* status */
	$status_labels = array(
		'name' => _x( 'Status', 'taxonomy general name' ),
		'singular_name' => _x( 'Status', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Status' ),
		'all_items' => __( 'All Status' ),
		'parent_item' => __( 'Parent Status' ),
		'parent_item_colon' => __( 'Parent Status:' ),
		'edit_item' => __( 'Edit Status' ), 
		'update_item' => __( 'Update Status' ),
		'add_new_item' => __( 'Add New Status' ),
		'new_item_name' => __( 'New Status Name' ),
		'menu_name' => __( 'Status' ),
	);

	register_taxonomy( 'manga_status', array('gmr_manga'), array(
		'hierarchical' => true,
		'labels' => $status_labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'manga-status' ),
	));

	/* genre */
	$genre_labels = array(
		'name' => _x( 'Genre', 'taxonomy general name' ),
		'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Genre' ),
		'all_items' => __( 'All Genre' ),
		'parent_item' => __( 'Parent Genre' ),
		'parent_item_colon' => __( 'Parent Genre:' ),
		'edit_item' => __( 'Edit Genre' ), 
		'update_item' => __( 'Update Genre' ),
		'add_new_item' => __( 'Add New Genre' ),
		'new_item_name' => __( 'New Genre Name' ),
		'menu_name' => __( 'Genre' ),
	);

	register_taxonomy( 'manga_genre', array('gmr_manga'), array(
		'hierarchical' => true,
		'labels' => $genre_labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'manga-genre' ),
	));
}



/**
 * Query The Post
 * This is the template used to display in Manga Title Page
 * ===========================
 * @since 0.1.0
**/
function genbu_manga_cpt_query(){

	$manga_title = get_query_var('manga_title');

	$args = array(
		'post_type' => array('any','gmr_manga'),
		'posts_per_page' => 1,
		'meta_key' => 'manga_folder',
		'meta_value' => genbu_manga_decode($manga_title),
	);

	$the_query = new WP_Query( $args );

	$cpt = '';

	/* start loop */
	while ( $the_query->have_posts() ) :
		$the_query->the_post();

		/* share buttons */
		$share  = genbu_manga_facebook_like();
		$share .= genbu_manga_tweet_button();
		$share .= genbu_manga_google_plus_one();

		/* thumbnail */
		$thumb = '';
		if ( has_post_thumbnail(get_the_ID()) ) {
			$thumb  = '<div class="manga-thumbnail">';
			$thumb .= get_the_post_thumbnail(get_the_ID(), 'manga-thumb');
			$thumb .= '</div>';
		}

		/* info */
		$info  = '<div class="manga-info">';

		/* alt name */
		if ( get_post_meta( get_the_ID(), 'manga_alt_title', true ) )
			$info .= '<p class="manga-alt-name">'.get_post_meta( get_the_ID(), 'manga_alt_title', true ).'</p>';

		/* released date */
		if ( get_post_meta( get_the_ID(), 'manga_released', true ) )
			$info .= '<p class="manga-released"><strong>Released</strong>: '.get_post_meta( get_the_ID(), 'manga_released', true ).'</p>';

		/* mangaka */
		$info .= get_the_term_list( get_the_ID(), 'mangaka', '<p class="manga-author"><strong>Mangaka</strong>: ', ', ', '</p>' );

		/* status */
		$info .= get_the_term_list( get_the_ID(), 'manga_status', '<p class="manga-status"><strong>Status</strong>: ', ', ', '</p>' );

		/* genre */
		$info .= get_the_term_list( get_the_ID(), 'manga_genre', '<p class="manga-genre"><strong>Genre</strong>: ', ', ', '</p>' );

		/* ads */
		$info .= '<div class="manga-ads">' . gmr_get_option('manga_title_cpt_ads') . '</div>';

		$info .= '</div>';

		/* output */
		$cpt = '';
		$edit = '';
		if ( current_user_can('edit_posts') )
			$edit = '<a href="' . get_edit_post_link() .'"> -edit</a>';
		if ( gmr_get_option('manga_title_header') != '1' ){
			$cpt .= '<div class="singular-thumbnail-none" id="singular-header"> ';
			$cpt .= '<h1 class="manga-title page-title entry-title">' . get_the_title() . $edit . '</h1>';
			$cpt .= '</div>';
		}
		else{
			$cpt .= '<h1 class="manga-title">' . get_the_title() . $edit . '</h1>';
		}
		$cpt .= '<div class="manga-reader-share">'.$share.'</div>';
		$cpt .= '<div class="manga-info-wrapper">';
		$cpt .= $thumb;
		$cpt .= $info;

		$cpt .= '</div>';
		$cpt .= '<div class="manga-description">' . wpautop(do_shortcode(get_the_content())) . '</div>';


	/* end loop */
	endwhile;

	/* restore */
	wp_reset_query();
	wp_reset_postdata();

	/* output */
	return $cpt;
}


/**
 * Change Permalink to Manga Title Page if Any.
 * 
 * @since 0.1.0
 */
add_filter('post_type_link','genbu_manga_cpt_permalink');
function genbu_manga_cpt_permalink( $url ){

	global $post;

	if ( is_admin()){
		if( isset( $_GET['post'] ) )
			$post_id = $_GET['post'];
		elseif( isset( $_POST['post_ID'] ) )
			$post_id = $_POST['post_ID'];
		if( !isset( $post_id ) )
			return $url;
	}

	$meta = get_post_meta( $post->ID, 'manga_folder', true );

	if ( $meta ){

		$url  = get_bloginfo( 'url' );
		$url .= '?page_id=' . gmr_get_option('manga_page');
		$url .= '&manga_title=' . genbu_manga_encode( $meta );

		$url  = genbu_manga_permalink( $url );
	}

	return $url;
}


/**
 * Redirect To Manga Title Page
 * 
 * @since 0.1.0
 */
add_action('template_redirect', 'genbu_manga_cpt_redirect');
function genbu_manga_cpt_redirect() {
	global $wp_query, $post;
	if ( is_singular('gmr_manga') ){
		$meta = get_post_meta( $post->ID, 'manga_folder', true );
		if ( $meta ){
			$url  = get_bloginfo( 'url' );
			$url .= '?page_id=' . gmr_get_option('manga_page');
			$url .= '&manga_title=' . genbu_manga_encode( $meta );

			$url  = genbu_manga_permalink( $url );

			wp_redirect( $url, 301 ); 
			exit;
		}
	}
}