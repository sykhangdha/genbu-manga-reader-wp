<?php
/**
 * HTML Output
 * Generated Using Shortcodes
 * 
 * @since 0.1.0
 */
add_shortcode('manga-reader','manga_reader_shortcode');
add_shortcode('manga-index','genbu_manga_title_index');
add_shortcode('manga-chapter-index','genbu_manga_chapter_index');
//add_filter('the_content','manga_reader_shortcode');


/**
 * Genbu Manga Reader HTML
 * [manga-reader]
 * @since 0.1.0
 */
function manga_reader_shortcode( $content = '' ){

	/* in manga page, filter content! */
	if ( is_page( gmr_get_option('manga_page') ) ){

		/* data */
		$title_var = get_query_var('manga_title');
		$chapter_var = get_query_var('manga_chapter');
		$page_var = get_query_var('manga_page');
		$page = gmr_valid_page_query();

		/* defaults */
		$out = '';

		/* share buttons */
		$share  = genbu_manga_facebook_like();
		$share .= genbu_manga_tweet_button();
		$share .= genbu_manga_google_plus_one();

		/* in manga page index */
		if( is_page( gmr_get_option('manga_page') ) ){
			$out  = '<div class="manga-index-page">';
			$out .= '<div class="before-manga">' . gmr_get_option('manga_index_top_ads') . '</div>';
			$out .= '<div class="manga-reader-share">'.$share.'</div>';
			$out .= apply_filters('manga_index_table',do_shortcode('[manga-index]'));
			$out .= '<div class="after-manga">' . gmr_get_option('manga_index_bottom_ads') . '</div>';
			$out .= '</div>';
		}

		/* in manga title page */
		if ( $title_var ){
			$out  = '<div class="manga-title-page">';
			$out .= genbu_manga_cpt_query();
			$out .= '<div class="before-manga">' . gmr_get_option('manga_title_top_ads') . '</div>';
			$out .= '<div class="manga-reader-share">'.$share.'</div>';
			$out .= apply_filters('manga_chapter_index_table', do_shortcode('[manga-chapter-index]'));
			$out .= '<div class="after-manga">' . gmr_get_option('manga_title_bottom_ads') . '</div>';
			$out .= '</div>';
		}

		/* in chapter / page */
		if ( $page ){
			$out  = '<div class="manga-reader-page">';
			$out .= gmr_reader_page();
			$out .= '</div>';
		}

		$content = $out;
	}
	return $content;
}

//$gmr_wp_title = '';
/**
 * Manga Reader HTML
 * 
 * @since 0.1.0
 */
