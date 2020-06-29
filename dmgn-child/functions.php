<?php
/**
 * Child theme functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Text Domain: oceanwp
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function oceanwp_child_enqueue_parent_style() {
	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme( 'OceanWP' );
	$version = $theme->get( 'Version' );
	// Load the stylesheet
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'oceanwp-style' ), $version );
	
}
add_action( 'wp_enqueue_scripts', 'oceanwp_child_enqueue_parent_style' );

/**
 * Additional head info
 *
 * Add additional X-UA-Compatible meta information to the 
 * `<head>` section.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head 
 */
function wpcrux_head_meta() {
  /*
   * MS IE and Edge compatibility meta (modify `content` as 
   * per your requirement).
   */
  $html = "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"/>\n";
  echo $html;
} 
add_action( 'wp_head', 'wpcrux_head_meta' );

/*
 * Function to link to Adobe Fonts Typekit
 */
add_action( 'wp_head', function() {
	?>
	<link rel="stylesheet" href="https://use.typekit.net/xvz4fdo.css">
	<?php
} );

/**
 * Gravity Wiz // Gravity Forms // Rename Uploaded Files
 *
 * Rename uploaded files for Gravity Forms. You can create a static naming template or using merge tags to base names on user input.
 *
 * Features:
 *  + supports single and multi-file upload fields
 *  + flexible naming template with support for static and dynamic values via GF merge tags
 *
 * Uses:
 *  + add a prefix or suffix to file uploads
 *  + include identifying submitted data in the file name like the user's first and last name
 *
 * @version   2.4
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/rename-uploaded-files-for-gravity-form/
 */
class GW_Rename_Uploaded_Files {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'template' => ''
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if( ! is_callable( array( 'GFFormsModel', 'get_physical_file_path' ) ) ) {
			return;
		}

		add_filter( 'gform_entry_post_save', array( $this, 'rename_uploaded_files' ), 9, 2 );
		add_filter( 'gform_entry_post_save', array( $this, 'stash_uploaded_files' ), 99, 2 );

