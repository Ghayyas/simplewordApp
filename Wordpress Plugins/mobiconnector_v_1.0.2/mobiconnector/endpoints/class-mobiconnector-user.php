<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');

class MobiConnectorUser extends  WP_REST_Controller{
	private $rest_url = 'mobiconnector/user';	
	public function __construct() {
		
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		
		// register users
		register_rest_route( $this->rest_url, '/register', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'register' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						),
						'password' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'random_password' => array(
							'sanitize_callback' => 'absint'
						),
						'email' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_nicename' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'display_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'nickname' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
					),
				)
			) 
		);
		
		// forgot password
		register_rest_route( $this->rest_url, '/forgot_password', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'forgot_password' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);
		// update user infomation
		register_rest_route( $this->rest_url, '/update_profile', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'update_profile' ),
					'args' => array(
						'user_pass' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_nicename' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_email' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'display_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'nickname' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_url' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'description' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_profile_picture' => '',
					),
				)
			) 
		);
		// get infomation of user logged
		register_rest_route( $this->rest_url, '/get_info', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'get_info' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);
	}
	// register a New user from frontend
	function register( $request ) {
		/** Full parameters
			array(
				'username' 			=> 'String (required)' ,
				'password' 			=> 'String (optional)' ,
				'email' 			=> 'String (optional)' ,
				'random_password ' 	=> 'Int 1/0 - Yes/No(optional)' ,
				'user_nicename' 	=> 'String (optional)' ,
				'display_name' 		=> 'String (optional)' ,
				'nickname' 			=> 'String (optional)' ,
				'first_name' 		=> 'String (optional)' ,
				'last_name' 		=> 'String (optional)' ,
			);
		**/
		$parameters = $request->get_params();
		// nếu random_password tồn tại = 1 thì hủy password
		if(isset($parameters['random_password'])) {
			$random_password = wp_generate_password( 8, false );
			$parameters['password'] = $random_password;
		}		
		// kiểm tra username hay email đã tồn tại chưa
		$user_id = username_exists( $parameters['username'] );
		//var_dump($user_id);
		if($user_id !== false) {
			return new WP_Error( 'username_exists', 'Username already exists', array( 'status' => 404 ) );
		}
		// kiểm tra email nếu có
		if(isset($parameters['email']) && email_exists($parameters['email']) != false) {
			return new WP_Error( 'email_exists', 'Email already exists', array( 'status' => 404 ) );
		}
		// kiểm tra có password hay ko
		if(!isset($parameters['password'])) {
			return new WP_Error( 'password_empty', 'Password required', array( 'status' => 404 ) );
		}
		$user_id = wp_create_user( $parameters['username'], $parameters['password'], @$parameters['email'] );
		// update firstname & lastname
		$new_user = array(
				'ID' 			=> $user_id,
				'user_nicename' => @$parameters['user_nicename'],
				'display_name' 	=> @$parameters['display_name'],
				'nickname' 		=> @$parameters['nickname'],
				'first_name' 	=> @$parameters['first_name'],
				'last_name' 	=> @$parameters['last_name'],
		);
		$user_id = wp_update_user( $new_user );
		// email den admin and user
		$this->new_user_notification($user_id,null, 'both');
		return $user_id;
	}
	/**
	 * Email login credentials to a newly-registered user.
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 * @since 2.0.0
	 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
	 * @since 4.3.1 The `$plaintext_pass` parameter was deprecated. `$notify` added as a third parameter.
	 * @since 4.6.0 The `$notify` parameter accepts 'user' for sending notification only to the user created.
	 *
	 * @global wpdb         $wpdb      WordPress database object for queries.
	 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
	 *
	 * @param int    $user_id    User ID.
	 * @param null   $deprecated Not used (argument deprecated).
	 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
	 */
	public function new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );
		
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		// email to admin
		if ( 'user' !== $notify ) {
			$message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );
		}

		// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notifcation.
		if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
			return;
		}

		$message = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		// email to customer
		wp_mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), $message);
	}
	/******* forgot_password **********/
	public function forgot_password( $request ) {
		/** Full parameters
			array(
				'username' 			=> 'String (required)'
			);
		**/
		$parameters = $request->get_params();
		// kiểm tra username hay email đã tồn tại chưa
		$user_id = username_exists( $parameters['username'] );
		//var_dump($user_id);
		if($user_id == false) {
			return new WP_Error( 'user_not_exists', 'User is not exist', array( 'status' => 404 ) );
		}
		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );
		//var_dump($user);
		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		$message = __("Someone requested that the password be reset for the following account:") . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message = __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . ">\r\n\r\n";

		wp_mail($user->user_email, sprintf(__('Password Reset for [%s]'), $user->user_login), $message);
		return true;
	}
	/** update users ************/
	public function update_profile($request) {
		/**** infomation
		 array(
			'ID' =>'Int (required)',
			'user_pass' =>'String (optional)',
			'user_nicename' =>'String (optional)',
			'user_email' =>'String (optional)',
			'display_name' =>'String (optional)',
			'first_name' =>'String (optional)',
			'last_name' =>'String (optional)',
			'user_url' =>'String (optional)',
			'nickname' =>'String (optional)',
			'description' =>'String (optional)',
			'user_profile_picture' =>'String (optional)', base64String encode
 		 );
		***/
		$parameters = $request->get_params();
		$id = (int) get_current_user_id();
		
		unset($parameters['user_login']);
		$user = get_userdata( $id );
		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.' ), array( 'status' => 400 ) );
		}

		if ( isset($parameters['user_email']) && email_exists( $parameters['user_email'] ) && $parameters['user_email'] !== $user->user_email ) {
			return new WP_Error( 'rest_user_invalid_email', __( 'Email address is invalid.' ), array( 'status' => 400 ) );
		}

		if ( isset( $parameters['username'] ) && $parameters['username'] !== $user->user_login ) {
			return new WP_Error( 'rest_user_invalid_argument', __( "Username isn't editable" ), array( 'status' => 400 ) );
		}
		// Ensure we're operating on the same user we already checked
		$new_user = array(
				'ID' 	=> $id
		);
		if ( isset( $parameters['user_pass']) ){
			$new_user['user_pass'] = @$parameters['user_pass'];
		}
		if ( isset( $parameters['user_nicename']) ){
			$new_user['user_nicename'] = @$parameters['user_nicename'];
		}
		if ( isset( $parameters['user_email']) ){
			$new_user['user_email'] = @$parameters['user_email'];
		}
		if ( isset( $parameters['display_name']) ){
			$new_user['display_name'] = @$parameters['display_name'];
		}
		
		if ( isset( $parameters['nickname']) ){
			$new_user['nickname'] = @$parameters['nickname'];
		}
		if ( isset( $parameters['first_name']) ){
			$new_user['first_name'] = @$parameters['first_name'];
		}
		if ( isset( $parameters['last_name']) ){
			$new_user['last_name'] = @$parameters['last_name'];
		}
		if ( isset( $parameters['user_url']) ){
			$new_user['user_url'] = @$parameters['user_url'];
		}
		if ( isset( $parameters['description']) ){
			$new_user['description'] = @$parameters['description'];
		}
		if ( isset( $parameters['user_profile_picture']) ){
			$new_user['user_profile_picture'] = @$parameters['user_profile_picture'];
		}
		$user_id = wp_update_user( $new_user );
		if ( is_wp_error( $user_id ) ) {
			return new WP_Error( 'rest_user_invalid_argument', __( "There was an error, probably that user doesn't exist." ), array( 'status' => 400 ) );
		}
		$this->update_user_avatar($user_id, $new_user['user_profile_picture']);
		
		$user = get_userdata( $user_id );
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
		$attachment = wp_get_attachment_url( (int) $user_meta['wp_user_avatar']);
		$user_meta['wp_user_avatar'] = $attachment;
		$user_meta = array_merge($user_meta, $user);
		
		return $user_meta;
	}
	/* update avatar for user 
		@param: image data in base64 encode
	*/
	public function update_user_avatar($user_id, $image_base64string) {
		global $blog_id, $wpdb;
		// cập nhật avatar
		if(!empty($image_base64string)) {
			list($type, $image_base64string) = explode(';', $image_base64string);
			list(, $type)        = explode(':', $type);
			list(, $type)        = explode('/', $type);
			list(, $image_base64string)      = explode(',', $image_base64string);
			$type = strtolower($type); // lay extension of image
			$data = base64_decode($image_base64string);
			// upload den thu muc trong wordpress
			$wp_upload_dir = wp_upload_dir();	
			$filename = "mobi_avatar_".time().".$type";
			$path_to_file = $wp_upload_dir['path']."/".$filename;
			$filetype = wp_check_filetype( basename( $filename), null );
			// tao anh trong thu muc
			@file_put_contents($path_to_file, $data);
			// chen vao wordpress
			$attachment = array(
				'post_author' => $user_id,
				'post_content' => '',
				'post_content_filtered' => '',
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_excerpt' => '',
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => $filetype['type'],
				'comment_status' => 'open',
				'ping_status' => 'closed',
				'post_password' => '',
				'to_ping' =>  '',
				'pinged' => '',
				'post_parent' => 0,
				'menu_order' => 0,
				'guid' => $wp_upload_dir['url']."/".$filename,
			);
			$wpdb->insert("{$wpdb->prefix}posts", $attachment); 
			$attach_id = $wpdb->insert_id;
			if($attach_id == false) // loi do cap nhat
				return false;
			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $path_to_file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			// cap nhat _wp_attached_file trong postdata
			update_attached_file($attach_id, $path_to_file);
			// Remove old attachment postmeta dung trong wp-user-avatar plugin
			delete_metadata('post', null, '_wp_attachment_wp_user_avatar', $user_id, true);
			// Create new attachment postmeta dung trong wp-user-avatar plugin
			add_post_meta($attach_id, '_wp_attachment_wp_user_avatar', $user_id);
			// Update usermeta dung trong wp-user-avatar plugin
			update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id).'user_avatar', $attach_id);

			return true;
		}
		return false;
	}
	/****** lấy thông tin của user đang login hiện tại ******************/
	public function get_info($request) {
		$parameters = $request->get_params();
		// kiểm tra username hay email đã tồn tại chưa
		$user_id = username_exists( $parameters['username'] );
		//var_dump($user_id);
		if($user_id == false) {
			return new WP_Error( 'username_not_exists', 'Username is not yet exists', array( 'status' => 404 ) );
		}
				
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.' ), array( 'status' => 400 ) );
		}
		$user = get_userdata( $user_id );
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
		$attachment = wp_get_attachment_url( (int) $user_meta['wp_user_avatar']);
		$user_meta['wp_user_avatar'] = $attachment;
		$user_meta = array_merge($user_meta, $user);
		unset($user_meta['user_pass']);
		unset($user_meta['wp_capabilities']);
		unset($user_meta['show_admin_bar_front']);
		unset($user_meta['last_update']);
		unset($user_meta['user_registered']);
		unset($user_meta['user_activation_key']);
		unset($user_meta['user_status']);
		unset($user_meta['last_update']);
		unset($user_meta['rich_editing']);
		unset($user_meta['comment_shortcuts']);
		unset($user_meta['admin_color']);
		unset($user_meta['wp_user_level']);
		unset($user_meta['use_ssl']);
		return $user_meta;
	}
}
$MobiConnectorPosts = new MobiConnectorUser();
?>