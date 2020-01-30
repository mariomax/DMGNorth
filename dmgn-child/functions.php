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
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
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