		add_action( 'gform_after_update_entry', array( $this, 'rename_uploaded_files_after_update' ), 9, 2 );
		add_action( 'gform_after_update_entry', array( $this, 'stash_uploaded_files_after_update' ), 99, 2 );

	}

	function rename_uploaded_files( $entry, $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach( $form['fields'] as &$field ) {

			if( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );

			if( empty( $uploaded_files ) ) {
				continue;
			}

			$uploaded_files = $this->parse_files( $uploaded_files, $field );
			$stashed_files  = $this->parse_files( gform_get_meta( $entry['id'], 'gprf_stashed_files' ), $field );
			$renamed_files  = array();

			foreach( $uploaded_files as $_file ) {

				// Don't rename the same files twice.
				if( in_array( $_file, $stashed_files ) ) {
					$renamed_files[] = $_file;
					continue;
				}

				$dir  = wp_upload_dir();
				$dir  = $this->get_upload_dir( $form['id'] );
				$file = str_replace( $dir['url'], $dir['path'], $_file );

				if( ! file_exists( $file ) ) {
					continue;
				}

				$renamed_file = $this->rename_file( $file, $entry );

				if ( ! is_dir( dirname( $renamed_file ) ) ) {
					wp_mkdir_p( dirname( $renamed_file ) );
				}

				$result = rename( $file, $renamed_file );

				$renamed_files[] = $this->get_url_by_path( $renamed_file, $form['id'] );

			}

			// In cases where 3rd party add-ons offload the image to a remote location, no images can be renamed.
			if( empty( $renamed_files ) ) {
				continue;
			}

			if( $field->get_input_type() == 'post_image' ) {
				$value = str_replace( $uploaded_files[0], $renamed_files[0], rgar( $entry, $field->id ) );
			} else if( $field->multipleFiles ) {
				$value = json_encode( $renamed_files );
			} else {
				$value = $renamed_files[0];
			}

			GFAPI::update_entry_field( $entry['id'], $field->id, $value );

			$entry[ $field->id ] = $value;

		}

		return $entry;
	}

	function get_upload_dir( $form_id ) {
		$dir = GFFormsModel::get_file_upload_path( $form_id, 'PLACEHOLDER' );
		$dir['path'] = dirname( $dir['path'] );
		$dir['url']  = dirname( $dir['url'] );
		return $dir;
	}

	function rename_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->rename_uploaded_files( $entry, $form );
	}

	/**
	 * Stash the "final" version of the files after other add-ons have had a chance to interact with them.
	 *
	 * @param $entry
	 * @param $form
	 */
	function stash_uploaded_files( $entry, $form ) {

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$uploaded_files = rgar( $entry, $field->id );
			gform_update_meta( $entry['id'], 'gprf_stashed_files', $uploaded_files );

		}

		return $entry;
	}

	function stash_uploaded_files_after_update( $form, $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		$this->stash_uploaded_files( $entry, $form );
	}

	function rename_file( $file, $entry ) {

		$new_file = $this->get_template_value( $this->_args['template'], $file, $entry );
		$new_file = $this->increment_file( $new_file );

		return $new_file;
	}

	function increment_file( $file ) {

		$file_path = GFFormsModel::get_physical_file_path( $file );
		$pathinfo  = pathinfo( $file_path );
		$counter   = 1;

		// increment the filename if it already exists (i.e. balloons.jpg, balloons1.jpg, balloons2.jpg)
		while ( file_exists( $file_path ) ) {
			$file_path = str_replace( ".{$pathinfo['extension']}", "{$counter}.{$pathinfo['extension']}", GFFormsModel::get_physical_file_path( $file ) );
			$counter++;
		}

		$file = str_replace( basename( $file ), basename( $file_path ), $file );

		return $file;
	}

	function is_path( $filename ) {
		return strpos( $filename, '/' ) !== false;
	}

	function get_template_value( $template, $file, $entry ) {

		$info = pathinfo( $file );

		if( strpos( $template, '/' ) === 0 ) {
			$dir      = wp_upload_dir();
			$template = $dir['basedir'] . $template;
		} else {
			$template = $info['dirname'] . '/' . $template;
		}

		// replace our custom "{filename}" psuedo-merge-tag
		$value = str_replace( '{filename}', $info['filename'], $template );

		// replace merge tags
		$form  = GFAPI::get_form( $entry['form_id'] );
		$value = GFCommon::replace_variables( $value, $form, $entry, false, true, false, 'text' );

		// make sure filename is "clean"
		$filename = $this->clean( basename( $value ) );
		$value    = str_replace( basename( $value ), $filename, $value );

		// append our file ext
		$value .= '.' . $info['extension'];

		return $value;
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['form_id'];
	}

	function is_applicable_field( $field ) {

		$is_file_upload_field   = in_array( GFFormsModel::get_input_type( $field ), array( 'fileupload', 'post_image' ) );
		$is_applicable_field_id = $this->_args['field_id'] ? $field['id'] == $this->_args['field_id'] : true;

		return $is_file_upload_field && $is_applicable_field_id;
	}

	function clean( $str ) {
		return sanitize_file_name( $str );
	}

	function get_url_by_path( $file, $form_id ) {

		$dir = $this->get_upload_dir( $form_id );
		$url = str_replace( $dir['path'], $dir['url'], $file );

		return $url;
	}

	function parse_files( $files, $field ) {

		if( empty( $files ) ) {
			return array();
		}

		if( $field->get_input_type() == 'post_image' ) {
			$file_bits = explode( '|:|', $files );
			$files = array( $file_bits[0] );
		} else if( $field->multipleFiles ) {
			$files = json_decode( $files );
		} else {
			$files = array( $files );
		}

		return $files;
	}

}

# Configuration

new GW_Rename_Uploaded_Files( array(
	'form_id' => 36,
	'field_id' => 4,
	'template' => '{Name (First):6.3}-{Name (Last):6.6}-{filename}' // most merge tags are supported, original file extension is preserved
) );

/**
 * Order the Social Icons in the Header Nav area
 * 
 * Credit to Rajeeb at OceanWP - 53 minutes to reply!
 */
