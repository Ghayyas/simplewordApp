<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');

class MobiConnectorUsers extends  WP_REST_Controller{
		
	public function __construct() {
		
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		// add to user object: mobiconnector_local_avatar : get avatar based https://wordpress.org/plugins/wp-user-avatar/screenshots/
		register_rest_field( 'user',
			'mobiconnector_local_avatar',
			array(
				'get_callback'    => array($this, 'get_local_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	/**
	 add avatar
	 */
	public function get_local_avatar( $object, $field_name, $request) {
		$avatar = get_avatar( $object["id"], 96); 
		if(!empty($avatar)) {
          $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER);
          $avatar = !empty($matches) ? $matches [0] [1] : "";
        }
		return $avatar;
	}
}
$MobiConnectorUsers = new MobiConnectorUsers();
?>