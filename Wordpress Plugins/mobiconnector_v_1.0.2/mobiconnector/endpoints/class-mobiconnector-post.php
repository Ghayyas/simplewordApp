<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');

class MobiConnectorPost extends  WP_REST_Controller{
	private $rest_url = 'mobiconnector/post';	
	public function __construct() {
		
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		
		// register users
		register_rest_route( $this->rest_url, '/counter_view', array(
				array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'counter_view' ),
					'args' => array(
						'post_id' => array(
							'required' => true,
							'sanitize_callback' => 'absint'
						)
						
					),
				)
			) 
		);
		// lấy bài biết đọc nhiều nhất với query: wp-json/wp/v2/posts?filter[orderby]=post_views&filter[order]=asc
	}
	public function counter_view( $request ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( !is_plugin_active( 'post-views-counter/post-views-counter.php' ) ) {
		  return new WP_Error( 'post-views-counter_deactive', __( 'Post Views Counter Deactive' ), array( 'status' => 400 ) );
		}
		// require plugin
		require_once( ABSPATH . 'wp-content/plugins/post-views-counter/includes/functions.php' );
		$parameters = $request->get_params();
		pvc_view_post($parameters["post_id"]); // update post view
		return pvc_get_post_views($parameters["post_id"]); // get view of Post
	}
}
$MobiConnectorPost = new MobiConnectorPost();
?>