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