add_filter( 'ocean_social_options', 'change_social_options_order' );
function change_social_options_order(){
  return array(
    'linkedin' => array(
        'label' => esc_html__( 'LinkedIn', 'oceanwp' ),
        'icon_class' => 'fa fa-linkedin',
    ),
    'instagram'  => array(
        'label' => esc_html__( 'Instagram', 'oceanwp' ),
        'icon_class' => 'fa fa-instagram',
    ),
    'twitter' => array(
        'label' => esc_html__( 'Twitter', 'oceanwp' ),
        'icon_class' => 'fa fa-twitter',
    ),
    'youtube' => array(
        'label' => esc_html__( 'Youtube', 'oceanwp' ),
        'icon_class' => 'fa fa-youtube',
    ),
    'email' => array(
        'label' => esc_html__( 'Email', 'oceanwp' ),
        'icon_class' => 'fa fa-envelope',
    ),
    'facebook' => array(
        'label' => esc_html__( 'Facebook', 'oceanwp' ),
        'icon_class' => 'fa fa-facebook',
    ),
    'googleplus' => array(
        'label' => esc_html__( 'Google Plus', 'oceanwp' ),
        'icon_class' => 'fa fa-google-plus',
    ),
    'pinterest'  => array(
        'label' => esc_html__( 'Pinterest', 'oceanwp' ),
        'icon_class' => 'fa fa-pinterest-p',
    ),
    'dribbble' => array(
        'label' => esc_html__( 'Dribbble', 'oceanwp' ),
        'icon_class' => 'fa fa-dribbble',
    ),
    'vk' => array(
        'label' => esc_html__( 'VK', 'oceanwp' ),
        'icon_class' => 'fa fa-vk',
    ),
    'tumblr'  => array(
        'label' => esc_html__( 'Tumblr', 'oceanwp' ),
        'icon_class' => 'fa fa-tumblr',
    ),
    'github'  => array(
        'label' => esc_html__( 'Github', 'oceanwp' ),
        'icon_class' => 'fa fa-github-alt',
    ),
    'flickr'  => array(
        'label' => esc_html__( 'Flickr', 'oceanwp' ),
        'icon_class' => 'fa fa-flickr',
    ),
    'skype' => array(
        'label' => esc_html__( 'Skype', 'oceanwp' ),
        'icon_class' => 'fa fa-skype',
    ),
    'vimeo' => array(
        'label' => esc_html__( 'Vimeo', 'oceanwp' ),
        'icon_class' => 'fa fa-vimeo-square',
    ),
    'vine' => array(
        'label' => esc_html__( 'Vine', 'oceanwp' ),
        'icon_class' => 'fa fa-vine',
    ),
    'xing' => array(
        'label' => esc_html__( 'Xing', 'oceanwp' ),
        'icon_class' => 'fa fa-xing',
    ),
    'yelp' => array(
        'label' => esc_html__( 'Yelp', 'oceanwp' ),
        'icon_class' => 'fa fa-yelp',
    ),
    'tripadvisor' => array(
        'label' => esc_html__( 'Tripadvisor', 'oceanwp' ),
        'icon_class' => 'fa fa-tripadvisor',
    ),
    'rss'  => array(
        'label' => esc_html__( 'RSS', 'oceanwp' ),
        'icon_class' => 'fa fa-rss',
    ),
  );
}

/*
 * Function for post duplication. Duplicates appear as drafts. User is redirected to the edit screen.
 */
function rd_duplicate_post_as_draft(){
    global $wpdb;
    if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
      wp_die('No post to duplicate has been supplied!');
    }
   
    /*
     * Nonce verification
     */
    if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
      return;
   
    /*
     * get the original post id
     */
    $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
    /*
     * and all the original post data then
     */
    $post = get_post( $post_id );
   
    /*
     * if you don't want current user to be the new post author,
     * then change next couple of lines to this: $new_post_author = $post->post_author;
     */
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;
   
    /*
     * if post data exists, create the post duplicate
     */
    if (isset( $post ) && $post != null) {
   
      /*
       * new post data array
       */
      $args = array(
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
        'post_author'    => $new_post_author,
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_name'      => $post->post_name,
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_status'    => 'draft',
        'post_title'     => $post->post_title,
        'post_type'      => $post->post_type,
        'to_ping'        => $post->to_ping,
        'menu_order'     => $post->menu_order
      );
   
      /*
       * insert the post by wp_insert_post() function
       */
      $new_post_id = wp_insert_post( $args );
   
      /*
       * get all current post terms ad set them to the new post draft
       */
      $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
      foreach ($taxonomies as $taxonomy) {
        $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
      }
   
      /*
       * duplicate all post meta just in two SQL queries
       */
      $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
      if (count($post_meta_infos)!=0) {
        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
        foreach ($post_meta_infos as $meta_info) {
          $meta_key = $meta_info->meta_key;
          if( $meta_key == '_wp_old_slug' ) continue;
          $meta_value = addslashes($meta_info->meta_value);
          $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
        }
        $sql_query.= implode(" UNION ALL ", $sql_query_sel);
        $wpdb->query($sql_query);
      }
   
   
      /*
       * finally, redirect to the edit post screen for the new draft
       */
      wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
      exit;
    } else {
      wp_die('Post creation failed, could not find original post: ' . $post_id);
    }
  }
  add_action( 'admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft' );
   
  /*
   * Add the duplicate link to action list for post_row_actions
   */
  function rd_duplicate_post_link( $actions, $post ) {
    if (current_user_can('edit_posts')) {
      $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
    return $actions;
  }
   
  add_filter( 'page_row_actions', 'rd_duplicate_post_link', 10, 2 );