<?php
/**
 * Manga Page Modification.
 * 
 * Filter Manga Page:
 * - the_title -> Post Title
 * - permalink -> for canonical too.
 * 
 * @since 0.1.0
**/

add_action('init','genbu_manga_filter');

/**
 * Filter The Manga Page
 * 
 * @since 0.1.0
 */
function genbu_manga_filter(){

	/* wp title ( head / seo title ) */
	add_filter( 'wp_title', 'genbu_manga_reader_filter_wp_title', 10, 3 );

	/* page title for heading */
	add_filter( 'the_title', 'genbu_manga_page_filter_title', 10, 2);

	/* permalink, for canonial too */
	add_filter('page_link','genbu_manga_page_filter_permalink' ,10, 2);

	/* remove adjacent link, rel next - previous */
	add_filter( 'next_post_rel_link', 'genbu_manga_page_disabler' );
	add_filter( 'previous_post_rel_link', 'genbu_manga_page_disabler' );
}


/* filter wp title */
function genbu_manga_reader_filter_wp_title( $title, $sep, $seplocation ){

	/* add blog name if theme/plugin use it */
	$blog_name = get_bloginfo( 'name' );

	if ( is_page( gmr_get_option('manga_page') ) ) {

		/* get query */
		$title_var = get_query_var('manga_title');
		$chapter_var = get_query_var('manga_chapter');
		$page_var = ltrim( get_query_var('manga_page'), '0' );
		$mangas = genbu_manga_get_mangas();

		/* in manga title 'Naruto' */
		if ( $title_var ){

			/* validate title */
			$title_decode = genbu_manga_decode( $title_var ); 
			if ( in_array( $title_decode, $mangas ) ){

				/* new title */
				$the_title = genbu_manga_decode( $title_var );

				/* in manga chapter */
				if ( $chapter_var ){
					$chapter = gmr_valid_chapter_query();
					$page = gmr_valid_page_query();

					if ( isset( $chapter['number'] ) ){
						$the_title .= ': ' . genbu_manga_decode( $chapter['number'] );
					}

					if ( $page ){
						$the_title .= ' - ' . $page_var . '/' . gmr_valid_page_query(true);
					}
				}
				$the_sep = ' | '; 
				$new_title = $the_title . $the_sep . $blog_name;
				$title = apply_filters( 'manga_reader_wp_title', $new_title, $the_title, $the_sep, $blog_name );
			}
		}
	}

	return $title;
}



/**
 * Filter Title of Manga Page
 * 
 * @since 0.1.0
 */
function genbu_manga_page_filter_title( $title, $id ){

	/* in manga page */
	if ( is_page( gmr_get_option('manga_page') ) && $id == gmr_get_option('manga_page') ) {

		/* get query */
		$title_var = get_query_var('manga_title');
		$chapter_var = get_query_var('manga_chapter');
		$page_var = ltrim( get_query_var('manga_page'), '0' );
		$mangas = genbu_manga_get_mangas();

		/* in manga title 'Naruto' */
		if ( $title_var ){

			/* validate title */
			$title_decode = genbu_manga_decode( $title_var ); 
			if ( in_array( $title_decode, $mangas ) ){

				/* new title */
				$title = genbu_manga_decode( $title_var );

				/* in manga chapter */
				if ( $chapter_var ){

					$chapter = gmr_valid_chapter_query();
					$page = gmr_valid_page_query();

					if ( isset( $chapter['number'] ) ){
						$title .= ': ' . genbu_manga_decode( $chapter['number'] );
					}
					if ( isset( $chapter['title'] ) ){
						$title .= ' ' . genbu_manga_decode( $chapter['title'] );
					}
					if ( $page ){
						$title .= ' - ' . $page . '/' . gmr_valid_page_query(true);
					}
				}
			}
		}
	}

	return $title;
}


/* permalink */
function genbu_manga_page_filter_permalink( $url, $id ){

	if ( is_page( gmr_get_option('manga_page') ) && $id == gmr_get_option('manga_page') ) {

		/* get query */
		$title_var = get_query_var('manga_title');
		$chapter_var = get_query_var('manga_chapter');
		$page_var = ltrim( get_query_var('manga_page'), '0' );
		$mangas = genbu_manga_get_mangas();

		/* in manga title 'Naruto' */
		if ( $title_var ){

			/* validate title */
			$title_decode = genbu_manga_decode( $title_var ); 
			if ( in_array( $title_decode, $mangas ) ){

				/* new title */
				$url .= '&manga_title=' . $title_var;

				/* in manga chapter */
				if ( $chapter_var ){

					$url .= '&manga_chapter=' . $chapter_var;

					if ( $page_var ){
						$url .= '&manga_page=' . $page_var;
					}
				}
			}
			$url = genbu_manga_permalink( $url );
		}
	}

	return $url;
}


/**
 * Disabler Helper function
 * 
 * @since 0.1.0
 */
function genbu_manga_page_disabler($out){
	if ( is_page( gmr_get_option('manga_page') ) ){
		return '';
	}
	return $out;
}
