<?php
/**
 * Tamatebako Meta boxes
 *
 * Helper Class to easily create metaboxes with custom fields
 * Based on Custom Metaboxes and Fields v.0.9 by:
 * - Andrew Norcross (@norcross / andrewnorcross.com)
 * - Jared Atchison (@jaredatch / jaredatchison.com)
 * - Bill Erickson (@billerickson / billerickson.net)
 * 
 * Available metabox:
 * 
 * TEXT
 * - text
 * - text_small
 * - text_medium
 * - text_money
 * 
 * COLOR PICKER
 * - colorpicker
 * 
 * TEXT AREA
 * - textarea
 * - textarea_small
 * - textarea_code
 * - wysiwyg
 * 
 * SELECT
 * - select
 * - radio_inline
 * - radio
 * - checkbox
 * - multicheck
 * - taxonomy_select
 * - taxonomy_radio
 * - taxonomy_multicheck
 * 
 * UPLOAD
 * - file
 * 
 * OTHER
 * - title
 * 
 * @link https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 * 
 * @package 	Tamatebako
 * @copyright	Copyright (c) 2012, David Chandra Purnama
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @version		0.2.0
 */

if ( ! class_exists( 'tmb_Meta_Box_Validate' ) ){

	if ( ! defined('TAMATEBAKO_METABOX_VERSION'))
		define( 'TAMATEBAKO_METABOX_VERSION', '0.2.0' );

	if ( ! defined('TAMATEBAKO_JS_DIR'))
		define( 'TAMATEBAKO_JS_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js' );

	if ( ! defined('TAMATEBAKO_JS'))
		define( 'TAMATEBAKO_JS', trailingslashit( plugin_dir_url( __FILE__) ) . 'js' );

	if ( ! defined('TAMATEBAKO_CSS_DIR'))
		define( 'TAMATEBAKO_CSS_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) . 'css' );

	if ( ! defined('TAMATEBAKO_CSS'))
		define( 'TAMATEBAKO_CSS', trailingslashit( plugin_dir_url( __FILE__) ) . 'css' );


/**
 * Get Metaboxes from theme.
 * 
 */
add_action('init','tmb_metabox_create');
function tmb_metabox_create(){
	static $meta_boxes = null;

	if (!$meta_boxes)
		$meta_boxes = array();

	$meta_boxes = apply_filters ( 'tmb_meta_boxes' , $meta_boxes );

	foreach ( (array) $meta_boxes as $meta_box ) {
		$my_box = new tmb_Meta_Box( $meta_box );
	}
}

/**
 * Validate value of meta fields
 *
 * Define ALL validation methods inside this class and use the names of these 
 * methods in the definition of meta boxes (key 'validate_func' of each field)
 */
class tmb_Meta_Box_Validate {
	function check_text( $text ) {
		if ($text != 'hello') {
			return false;
		}
		return true;
	}
}

/**
 * Create meta boxes
 */
class tmb_Meta_Box {
	protected $_meta_box;

	function __construct( $meta_box ) {
		if ( !is_admin() ) return;

		$this->_meta_box = $meta_box;

		$upload = false;
		foreach ( $meta_box['fields'] as $field ) {
			if ( $field['type'] == 'file' || $field['type'] == 'file_list' || $field['type'] == 'file_gallery' ) {
				$upload = true;
				break;
			}
		}

		global $pagenow;
		if ( $upload && in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) ) {
			add_action( 'admin_head', array( &$this, 'add_post_enctype' ) );
		}

		add_action( 'admin_menu', array( &$this, 'add' ) );
		add_action( 'save_post', array( &$this, 'save' ) );

		/* only if WordPress at least version 3.5 */
		global $wp_version;

		if ( version_compare( round( $wp_version, 1 ), '3.5' ) >= 0  ){

			add_action( 'add_attachment', array( &$this, 'save' ), 1 );
			add_action( 'edit_attachment', array( &$this, 'save' ), 1 );
		}

		add_filter( 'tmb_show_on', array( &$this, 'add_for_id' ), 10, 2 );
		add_filter( 'tmb_show_on', array( &$this, 'add_for_page_template' ), 10, 2 );
	}

	function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#post").attr("enctype", "multipart/form-data");
			jQuery("#post").attr("encoding", "multipart/form-data");
		});
		</script>';
	}

	// Add metaboxes
	function add() {
		$this->_meta_box['context'] = empty($this->_meta_box['context']) ? 'normal' : $this->_meta_box['context'];
		$this->_meta_box['priority'] = empty($this->_meta_box['priority']) ? 'high' : $this->_meta_box['priority'];
		$this->_meta_box['show_on'] = empty( $this->_meta_box['show_on'] ) ? array('key' => false, 'value' => false) : $this->_meta_box['show_on'];

		foreach ( $this->_meta_box['pages'] as $page ) {
			if( apply_filters( 'tmb_show_on', true, $this->_meta_box ) )
				add_meta_box( $this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']) ;
		}
	}

	/**
	 * Show On Filters
	 * Use the 'tmb_show_on' filter to further refine the conditions under which a metabox is displayed.
	 * Below you can limit it by ID and page template
	 */

	// Add for ID 
	function add_for_id( $display, $meta_box ) {
		if ( 'id' !== $meta_box['show_on']['key'] )
			return $display;

		// If we're showing it based on ID, get the current ID
		if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
		elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
		if( !isset( $post_id ) )
			return false;

		// If value isn't an array, turn it into one
		$meta_box['show_on']['value'] = !is_array( $meta_box['show_on']['value'] ) ? array( $meta_box['show_on']['value'] ) : $meta_box['show_on']['value'];
		
		// If current page id is in the included array, display the metabox

		if ( in_array( $post_id, $meta_box['show_on']['value'] ) )
			return true;
		else
			return false;
	}

	// Add for Page Template
	function add_for_page_template( $display, $meta_box ) {
		if( 'page-template' !== $meta_box['show_on']['key'] )
			return $display;

		// Get the current ID
		if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
		elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
		if( !( isset( $post_id ) || is_page() ) ) return false;

		// Get current template
		$current_template = get_post_meta( $post_id, '_wp_page_template', true );

		// If value isn't an array, turn it into one
		$meta_box['show_on']['value'] = !is_array( $meta_box['show_on']['value'] ) ? array( $meta_box['show_on']['value'] ) : $meta_box['show_on']['value'];

		// See if there's a match
		if( in_array( $current_template, $meta_box['show_on']['value'] ) )
			return true;
		else
			return false;
	}

	// Show fields
	function show() {

		global $post;

		// Use nonce for verification
		echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';
		echo '<table class="form-table tmb_metabox">';

		foreach ( $this->_meta_box['fields'] as $field ) {
			// Set up blank or default values for empty ones
			if ( !isset( $field['name'] ) ) $field['name'] = '';
			if ( !isset( $field['desc'] ) ) $field['desc'] = '';
			if ( !isset( $field['std'] ) ) $field['std'] = '';
			if ( 'file' == $field['type'] && !isset( $field['allow'] ) ) $field['allow'] = array( 'url', 'attachment' );
			if ( 'file' == $field['type'] && !isset( $field['save_id'] ) )  $field['save_id']  = false;
			if ( 'multicheck' == $field['type'] ) $field['multiple'] = true;  
						
			$meta = get_post_meta( $post->ID, $field['id'], 'multicheck' != $field['type'] /* If multicheck this can be multiple values */ );

			echo '<tr>';

			if ( $field['type'] == "title" ) {
				echo '<td colspan="2">';
			} else {
				if( $this->_meta_box['show_names'] == true ) {
					echo '<th style="width:18%"><label for="', $field['id'], '">', $field['name'], '</label></th>';
				}
				echo '<td>';
			}

			switch ( $field['type'] ) {

				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" />','<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'text_small':
					echo '<input class="tmb_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_medium':
					echo '<input class="tmb_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_money':
					echo '$ <input class="tmb_text_money" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_slug':
					$extrapostdata = get_post(get_the_ID(), ARRAY_A);
					$slug = $extrapostdata['post_name'];
					echo '<input class="tmb_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $slug : $slug, '" /><span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'colorpicker':
					wp_enqueue_script( 'tmb-meta-box-color-picker-scripts' );
					$meta = '' !== $meta ? $meta : $field['std'];
					$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
					if ( preg_match( '/^' . $hex_color . '/i', $meta ) ) // Value is just 123abc, so prepend #.
						$meta = '#' . $meta;
					elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta ) ) // Value doesn't match #123abc, so sanitize to just #.
						$meta = "#";
					echo '<input class="tmb_colorpicker tmb_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta, '" /><span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'textarea':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'textarea_small':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'textarea_code':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" class="tmb_textarea_code">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'select':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					foreach ($field['options'] as $option) {
						echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
					}
					echo '</select>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio_inline':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<div class="tmb_radio_inline">';
					$i = 1;
					foreach ($field['options'] as $option) {
						echo '<div class="tmb_radio_inline_option"><input type="radio" name="', $field['id'], '" id="', $field['id'], $i, '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'], '</label></div>';
						$i++;
					}
					echo '</div>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<ul>';
					$i = 1;
					foreach ($field['options'] as $option) {
						echo '<li><input type="radio" name="', $field['id'], '" id="', $field['id'], $i,'" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'].'</label></li>';
						$i++;
					}
					echo '</ul>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" value="1" name="'.$field['id'].'" '. checked( ! empty( $meta ),true ,false ) .' />';
					echo '<span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'multicheck':
					echo '<ul>';
					$i = 1;
					foreach ( $field['options'] as $value => $name ) {
						// Append `[]` to the name to get multiple values
						// Use in_array() to check whether the current option should be checked
						echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], $i, '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $name, '</label></li>';
						$i++;
					}
					echo '</ul>';
					echo '<span class="tmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'title':
					echo '<h5 class="tmb_metabox_title">', $field['name'], '</h5>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'wysiwyg':
					wp_editor( $meta ? $meta : $field['std'], $field['id'], isset( $field['options'] ) ? $field['options'] : array() );
			        echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_select':
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ( $terms as $term ) {
						if (!is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<option value="' . $term->slug . '" selected>' . $term->name . '</option>';
						} else {
							echo '<option value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name . '</option>';
						}
					}
					echo '</select>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_radio':
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					echo '<ul>';
					foreach ( $terms as $term ) {
						if ( !is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<li><input type="radio" name="', $field['id'], '" value="'. $term->slug . '" checked>' . $term->name . '</li>';
						} else {
							echo '<li><input type="radio" name="', $field['id'], '" value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name .'</li>';
						}
					}
					echo '</ul>';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_multicheck':
					echo '<ul>';
					$names = wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ($terms as $term) {
						echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], '" value="', $term->name , '"'; 
						foreach ($names as $name) {
							if ( $term->slug == $name->slug ){ echo ' checked="checked" ';};
						}
						echo' /><label>', $term->name , '</label></li>';
					}
				break;
				case 'file':
					wp_enqueue_script( 'tmb-meta-box-uploader-scripts' );
					$input_type_url = "hidden";
					if ( 'url' == $field['allow'] || ( is_array( $field['allow'] ) && in_array( 'url', $field['allow'] ) ) )
						$input_type_url="text";
					echo '<input class="tmb_upload_file" type="' . $input_type_url . '" size="45" id="', $field['id'], '" name="', $field['id'], '" value="', $meta, '" />';
					echo '<input class="tmb_upload_button button" type="button" value="Upload File" />';
					echo '<input class="tmb_upload_file_id" type="hidden" id="', $field['id'], '_id" name="', $field['id'], '_id" value="', get_post_meta( $post->ID, $field['id'] . "_id",true), '" />';
					echo '<p class="tmb_metabox_description">', $field['desc'], '</p>';
					echo '<div id="', $field['id'], '_status" class="tmb_upload_status">';	
						if ( $meta != '' ) { 
							$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
							if ( $check_image ) {
								echo '<div class="img_status">';
								echo '<img src="', $meta, '" alt="" />';
								echo '<a href="#" class="tmb_remove_file_button" rel="', $field['id'], '">Remove Image</a>';
								echo '</div>';
							} else {
								$parts = explode( '/', $meta );
								for( $i = 0; $i < count( $parts ); ++$i ) {
									$title = $parts[$i];
								}
								echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <a href="#" class="tmb_remove_file_button" rel="', $field['id'], '">Remove</a>)';
							}
						}
					echo '</div>'; 
				break;
				default:
					do_action('tmb_render_' . $field['type'] , $field, $meta);
			}

			echo '</td>','</tr>';
		}
		echo '</table>';
	}

	// Save data from metabox
	function save( $post_id)  {

		// verify nonce
		if ( ! isset( $_POST['wp_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wp_meta_box_nonce'], basename(__FILE__) ) ) {
			return $post_id;
		}

		// check autosave
		if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		foreach ( $this->_meta_box['fields'] as $field ) {
			$name = $field['id'];

			if ( ! isset( $field['multiple'] ) )
				$field['multiple'] = ( 'multicheck' == $field['type'] ) ? true : false;

			$old = get_post_meta( $post_id, $name, !$field['multiple'] /* If multicheck this can be multiple values */ );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;

			if ( in_array( $field['type'], array( 'taxonomy_select', 'taxonomy_radio', 'taxonomy_multicheck' ) ) )  {	
				$new = wp_set_object_terms( $post_id, $new, $field['taxonomy'] );
			}
			if ( ($field['type'] == 'textarea') || ($field['type'] == 'textarea_small') ) {
				$new = htmlspecialchars( $new );
			}

			if ( ($field['type'] == 'textarea_code') ) {
				if ( current_user_can('unfiltered_html') ) {
					$new = $new;
				}
				else{
					$new = htmlspecialchars_decode( $new );
				}
			}

			$new = apply_filters('tmb_validate_' . $field['type'], $new, $post_id, $field);

			// validate meta value
			if ( isset( $field['validate_func']) ) {
				$ok = call_user_func( array( 'tmb_Meta_Box_Validate', $field['validate_func']), $new );
				if ( $ok === false ) { // pass away when meta value is invalid
					continue;
				}
			} elseif ( $field['multiple'] ) {
				delete_post_meta( $post_id, $name );
				if ( !empty( $new ) ) {
					foreach ( $new as $add_new ) {
						add_post_meta( $post_id, $name, $add_new, false );
					}
				}
			} elseif ( '' !== $new && $new != $old  ) {
				update_post_meta( $post_id, $name, $new );
			} elseif ( '' == $new ) {
				delete_post_meta( $post_id, $name );
			}

			if ( 'file' == $field['type'] ) {
				$name = $field['id'] . "_id";
				/* If multicheck this can be multiple values */
				$old = get_post_meta( $post_id, $name, !$field['multiple'] );
				if ( isset( $field['save_id'] ) && $field['save_id'] ) {
					$new = isset( $_POST[$name] ) ? $_POST[$name] : null;
				} else {
					$new = "";
				}

				if ( $new && $new != $old ) {
					update_post_meta( $post_id, $name, $new );
				} elseif ( '' == $new && $old ) {
					delete_post_meta( $post_id, $name, $old );
				}
			}
		}
	}
}