function gmr_reader_page(){

	/* data */
	$mangas = genbu_manga_get_mangas();
	$manga = gmr_valid_title_query();
	$chapters = genbu_manga_get_chapters( $manga );
	$the_chapter = gmr_valid_chapter_query();
	$chapter = null;
	$chapter_number = null;
	$chapter_title = null;
	$chapter_number_escaped = null;
	$previous_chapter = null;
	$next_chapter = null;
	if ( isset( $the_chapter['folder'] ) )
		$chapter = $the_chapter['folder'];
	if ( isset( $the_chapter['number'] ) ){
		$chapter_number = $the_chapter['number'];
		$chapter_number_escaped = genbu_manga_encode( $the_chapter['number'] );
	}
	if ( isset( $the_chapter['title'] ) )
		$chapter_title = $the_chapter['title'];
	$page = get_query_var('manga_page');
	$previous_chapter = gmr_valid_chapter_query( 'previous' );
	$next_chapter = gmr_valid_chapter_query( 'next' );
	$pages = genbu_manga_get_pages( $manga, $chapter );
	$manga_dir_url = genbu_manga_dir_url();
	$prev_page_url = genbu_manga_get_previous_page( genbu_manga_encode( $manga ), $chapter_number_escaped, $page, $previous_chapter );
	$next_page_url = genbu_manga_get_next_page( genbu_manga_encode( $manga ), $chapter_number_escaped, $page, count( $pages ), $next_chapter );
	$prev_page_url = genbu_manga_permalink( $prev_page_url );
	$next_page_url = genbu_manga_permalink( $next_page_url );

	/* MANGA PAGER HTML
	========================= */

	/* title select
	------------------------- */
	$title_select = '<div class="manga-pager-title">' . "\n" ;

	$title_select .= '<select class="select-manga-title" onchange="change_manga(this.value)" name="manga">';

	$title_select .= '<option value="0">Select title..</option>';

	for ( $i = 0; $i < count( $mangas ); $i++) {

		$m = $mangas[$i];

		$title_select .= '<option value="' . genbu_manga_encode( $m ) . '"' . ( ( $m == $manga ) ? ' selected="selected"' : '' ) . '>' . $m . '</option>';
	}

	$title_select .= "</select>\n";

	$title_select .= "</div>\n";

	$chapter_select = '<div class="manga-pager-chapter">' . "\n" ;

	$chapter_select .= '<select class="select-manga-chapter" onchange="change_chapter(\'' . genbu_manga_encode( $manga ) . '\',this.value)" name="chapter">';

	$chapter_reg_options = '';
	$chapter_extra_options = '';

	for ($i = 0; $i < count( $chapters ); $i++) {

		$cnumber = $chapters[$i]["number"];

		/* regular chapter */
		if ( is_numeric( $cnumber ) ){

			$chapter_reg_options .= '<option value="' . genbu_manga_encode( $cnumber ) . '"' . ( ( $cnumber == $chapter_number ) ? ' selected="selected"' : '' ) . '>' . $cnumber . ( isset( $chapters[$i]["title"] ) ? ( ' - ' . $chapters[$i]["title"]) : '') . '</option>';

		}
		/* bonus chapter */
		else{

			$chapter_extra_options .= '<option value="' . genbu_manga_encode( $cnumber ) . '"' . ( ( $cnumber == $chapter_number ) ? ' selected="selected"' : '' ) . '>' . $cnumber . ( isset( $chapters[$i]["title"] ) ? ( ' - ' . $chapters[$i]["title"]) : '') . '</option>';
		}
	}

	$chapter_select .= $chapter_reg_options;
	$chapter_select .= $chapter_extra_options;

	$chapter_select .= "</select>\n";

	$chapter_select .= '</div>' . "\n" ;

	/* page select
	------------------------- */
	$page_select  = '<div class="manga-pager-page">' . "\n" ;

	$page_select .= '<select class="select-manga-page" onchange="change_page(\'' . genbu_manga_encode( $manga ) . '\',\'' . $chapter_number_escaped . '\', this.value)" name="page">';

	for ( $p = 1; $p <= count( $pages ); $p++ ) {
			$page_select .= '<option value="' . $p . '" ' . ( ( $p == $page ) ? 'selected="selected"' : '') . '>#' . $p . '</option>';
	}

	$page_select .= "</select>\n";

	$page_select .= '</div>' . "\n" ;

	$manga_pager = $title_select . $chapter_select . $page_select;

	/* MANGA IMAGE
	========================= */
	/* next page link tag */
	$next_page_link_open = '';
	$next_page_link_close = '';

	/* if next page exist display it, and set */
	if ( $next_page_url  ) {
		$next_page_link_open = '<a href="' . $next_page_url . '">';
		$next_page_link_close = '</a>';
	}

	/* the image */
	$img_url = $manga_dir_url . $manga . "/" . $chapter . "/" . $pages[$page - 1];

	/* IMPORTANT: CHANGE IT TO POST TITLE ATTR */
	$image_alt = esc_attr( strip_tags( $manga . ' - ' . $chapter . ' - ' . $page ));

	/* Image tag */
	$img_src = '<img src="'.$img_url.'" alt="'.$image_alt.'" class="manga-picture" />';

	$img_src = $next_page_link_open . $img_src . $next_page_link_close;

	/* filter it */
	$img_src = apply_filters( 'manga_image_src', $img_src );

	/* NEXT PREV NAV
	========================= */
	/* previous html */
	$prevhtml = '<a class="pager-nav-previous" href="' . $prev_page_url . '">Prev</a>';
	if ( !$prev_page_url )
		$prevhtml = '<div class="pager-nav-no-previous">Prev</div>';

	/* next html */
	$nexthtml = '<a class="pager-nav-next" href="' . $next_page_url . '">Next</a>';
	if ( !$next_page_url )
		$nexthtml = '<div class="pager-nav-no-next">Next</div>';

	/* SHARE BUTTON
	========================= */
	$share  = genbu_manga_facebook_like();
	$share .= genbu_manga_tweet_button();
	$share .= genbu_manga_google_plus_one();

	/* NOTICE
	========================= */
	$notice  = '<div class="alert manga-alert manga-alert-key">';
	if ( isset( $chapter_title ) )
		$chapter_title = ' '.$chapter_title.' ';
	$notice .= '<p>You are reading <strong>"'. $manga . ' ' . $chapter_number .'"</strong>'. $chapter_title . ' ( Page ' . $page .' of '. count($pages) .' )</p>';
	$notice .= '<p><strong>Tips:</strong> You can use left and right keyboard keys or click on the image to browse between pages.</p>';
	$notice .= "</div>";

	/* OUTPUT START
	========================= */
	$output = '';

	/* top ads */
	$output .= '<div class="before-manga">' . gmr_get_option('manga_reader_top_ads') . '</div>';

	/* pager top */
	$output .= '<div class="manga-pager manga-pager-top">' . "\n" . $manga_pager . '</div>';

	/* top extras, nav and button */
	$output .= '<div class="manga-reader-utility manga-reader-utility-top">';
	$output .= '<div class="manga-pager-page-nav">' . $prevhtml . $nexthtml . '</div>';
	$output .= '<div class="manga-reader-share">'.$share.'</div>';
	$output .= "</div>\n";

	/* image */
	$output .= '<div class="manga-picture-wrap">';

		/* h1 title */
		$h1title = $manga;
		if ( isset( $the_chapter['number'] ) ){
			$h1title .= ': ' . genbu_manga_decode( $the_chapter['number'] );
		}
		if ( isset( $the_chapter['title'] ) ){
			$h1title .= ' ' . genbu_manga_decode( $the_chapter['title'] );
		}
		if ( $page ){
			$h1title .= ' - ' . $page . '/' . gmr_valid_page_query(true);
		}
		$output .= '<h1 class="manga-reader-title">'.$h1title.'</h1>';


	$output .= $img_src;

		/* bottom ads */
		$output .= '<div class="after-manga">' . gmr_get_option('manga_reader_bottom_ads') . '</div>';

	$output .= '</div>';

	/* bottom extras, nav and button */
	$output .= '<div class="manga-reader-utility manga-reader-utility-bottom">';
	$output .= '<div class="manga-pager-page-nav">' . $prevhtml . $nexthtml . '</div>';
	$output .= '<div class="manga-reader-share">'.$share.'</div>';
	$output .= "</div>\n";

	/* pager bottom */
	$output .= '<div class="manga-pager manga-pager-bottom">' . "\n" . $manga_pager . "</div>\n";

	/* notice */
	$output .= $notice;

	return $output;
}



