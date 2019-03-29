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