/**
 * Register and Enqueque Script and Style For Metaboxes
 * This will only loaded in admin, in post screen.
 * 
 */
add_action( 'admin_enqueue_scripts', 'tmb_scripts', 10 );
function tmb_scripts( $hook ) {

  	if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {

		/* color picker */
		$color_updated = date( '-Y.m.d', filemtime( TAMATEBAKO_JS_DIR . '/meta-box-color-picker.js' ) );
		wp_register_script( 'tmb-meta-box-color-picker-scripts', trailingslashit( TAMATEBAKO_JS ) . 'meta-box-color-picker.js', array( 'jquery', 'farbtastic' ), 'tmb-mb-'. TAMATEBAKO_METABOX_VERSION . $color_updated );

		/* uploader */
		$uploader_updated = date( '-Y.m.d', filemtime( TAMATEBAKO_JS_DIR . '/meta-box-uploader.js' ) );
		wp_register_script( 'tmb-meta-box-uploader-scripts', trailingslashit( TAMATEBAKO_JS ) . 'meta-box-uploader.js', array( 'jquery', 'jquery-ui-core', 'media-upload', 'thickbox' ). 'tmb-mb-'. TAMATEBAKO_METABOX_VERSION . $uploader_updated );

		/* Register Style */
		wp_deregister_style( 'tmb-meta-box-styles' );
		$css_updated = date( '-Y.m.d', filemtime( TAMATEBAKO_CSS_DIR . '/meta-box.css' ) );
		wp_register_style( 'tmb-meta-box-styles', trailingslashit( TAMATEBAKO_CSS ) . 'meta-box.css', array( 'thickbox', 'farbtastic' ), 'tmb-mb-'. TAMATEBAKO_METABOX_VERSION . $css_updated );

		/* Enqueque Style */
		wp_enqueue_style( 'tmb-meta-box-styles' );

	}

}