/**
 * Manga Index
 * [manga-index]
 * @since 0.1.0
 */
function genbu_manga_title_index(){

	/* empty */
	$out = '';

	/* get mangas */
	$mangas = genbu_manga_get_mangas();

	/* get mangas query variable */
	$manga_title_query = get_query_var('manga_title');

	/* don't display in manga query */
	//if ( $manga_title_query ){
		//return;
	//}

	/* if manga exist, display */
	if ( $mangas ){

		/* table head */
		$out .= '<table class="manga-index">';
		$out .= '<thead>';
			$out .= '<tr>';
				$out .= '<th>Title</th>';
				$out .= '<th>Latest</th>';
				$out .= '<th>Updated</th>';
			$out .= '</tr>';
		$out .= '</thead>';

		/* start table */
		$out .= '<tbody>';

		/* foreach manga */
		foreach ( $mangas as $manga ){

			/* url ugly permalink */
			$url  = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title=' . genbu_manga_encode( $manga );

			/* Chapters */
			$chapters = genbu_manga_get_chapters( $manga );
			
			$chap_folder = array();
			$chap_number = array();
			$chap_title = array();

			$latest_folder = '';
			if ( isset( $chapters[0]["folder"] ) )
				$latest_folder = $chapters[0]["folder"];

			$latest_chapter = '';
			if ( isset( $chapters[0]["number"] ) )
				$latest_chapter = $chapters[0]["number"];

			$latest_title = '';
			if ( isset( $chapters[0]["title"] ) )
				$latest_title = $chapters[0]["title"];

			$latest_folder_num = array();
			$latest_title_num = array();
			$latest_number_num = array();

			foreach ( $chapters as $chapter ){
				if ( is_numeric($chapter["number"]) ){
				
					if ( isset( $chapter["folder"] ) )
						$latest_folder_num[] = $chapter["folder"];
				
					if ( isset( $chapter["number"] ) )
						$latest_number_num[] = $chapter["number"];
				
					if ( isset( $chapter["title"] ) )
						$latest_title_num[] = $chapter["title"];
				}
			}

			if ( !is_numeric( $latest_chapter ) ){
				/* fix folder */
				if ( isset( $latest_folder_num[0] ) )
					$latest_folder = $latest_folder_num[0];
				
				/* fix title */
				if ( isset( $latest_title_num[0] ) )
					$latest_title = $latest_title_num[0];
				
				/* fix number */
				if ( isset( $latest_number_num[0] ) )
					$latest_chapter = $latest_number_num[0];
				
			}

			/* latest */
			$latest_url = $url . '&manga_chapter=' . genbu_manga_encode( $latest_chapter ) . '&manga_page=1';

			if (!empty($latest_title))
				$latest_title = ' - ' . $latest_title;

			$path = genbu_manga_dir();
			$path = trailingslashit( genbu_manga_dir().$manga.'/'.$latest_folder );
			$update = 'N/A';
			$time_update = 'N/A';
			if (file_exists($path)) {
				$update = filemtime( $path );
				$time_update = '<span class="manga-updated manga-new">' . human_time_diff( $update, current_time('timestamp') ).' ago</span>';
				if( strtotime( '-1 week' ) >= $update ) {
					$time_update = '<span class="manga-updated">'.date( 'M d, Y', $update ).'</span>';
				}
			}

			/* output */
			$out .= '<tr>';
				$out .= '<th><a href="' . genbu_manga_permalink( $url ) . '">'.$manga.'</a></th>';
				$out .= '<td><a href="' . genbu_manga_permalink( $latest_url ) . '">' . $latest_chapter . $latest_title . '</a></td>';
				$out .= '<td>'.$time_update.'</td>';
			$out .= '</tr>';
		}

		$out .= '</tbody>';
		$out .= '</table>';
	}

	return $out;
}


