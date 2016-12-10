<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');

class MobiConnectorCategory extends  WP_REST_Controller{
		
	public function __construct() {
		
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		
		// add to user object: mobiconnector_local_avatar : get avatar based https://wordpress.org/plugins/wp-user-avatar/screenshots/
		register_rest_field( 'category',
			'mobiconnector_avatar',
			array(
				'get_callback'    => array($this, 'get_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	/**
	 add avatar
	 */
	public function get_avatar( $object, $field_name, $request) {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(is_plugin_active('wpcustom-category-image/load.php') == false)
			return null;
		require_once(ABSPATH.'wp-content/plugins/wpcustom-category-image/WPCustomCategoryImage.php');
		$attr = array(
			'term_id' => $object['id'],
		);
		return WPCustomCategoryImage::get_category_image($attr, true);
		
	}
}
$MobiConnectorCategory = new MobiConnectorCategory();
?>