<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');

class MobiConnectorPosts extends  WP_REST_Controller{
	// Mảng chứa các ảnh cần tạo
	public $thumnails = array(
		'mobiconnector_small' => array(
			'width' => 320,
			'height' => 240
		),
		'mobiconnector_medium' => array(
			'width' => 480,
			'height' => 360
		),
		'mobiconnector_large' => array(
			'width' => 752,
			'height' => 564
		),
		'mobiconnector_x_large' => array(
			'width' => 1080,
			'height' => 810
		),
	);	
	public function __construct() {
		
		$this->register_routes();
		// add active create photo when save Posts
		add_action('wp_insert_post', array( $this, 'update_thumnail_mobile'), 100, 2);
	}
	/** tạo thumnail cho mobile *****/
	public function update_thumnail_mobile($post_ID, $post) {
		$post_thumbnail_id = get_post_thumbnail_id( $post );
		if(empty($post_thumbnail_id))
			return true;
		/// kiểm tra xem đã tồn tại thumnail chưa
		$mobiconnector_large = get_post_meta($post_thumbnail_id, 'mobiconnector_large', true);
		$mobiconnector_medium = get_post_meta($post_thumbnail_id, 'mobiconnector_medium', true);
		$mobiconnector_x_large = get_post_meta($post_thumbnail_id, 'mobiconnector_x_large', true);
		$mobiconnector_small = get_post_meta($post_thumbnail_id, 'mobiconnector_small', true);
		if(!empty($mobiconnector_medium) && !empty($mobiconnector_x_large) && !empty($mobiconnector_large) && !empty($mobiconnector_small))
			return true; // đã tồn tại rồi ko tạo nữa
		// lấy thông tin của ảnh
		$relative_pathto_file = get_post_meta( $post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$relative_pathto_file;
		// kiểm tra file gốc có tồn tại hay không?
		if(!file_exists($absolute_pathto_file))
			return true; // file ko tồn tại
		////////////////
		
		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// tạo ảnh 
		foreach($this->thumnails as $key => $value){
			$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
			$dest = $wp_upload_dir['basedir'].'/'.$path;
			MobiConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
			// cập nhật post meta for thumnail
			update_post_meta ($post_thumbnail_id, $key, $path);
			/* tạo ảnh base64
			$data = @file_get_contents($wp_upload_dir['basedir']."/".$path);
			$base64 = 'data:image/' . $ext . ';base64,' . base64_encode($data);
			update_post_meta ($post_thumbnail_id, $key.'_base64', $base64); */
		}
		return true;
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		// thêm trường ảnh tiêu biểu
		register_rest_field( 'post',
			'mobiconnector_feature_image',
			array(
				'get_callback'    => array($this, 'get_feature_image'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// lấy tổng số comment mỗi bài
		register_rest_field( 'post',
			'mobiconnector_total_comments',
			array(
				'get_callback'    => array($this, 'get_total_comments'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// lấy tên tác giả
		register_rest_field( 'post',
			'mobiconnector_author_name',
			array(
				'get_callback'    => array($this, 'get_author_name'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// đếm tổng số lượt view, sử dụng plugin: https://wordpress.org/plugins/post-views-counter
		// lấy tên tác giả
		register_rest_field( 'post',
			'mobiconnector_total_views',
			array(
				'get_callback'    => array($this, 'get_total_views'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// lấy tất cả comments của 1 bài Post
		register_rest_field( 'post',
			'mobiconnector_comments',
			array(
				'get_callback'    => array($this, 'get_comments'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// lấy 10 bài viết cùng category với Post
		register_rest_field( 'post',
			'mobiconnector_posts_incategory',
			array(
				'get_callback'    => array($this, 'get_posts_in_the_same_category'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	
	/**
	 * Handler for getting custom field data.
	 *
	 * @since 0.1.0
	 *
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function get_feature_image( $object, $field_name, $request) {
		
		// Only proceed if the post has a featured image.
		if ( ! empty( $object['featured_media'] ) ) {
			$image_id = (int)$object['featured_media'];
		} elseif ( ! empty( $object['featured_image'] ) ) {
			// This was added for backwards compatibility with < WP REST API v2 Beta 11.
			$image_id = (int)$object['featured_image'];
		} else {
			return null;
		}

		$image = get_post( $image_id );

		if ( ! $image ) {
			return null;
		}

		// This is taken from WP_REST_Attachments_Controller::prepare_item_for_response().
		$featured_image['id']            = $image_id;
		$featured_image['alt_text']      = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$featured_image['caption']       = $image->post_excerpt;
		$featured_image['description']   = $image->post_content;
		$featured_image['media_type']    = wp_attachment_is_image( $image_id ) ? 'image' : 'file';
		$featured_image['media_details'] = wp_get_attachment_metadata( $image_id );
		$featured_image['post']          = ! empty( $image->post_parent ) ? (int) $image->post_parent : null;
		$featured_image['source_url']    = wp_get_attachment_url( $image_id );
		// attached more thumnail
		
		// resize image
		$wp_upload_dir = wp_upload_dir();	
		
		$file_name = $wp_upload_dir['basedir']."/".$featured_image['media_details']['file'];
		// kiểm tra xem có ảnh thumnails cho Post chưa?
		$mobiconnector_large = get_post_meta($image_id, 'mobiconnector_large', true);
		$mobiconnector_medium = get_post_meta($image_id, 'mobiconnector_medium', true);
		$mobiconnector_x_large = get_post_meta($image_id, 'mobiconnector_x_large', true);
		$mobiconnector_small = get_post_meta($image_id, 'mobiconnector_small', true);
		if( empty($mobiconnector_large) || empty($mobiconnector_medium) || empty($mobiconnector_x_large) || empty($mobiconnector_small)) { // chưa tồn tại ảnh thì tạo
			$post_ID = $object['id'];
			$post = get_post($post_ID);
			$this->update_thumnail_mobile($post_ID, $post);
		}
		// gắn thumnail mới
		foreach($this->thumnails as $key => $value){
			$featured_image[$key] = $wp_upload_dir['baseurl']."/". get_post_meta($image_id, $key, true);
			//$featured_image[$key.'_base64'] = get_post_meta($image_id,$key.'_base64', true);
		}
		
		if ( empty( $featured_image['media_details'] ) ) {
			$featured_image['media_details'] = new stdClass;
		} elseif ( ! empty( $featured_image['media_details']['sizes'] ) ) {
			$img_url_basename = wp_basename( $featured_image['source_url'] );
			foreach ( $featured_image['media_details']['sizes'] as $size => &$size_data ) {
				$image_src = wp_get_attachment_image_src( $image_id, $size );
				if ( ! $image_src ) {
					continue;
				}
				$size_data['source_url'] = $image_src[0];
			}
		} elseif ( is_string( $featured_image['media_details'] ) ) {
			// This was added to work around conflicts with plugins that cause
			// wp_get_attachment_metadata() to return a string.
			$featured_image['media_details'] = new stdClass();
			$featured_image['media_details']->sizes = new stdClass();
		} else {
			$featured_image['media_details']['sizes'] = new stdClass;
		}

		return apply_filters( 'mobiconnector_rest_api_featured_image', $featured_image, $image_id );
	}
	// lấy tổng số comments
	public function get_total_comments($object, $field_name, $request) {
		$comments=(array) wp_count_comments( $object['id']);
		// chuyen thanh số nguyên
		if(!empty($comments)) {
			foreach($comments as &$item) {
				$item = absint($item);
			}
		}
		return $comments;
	}
	// lấy tên tác giả
	public function get_author_name ($object, $field_name, $request) {
		return get_the_author_meta('nicename',$object['author']);
	}
	// lấy tổng số view 
	public function get_total_views($object, $field_name, $request) {
		
		// nếu chưa cài đặt thì set 0
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
		//var_dump(is_plugin_active('post-views-counter/post-views-counter.php'));die;
		if(is_plugin_active('post-views-counter/post-views-counter.php') == false)
			return 0;
		
		global $wpdb;
		// get total post views
		$count = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT count
				FROM " . $wpdb->prefix . "post_views
				WHERE id = %d AND type = 4", absint( $object['id'] )
			)
		);
		
		return $count;
	}
	// lấy tất cả comments của 1 bài Post
	public function get_comments($object, $field_name, $request) {
		$args = array(
			'author_email' => '',
			'author__in' => '',
			'author__not_in' => '',
			'include_unapproved' => '',
			'fields' => '',
			'ID' => '',
			'comment__in' => '',
			'comment__not_in' => '',
			'karma' => '',
			'number' => '',
			'offset' => '',
			'orderby' => 'comment_date',
			'order' => 'DESC',
			'parent' => '',
			'post_author__in' => '',
			'post_author__not_in' => '',
			'post_ID' => '', // ignored (use post_id instead)
			'post_id' => absint( $object['id'] ),
			'post__in' => '',
			'post__not_in' => '',
			'post_author' => '',
			'post_name' => '',
			'post_parent' => '',
			'post_status' => '',
			'post_type' => '',
			'status' => 'approve',
			'type' => '',
				'type__in' => '',
				'type__not_in' => '',
			'user_id' => '',
			'search' => '',
			'count' => false,
			'meta_key' => '',
			'meta_value' => '',
			'meta_query' => '',
			'date_query' => null, // See WP_Date_Query
		);
		return get_comments( $args ); 		
	}
	/**  Lay 10 bai viet cung chuyen muc **/
	public function get_posts_in_the_same_category($object, $field_name, $request) {
		$parameters = $request->get_params();
		
		$post_category_per_page = isset($parameters['post_category_per_page']) ? absint($parameters['post_category_per_page']): 10;
		$post_category_page = isset($parameters['post_category_page']) ? absint($parameters['post_category_page']): 1;
		
		$args = array(
			'paged'             => $post_category_page,
			'posts_per_page'   => $post_category_per_page,
			'category'         => implode(',', wp_get_post_categories($object['id'])),
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => $object['id'],
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'post',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts_array = get_posts( $args );
		if(!empty($posts_array)) {
			foreach($posts_array as &$post) {
				$featured_media = get_post_thumbnail_id($post->ID);
				$post->mobiconnector_feature_image = $this->get_feature_image(array('featured_media'=>$featured_media),'mobiconnector_feature_image', $request);
			}
		}
		//echo '<pre>';var_dump($args);die;
		return $posts_array;
	}
}
$MobiConnectorPosts = new MobiConnectorPosts();
?>