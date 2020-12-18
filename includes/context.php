<?php
/**
 * Manga Context
 * 
 * Various helper function to check current context of mangas.
 * this function is meant to use in template or 
 * in some function need to hooked to template_redirect.
 * 
 * @since 0.1.0
 * @package GMR
 */

/* filter body class */
add_filter( 'body_class', 'genbu_manga_body_class' );

/**
 * Filter Body Class
 * 
 * @since 0.1.0
 */
function genbu_manga_body_class( $classes ){

	if ( is_page( gmr_get_option('manga_page') ) ){

		if( get_query_var('manga_chapter') ){
			$classes[] = 'is_manga_reader';
		}
		elseif ( get_query_var('manga_title') ){
			$classes[] = 'is_manga_title';
		}
	}

	return $classes;
}

/**
 * Get Valid Manga
 * 
 * return valid manga folder if queried
 * 
 */
function gmr_valid_title_query(){

	/* data */
	$manga = null;
	$mangas = genbu_manga_get_mangas();
	$manga_title_query = get_query_var('manga_title');

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
	return $manga;
}

/**
 * Get Chapter
 * the number of chapter or folder for bonus chapter
 * 
 * @param	$get	string	various format of chapter
 * @param	$query	bool	set to "true" if need to displayed only on queried chapter
 * @return	mixed	default is array.s
 * @since 0.1.0
 */
function gmr_valid_chapter_query( $get = null, $query = null ){

	/* defaults */
	$chapter = null;
	$chapter_number = null;
	$next_chapter = null;
	$previous_chapter = null;
	$chapter_number_escaped = null;

	/* data */
	$manga = gmr_valid_title_query();
	$chapters = genbu_manga_get_chapters( $manga );
	$manga_chapter_query = get_query_var('manga_chapter');
	$genbu_manga_chapters_sorting = genbu_manga_chapter_sort_order();

	/* if not in manga, return */
	if ( !$manga_chapter_query )
		return;

	/* if manga is valid */
	if ( $manga ) {

		/* if manga chapter is selected */
		if ( $manga_chapter_query ) {

			/* decode chapter to original ch. folder, important for non numeric folder */
			$chapter_number = genbu_manga_decode( $manga_chapter_query );

			for ($i = 0; $i < count( $chapters ); $i++) {
				$cnumber = $chapters[$i]["number"];

				if ( $cnumber == $chapter_number ){

					$chapter_title = $cnumber;
					//$sc_chapter_out = $cnumber;
					//$sc_chapter_slug_out = $cnumber;

					if ( isset($chapters[$i]["title"] ) ){
						$chapter_title = $chapters[$i]["title"];
						//$sc_chapter_out = $cnumber .' - '. $chapters[$i]["title"];
					}
				}

			}//end for

			/* get chapter index, for numeric folder */
			$index = genbu_manga_get_chapter_index( $chapters, $chapter_number );

			/* if it's not negative */
			if ( $index != -1 ) {

				/* chapter is chapter index */
				$chapter = $chapters[$index];

				/* decoded chapter */
				$chapter_number_escaped = genbu_manga_encode( $manga_chapter_query );

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

		/* if no manga chapter is selected, get first chapter */
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
	}

	/* output */
	$output = $chapter;
	if ( $get == 'folder' )
		$output = $chapter["folder"];
	if ( $get == 'title' ){
		if ( isset( $chapter_title ) )
		$output = $chapter_title;
	}
	if ( $get == 'number' )
		$output = $chapter_number;
	if ( $get == 'number_escaped' )
		$output = $chapter_number_escaped;
	if ( $get == 'next' )
		$output = $next_chapter;
	if ( $get == 'previous' )
		$output = $previous_chapter;
	
	/* if set to display only in chapter query */
	if ( $query ){
		if ( !$manga_chapter_query ){
			$output = '';
		}
	}
	return $output;
}


/**
 * Valid Page Query
 * 
 * @since 0.1.0
 */
function gmr_valid_page_query( $page_count = null, $chapter_query = null, $page_query = null ){

	/* defaults */
	$page = null;

	/* data */
	$chapter = gmr_valid_chapter_query('folder');
	$manga = gmr_valid_title_query();
	$manga_page_query = ltrim( get_query_var('manga_page'), '0' );
	$manga_chapter_query = get_query_var('manga_chapter');
	$manga_chapter_query = gmr_valid_chapter_query( 'folder', true );

	/* if not in chapter, return */
	if ( !$manga_chapter_query )
		return $page;

	/* if chapters found */
	if ( $chapter ) {

		/* get the pages of the current chapter */
		$pages = genbu_manga_get_pages( $manga, $chapter );

		/* if need the count */
		if ( isset( $page_count ) )
			return count( $pages );

		/* if manga page is selected */
		if ( $manga_page_query ) {
			$_page = intval( $manga_page_query );
			if ( ( $_page >= 1 ) && ( $_page <= count( $pages ) ) ) {
				$page = $_page;
			}
		}
		/* if pages exist, set to 1 */
		elseif ( count($pages) > 0 ) {
			$page = 1;
		}
	}

	/* if set to display only in chapter query */
	if ( $chapter_query ){
		if ( !$manga_chapter_query ){
			$page = null;
		}
	}
	/* if set to display only in chapter query */
	if ( $page_query ){
		if ( !$manga_page_query ){
			$page = null;
		}
	}
	return $page;
}