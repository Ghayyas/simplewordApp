<?php
/**
 * Plugin Name: Mobile Connector
 * Plugin URI: https://buy-addons.com/
 * Description: Intergrated to Wordpress Rest API
 * Version: 1.0.2
 * Author: buy-addons
 * Author URI: https://buy-addons.com
 * Requires at least: 2.0
 * Tested up to: 4.5
 * Compatibility with the REST API v2
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class MobiConnector{
	
	public function __construct() {
		require_once( 'includes/class.core.php' );
		require_once( 'includes/class-mobiconnector-install.php' );
		require_once( 'includes/class-mobiconnector-posts.php' );
		require_once( 'includes/class-mobiconnector-category.php' );
		require_once( 'includes/class-mobiconnector-comments.php' );
		require_once( 'includes/class-mobiconnector-users.php' );
		require_once( 'endpoints/class-mobiconnector-user.php' );
		require_once( 'endpoints/class-mobiconnector-post.php' );
		// cập nhật avatar
		require_once(ABSPATH.'wp-includes/pluggable.php' );
		require_once(ABSPATH.'wp-admin/includes/image.php' );
		
	}
}
$mobie = new MobiConnector();