/**
 * Editor Footer Script
 * 
 */
add_action( 'admin_print_footer_scripts', 'tmb_editor_footer_scripts', 99 );
function tmb_editor_footer_scripts() { ?>
	<?php
	if ( isset( $_GET['tmb_force_send'] ) && 'true' == $_GET['tmb_force_send'] ) { 
		$label = $_GET['tmb_send_label']; 
		if ( empty( $label ) ) $label="Select File";
		?>	
		<script type="text/javascript">
		jQuery(function($) {
			$('td.savesend input').val('<?php echo $label; ?>');
		});
		</script>
		<?php 
	}
}

/**
 * Force 'Insert into Post' button from Media Library 
 * 
 */
add_filter( 'get_media_item_args', 'tmb_force_send' );
function tmb_force_send( $args ) {

	// if the Gallery tab is opened from a custom meta box field, add Insert Into Post button
	if ( isset( $_GET['tmb_force_send'] ) && 'true' == $_GET['tmb_force_send'] )
		$args['send'] = true;

	// if the From Computer tab is opened AT ALL, add Insert Into Post button after an image is uploaded
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {

		$args['send'] = true;

	}

	// change the label of the button on the From Computer tab
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {

		echo '
			<script type="text/javascript">
				function tmbGetParameterByNameInline(name) {
					name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
					var regexS = "[\\?&]" + name + "=([^&#]*)";
					var regex = new RegExp(regexS);
					var results = regex.exec(window.location.href);
					if(results == null)
						return "";
					else
						return decodeURIComponent(results[1].replace(/\+/g, " "));
				}

				jQuery(function($) {
					if (tmbGetParameterByNameInline("tmb_force_send")=="true") {
						var tmb_send_label = tmbGetParameterByNameInline("tmb_send_label");
						$("td.savesend input").val(tmb_send_label);
					}
				});
			</script>
		';
	}
	return $args;
}
// End. That's it, folks! //


/**
 * Validation Functions
 * 
 * @since 0.2.2
 */
add_filter( 'tmb_validate_file', 'tmb_validate_file' ); //file (uploader)
add_filter( 'tmb_validate_colorpicker', 'tmb_validate_colorpicker' ); //uploader


/* File Uploader */
function tmb_validate_file( $new ) {
	$filetype = wp_check_filetype($new);
	if ( $filetype["ext"] ) 
		$new = $new;
	else
		$new = '';
	return $new;
}

/* color picker */
function tmb_validate_colorpicker( $new ) {

	$hex = trim( $new );

	/* Strip recognized prefixes. */
	if ( 0 === strpos( $hex, '#' ) ) {
		$hex = substr( $hex, 1 );
	}
	elseif ( 0 === strpos( $hex, '%23' ) ) {
		$hex = substr( $hex, 3 );
	}
	/* Regex match. */
	if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return '';
	}
	else {
		return trim( $new );
	}
}

} //class exist check