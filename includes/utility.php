<?php
/**
 * Helper Functions
 * 
 * @since	0.1.0
 * @package	GMR
 */

/**
 * Chapter Sort Order
 * 
 * @since 0.1.0
 */
function genbu_manga_chapter_sort_order(){

	$order = SORT_DESC;

	return apply_filters( 'genbu_manga_sort_order', $order );
}

/**
 * Upload Folder
 * 
 * @since 0.1.0
 */
function genbu_manga_upload_folder(){

	$folder = gmr_get_option('upload_folder');
	return apply_filters( 'genbu_manga_upload_folder', $folder );
}

/**
 * Manga Directory Path
 * 
 * @link http://codex.wordpress.org/Function_Reference/wp_upload_dir
 * @since 0.1.0
 */
function genbu_manga_dir(){

	/* wordpress upload dir */
	$wp_upload_dir = wp_upload_dir();

	/* wp upload base dir */
	$wp_upload_basedir = trailingslashit( $wp_upload_dir['basedir'] );
	
	/* manga folder  */
	$manga_folder = genbu_manga_upload_folder();

	/* manga dir path */
	$manga_dir = trailingslashit( $wp_upload_basedir . $manga_folder );

	/* output: manga folder path */
	return $manga_dir;
}

/**
 * Manga Directory URL
 * 
 * @since 0.1.0
 */
function genbu_manga_dir_url(){

	/* wordpress upload dir */
	$wp_upload_dir = wp_upload_dir();

	/* wp upload base dir */
	$wp_upload_baseurl = trailingslashit( $wp_upload_dir['baseurl'] );

	/* manga folder, == need to filter this ==  */
	$manga_folder = genbu_manga_upload_folder();

	/* manga dir path */
	$manga_url = trailingslashit( $wp_upload_baseurl . $manga_folder );

	/* output: manga folder uri */
	return $manga_url;
}


/**
 * Encode Folder Name
 * 
 * @since 0.1.0
 */
function genbu_manga_encode($text){

	/* replace space with underscore */
	return str_replace(' ', '_', $text);
}

/**
 * Decode Folder Name
 * 
 * @since 0.1.0
 */
function genbu_manga_decode($encoded_text){

	/* change underscore back to space */
	return str_replace('_', ' ', $encoded_text);
}


/**
 * Get Manga
 * 
 * @return array() of mangas.
 * @since 0.1.0
 */
function genbu_manga_get_mangas() {

	/* to make sure it's an array */
	$mangas = array();

	/* mangas directory */
	$dirname = genbu_manga_dir();

	/* get list of all directory in the folder */
	$dir = @opendir( $dirname );
	if ( $dir ) {
		while ( ( $file = @readdir( $dir ) ) !== false ) {
			if ( is_dir( $dirname . $file . '/' ) && ( $file != "." ) && ( $file != ".." ) ) {
				$mangas[] = $file;
			}
		}
		@closedir( $dir );
	}

	/* sort it by title */
	sort( $mangas );

	/* return output as array */
	return $mangas;
}


/**
 * Get Chapter
 * 
 * @return array() multi dimentional array of chapter info.
 * @since 0.1.0
 */
function genbu_manga_get_chapters( $manga ) {

	/* sort order */
	$manga_chapters_sorting = genbu_manga_chapter_sort_order();

	/* chapters folder and IDs default array */
	$chapters = array();
	$chapters_id = array();

	/* mangas chapter directory */
	$dirname = trailingslashit( genbu_manga_dir() . $manga );

	/* open the dir and get all chapter  */
	$dir = @opendir( $dirname );

	if ( $dir ) {
		while ( ( $file = @readdir( $dir ) ) !== false ) {
			if ( is_dir( $dirname . $file . '/' ) && ( $file != "." ) && ( $file != ".." ) ) {
				
				$chapter = array();
				$chapter["folder"] = $file;
				
				/* if in folder have "-" */
				$pos = strpos( $file, '-' );
				
				/* if it don't have chapter title, use name file */
				if ( $pos === false ) {
					$chapter["number"] = $file;
				}
				/* if it have chapter name */
				else {

					/* get the number */
					$chapter["number"] = trim( substr( $file, 0, $pos - 1 ) );

					/* get the title */
					$chapter["title"] = trim( substr( $file, $pos + 1 ) );
				}

				/* format numeric chapter with min 3 digits */
				if ( is_numeric( $chapter["number"] ) ){

					/* strip leading zeros */
					$chapter["number"] = ltrim( $chapter["number"], '0' );
					
					/* add leading zeros */
					$chapter["number"] = sprintf( "%03s", $chapter["number"] );
				}

				$chapters_id[] = $chapter["number"];

				$chapters[] = $chapter;
				
				/* create num */
			}
		}
		@closedir( $dir );
	}
	//sort( $chapters_id,  SORT_ASC );
	array_multisort( $chapters_id, $manga_chapters_sorting, $chapters );

	return $chapters;
}