/**
 * Chapter Index
 * [manga-chapter-index]
 * @since 0.1.0
 */
function genbu_manga_chapter_index( $attr ){

	/* shortcode attribute */
	$attr = shortcode_atts( array( 
		'title' => gmr_valid_title_query()
	), $attr );

	/* defaults */
	$out = '';
	$out_chap = '';
	$output_chap = '';
	$out_chap_open = '';
	$output_extra = '';
	$out_extra = '';
	$out_extra_open = '';
	$chap_reg_exist = null;
	$chap_bonus_exist = null;

	$title = $attr["title"];

	/* get mangas query variable */
	$manga = get_query_var('manga_title');
	$chapter_query = gmr_valid_chapter_query();
	$chapters = genbu_manga_get_chapters( $title );
	
	/* not valid, return */
	if ( !$chapters ){
		return;
	}

	foreach ( $chapters as $chapter ){

		/* url */
		$url  = home_url() . '?page_id=' . gmr_get_option('manga_page') . '&manga_title=' . $manga . '&manga_chapter=' . genbu_manga_encode( $chapter["number"] ) . '&manga_page=1';
		$url = genbu_manga_permalink( $url );

		$path = genbu_manga_dir();
		$path = trailingslashit( genbu_manga_dir().$title.'/'.$chapter["folder"] );
		$update = 'N/A';
		$time_update = 'N/A';
		if (file_exists($path)) {
			$update = filemtime( $path );
			$time_update = '<span class="manga-updated manga-new">'.human_time_diff( $update, current_time('timestamp') ).' ago</span>';
			if( strtotime( '-2 day' ) >= $update ) {
				$time_update = '<span class="manga-updated">'.date( 'M d, Y', $update ).'</span>';
			}
		}
		$chapter_title = '';
		if (isset($chapter["title"]))
			$chapter_title = $chapter["title"];

		/* regular chapter */
		if ( is_numeric( $chapter["number"] ) ){
			if ( $chapter["number"] ){
			
				if ($chapter_title)
					$chapter_title = '<span class="manga-chapter-index-title">'. $chapter_title.'</span>';

				$chap_reg_exist = true;

				$out_chap .= '<tr>';
					$out_chap .= '<th><a href="'.$url.'">'.$title.' '.$chapter["number"].'</a> '.$chapter_title.'</th>';
					$out_chap .= '<td>'.$time_update.'</td>';
				$out_chap .= '</tr>';
			}
		}
		/* bonus chapter */
		else{
			if ( $chapter["number"] ){
				if ($chapter_title)
					$chapter_title = '<span class="manga-chapter-index-title">'. $chapter_title.'</span>';

				$chap_bonus_exist = true;

				$out_extra .= '<tr class="extras">';
					$out_extra .= '<th><a href="'.$url.'">'.$title.': '.$chapter["number"].'</a> '.$chapter_title.'</th>';
					$out_extra .= '<td>'.$time_update.'</td>';
				$out_extra .= '</tr>';
			}
		}
	}

	/* Table Head */
	$output_chap .= '<table class="manga-chapter-index">';
	$output_chap .= '<thead>';
	$output_chap .= '<tr>';
	$output_chap .= '<th>' . $title . '</th>';
	$output_chap .= '<th>Updated</th>';
	$output_chap .= '</tr>';
	$output_chap .= '</thead>';

	/* display regular chapter */
	if ( $chap_reg_exist ){
		$output_chap .= $out_chap;
	}
	/* display bonus/extra chapter */
	if ( $chap_bonus_exist ){
		$output_chap .= $out_extra;
	}

	$output_chap .= '</tbody>';
	$output_chap .= '</table>';

	/* regular */
	$out .= $output_chap;

	/* bonus */
	$out .= $output_extra;

	return $out;
}



