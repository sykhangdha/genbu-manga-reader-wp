<?
/**
 * Genbu Theme Compatibility
 * 
 * @since 0.1.0
 */
add_action('after_setup_theme','genbu_manga_filter_genbu_theme',11);

/**
 * Filters
 * 
 * @since 0.1.0
 */
function genbu_manga_filter_genbu_theme(){

	/* breadcrumbs */
	add_filter( 'genbu_breadcrumbs_current', 'genbu_manga_genbu_theme_breadcrumbs' );

	/* byline */
	add_filter( 'genbu_byline', 'genbu_manga_page_disabler');

	/* remove */
	add_action( 'template_redirect', 'genbu_manga_genbu_theme_remove' );
}

/* remove */
function genbu_manga_genbu_theme_remove(){

	if ( is_page( gmr_get_option('manga_page') ) ){


		/* seo stuff */
		remove_action( "genbu_head", 'genbu_seo_meta_description' );

		/* manga reader layout */
		$chapter_var = get_query_var('manga_chapter');
		$title_var = get_query_var('manga_title');
		if ( $chapter_var ){
			add_filter( "genbu_atomic_layout", 'genbu_manga_reader_chapter_layout' );
			if ( gmr_get_option('manga_reader_header') != '1' ){
				add_filter( 'genbu_get_singular_header', '__return_false' );
			}
		}
		elseif ( $title_var ){
			add_filter( "genbu_atomic_layout", 'genbu_manga_reader_title_layout' );
			if ( gmr_get_option('manga_title_header') != '1' ){
				add_filter( 'genbu_get_singular_header', '__return_false' );
			}
		}
	}
}

/**
 * Breadcrumbs
 * 
 * @since 0.1.0
 */
function genbu_manga_genbu_theme_breadcrumbs( $items ){

	$page_id = gmr_get_option('manga_page');
	$page_data = get_post( $page_id );
	$page_title = $page_data->post_title;

	if ( is_page( $page_id ) ){

		/* get query */
		$title_var = get_query_var('manga_title');
		$chapter_var = get_query_var('manga_chapter');
		//$page_var = ltrim( get_query_var('manga_page'), '0' );
		$mangas = genbu_manga_get_mangas();
	
		/* in manga title 'Naruto' */
		if ( $title_var ){

			/* validate title */
			$title_decode = genbu_manga_decode( $title_var ); 
			if ( in_array( $title_decode, $mangas ) ){

				/* manga index page */
				$index_url = home_url() . '?page_id=' . $page_id;
				$index_item = tamatebako_breadcrumb_link( genbu_manga_permalink( $index_url ), $page_title );

				$items  = $index_item;
				$items .= '<span class="breadcrumb-current-item">' . $title_decode . '</span>';

				/* in manga chapter */
				if ( $chapter_var ){

					$chapter = gmr_valid_chapter_query();
					$page = gmr_valid_page_query();

					if ( isset( $chapter['number'] ) ){

						if ( $page ){

							/* manga title title */
							$title_url = $index_url . '&manga_title=' . $title_var;
							$title_item = tamatebako_breadcrumb_link( genbu_manga_permalink( $title_url ), $title_decode );

							$items  = $index_item;
							$items .= $title_item;
							
							/* chapter and page */
							$items .= '<span class="breadcrumb-current-item">' . genbu_manga_decode( $chapter['number'] ) . ' - Page ' . $page .'</span>';
						}
					}
				}
			}
		}
	}
	return $items;
}

/**
 * Manga Title Layouts
 * 
 * @since 0.1.0
 */
function genbu_manga_reader_title_layout( $layout ){
	$layout = gmr_get_option( 'manga_title_layout' );
	return $layout;
}
/**
 * Manga Reader Layouts
 * 
 * @since 0.1.0
 */
function genbu_manga_reader_chapter_layout( $layout ){
	$layout = gmr_get_option( 'manga_reader_layout' );
	return $layout;
}