/**
 * Get Chapter Index
 * 
 * @since 0.1.0
 */
function genbu_manga_get_chapter_index($chapters, $chapter_number) {

	/* order it! */
	$i = 0;
	while (($i < count($chapters)) && ($chapters[$i]["number"] != $chapter_number)) $i++;

	return ($i < count($chapters)) ? $i : -1;
}


/**
 * Get Pages
 * 
 * @since 0.1.0
 */
function genbu_manga_get_pages($manga, $chapter) {

	/* image types, maybe filterable ? */
	$omv_img_types = array("jpg", "jpeg", "png", "bmp", "gif");

	/* empty array */
	$pages = array();

	/* mangas pages directory */
	$dirname = trailingslashit( genbu_manga_dir() . $manga );
	$dirname = trailingslashit( $dirname . $chapter );

	$dir = @opendir($dirname);
	if ($dir) {
		while (($file = @readdir($dir)) !== false) {
			if (!is_dir($dirname . $file . '/')) {
				$file_extension = strtolower(substr($file, strrpos($file, ".") + 1));
				if (in_array($file_extension, $omv_img_types)) {
					$pages[] = $file;
				}
			}
		}
		@closedir($dir);
	}

	sort($pages);

	return $pages;
}


/**
 * Get Previous Page URL
 * 
 * @since 0.1.0
 */
function genbu_manga_get_previous_page($manga_e, $chapter_number_e, $current_page, $previous_chapter) {

	$url = null;

	if ($current_page > 1) {
		$url = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title='.$manga_e . '&manga_chapter=' . $chapter_number_e . '&manga_page=' . ($current_page - 1);
	}
	else if ($previous_chapter) {
		$pages = genbu_manga_get_pages(genbu_manga_decode($manga_e), $previous_chapter["folder"]);
		$url = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title='.$manga_e . '&manga_chapter=' . genbu_manga_encode($previous_chapter["number"]) . '&manga_page=' . count($pages);
	}
	return $url;
}

/**
 * Get Next Page URL
 * 
 * @since 0.1.0
 */
function genbu_manga_get_next_page( $manga_e, $chapter_number_e, $current_page, $nb_pages, $next_chapter ){

	$url = null;

	if( $current_page < $nb_pages) {
		$url = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title='.$manga_e . '&manga_chapter=' . $chapter_number_e . '&manga_page=' . ( $current_page + 1 );
	}
	elseif( $next_chapter ) {
		$url = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title='.$manga_e . '&manga_chapter=' . genbu_manga_encode($next_chapter["number"]) . '&manga_page=1';
	}

	return $url;
}


/**
 * Generate Pretty Permalink
 * only if custom using permalink
 *
 * @since 0.1.0
 */
function genbu_manga_permalink( $url ){

	/* globalize wp_rewrite object */
	global $wp_rewrite;

	/* if using pretty permalink */
	if ( $wp_rewrite->using_permalinks() ){

		$pageid = gmr_get_option('manga_page');
		$id = get_post( $pageid );
		$slug = $id->post_name;

		$url = str_replace('?page_id='.$pageid, '/'.$slug.'/', $url);
		$url = str_replace('&manga_title=', '', $url);
		$url = str_replace('&manga_chapter=', '/', $url);
		$url = str_replace('&manga_page=', '/', $url);
		if ( $url != '' )
			$url = user_trailingslashit( $url );
		$url = esc_url_raw( $url );
	}

	/* return the url */
	return $url;
}