/**
 * Facebook Like
 * 
 * @since 0.1.0
 */
function genbu_manga_facebook_like(){

	wp_enqueue_script( 'genbu_manga_facebook', trailingslashit( GENBU_MANGA_JS ) .'facebook.js', array(), '0.1.0', true );

	$url = get_permalink(gmr_get_option('manga_page'));

	$share  = '<div class="gmr-like">';
	$share .= '<fb:like href="'. $url.'" send="false" width="280" show_faces="false"></fb:like>';
	$share .= '</div>';

	return $share;
}

/**
 * Tweet Button
 * 
 * @since 0.1.0
 */
function genbu_manga_tweet_button(){

	wp_enqueue_script( 'genbu_manga_twitter', "http://platform.twitter.com/widgets.js", array(), '0.1.0', true );

	$mention = '';
	$url = get_permalink(gmr_get_option('manga_page'));

	$share  = '<div class="gmr-tweet">';
	$share .= '<a href="'.__('http://twitter.com/share','genbu').'" class="twitter-share-button" data-url="'. $url.'" data-count="horizontal" ' . $mention . '>Tweet</a>';
	$share .= '</div>';

	return $share;
}

/**
 * Google +1
 * 
 * @since 0.1.0
 */
function genbu_manga_google_plus_one(){

	wp_enqueue_script( 'genbu_manga_google_plus',"https://apis.google.com/js/plusone.js", array(), '0.1.0', true );

	$url = get_permalink(gmr_get_option('manga_page'));

	$share  = '<div class="gmr-plus">';
	$share .= '<g:plusone size="medium" href="'. $url.'"></g:plusone>';
	$share .= '</div>';

	return $share;
}



