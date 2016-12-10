<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class WPCustomCategoryImage
{


    protected $taxonomies;

    public static function activate()
    {
        if (WPCCI_WP_VERSION < WPCCI_WP_MIN_VERSION || version_compare(PHP_VERSION, WPCCI_MIN_PHP_VERSION, '<')) {
            // NO GOD! PLEASE NO!!! NOOOOOOOOOO
            $message = '<a href="http://www.youtube.com/watch?v=umDr0mPuyQc" target="_blank">';
            $message.= __('Sorry, WPCustom Category Image works only under Wordpress 3.5 or higher', 'wpcustom-category-image');
            $message.= '<br>';
            $message.= __('And... PHP ', 'wpcustom-category-image') . WPCCI_MIN_PHP_VERSION;
            $message.= '</a>';

            wpcci_error($message, E_USER_ERROR);
        }
    }

    public static function deactivate()
    {
        delete_option('wpcustom_notice', 0);
    }

    // initialize wp custom category image
    public static function initialize()
    {
        $instance = new self();

        add_shortcode('wp_custom_image_category', array($instance, 'shortcode'));

        add_action('admin_init', array($instance, 'admin_init'));
        add_action('admin_enqueue_scripts', array($instance, 'admin_enqueue_assets'));
        add_action('edit_term', array($instance, 'save_image'));
        add_action('create_term', array($instance, 'save_image'));
        add_action('admin_notices', array($instance, 'admin_notice'));
    }

    public static function shortcode($atts)
    {
        $args = shortcode_atts(array(
                'size'    => 'full',
                'term_id' => null,
                'alt'     => null,
                'onlysrc' => false
        ), $atts);

        return self::get_category_image(array(
            'size'    => $args['size'],
            'term_id' => $args['term_id'],
            'alt'     => $args['alt']
        ), (boolean) $args['onlysrc']);
    }

    public static function get_category_image($atts = array(), $onlysrc = false)
    {
        $params = array_merge(array(
                'size'    => 'full',
                'term_id' => null,
                'alt'     => null
        ), $atts);

        $term_id = $params['term_id'];
        $size    = $params['size'];

        if (! $term_id) {
            if (is_category()) {
                $term_id = get_query_var('cat');
            } elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        if (!$term_id) {
            return;
        }

        $attachment_id   = get_option('categoryimage_'.$term_id);

        $attachment_meta = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $attachment_alt  = trim(strip_tags($attachment_meta));

        $attr = array(
            'alt'=> (is_null($params['alt']) ?  $attachment_alt : $params['alt'])
        );

        if ($onlysrc == true) {
            $src = wp_get_attachment_image_src($attachment_id, $size, false);
            return is_array($src) ? $src[0] : null;
        }

        return wp_get_attachment_image($attachment_id, $size, false, $attr);
    }

    public function admin_notice()
    {
        if (get_option('wpcustom_notice', false)  !== false) {
            return;
        }

        $html = '<div class="updated">';
        $html .= '<p>';
        $html .= sprintf('<strong>WPCustom Category Image</strong> - <a href="https://goo.gl/zG18dh" target="_blank">%s</a>',
                         __('If you like this plugin, then please leave us a good rating and review.', 'wpcustom-category-image'));
        $html .= '</p>';
        $html .= sprintf('<p><small>%s <a href="http://github.com/eduardostuart" target="_blank">Eduardo Stuart</a></small></p>',
                        __('Developed with &#9829; by', 'wpcustom-category-image'));
        $html .= '</div>';

        echo $html;

        update_option('wpcustom_notice', 1);
    }

    public function manage_category_columns($columns)
    {
        $columns['image'] = __('Image', 'wpcustom-category-image');
        return $columns;
    }

    public function manage_category_columns_fields($deprecated, $column_name, $term_id)
    {
        if ($column_name == 'image' && $this->has_image($term_id)) {
            echo self::get_category_image(array(
                'term_id' => $term_id,
                'size'    => 'thumbnail',
            ));
        }
    }

    public function admin_init()
    {
        $this->taxonomies = get_taxonomies();

        add_filter('manage_edit-category_columns', array($this, 'manage_category_columns'));
        add_filter('manage_category_custom_column', array($this, 'manage_category_columns_fields'), 10, 3);

        foreach ((array) $this->taxonomies as $taxonomy) {
            $this->add_custom_column_fields($taxonomy);
        }
    }

    public function add_custom_column_fields($taxonomy)
    {
        add_action("{$taxonomy}_add_form_fields", array($this, 'add_taxonomy_field'));
        add_action("{$taxonomy}_edit_form_fields", array($this, 'edit_taxonomy_field'));

        // Add custom columns to custom taxonomies
        add_filter("manage_edit-{$taxonomy}_columns", array($this, 'manage_category_columns'));
        add_filter("manage_{$taxonomy}_custom_column", array($this, 'manage_category_columns_fields'), 10, 3);
    }

    /**
     * Enqueue assets into admin
     */
    public function admin_enqueue_assets($hook)
    {
        if ($hook != 'edit-tags.php' && $hook != 'term.php') {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_script(
            'category-image-js',
            plugins_url('/js/categoryimage.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'category-image-js',
            'CategoryImage',
            array(
                'wp_version' => WPCCI_WP_VERSION,
                'label'      => array(
                    'title'  => __('Choose Category Image', 'wpcustom-category-image'),
                    'button' => __('Choose Image', 'wpcustom-category-image')
                )
            )
        );

        wp_enqueue_style(
            'category-image-css',
            plugins_url('/css/categoryimage.css', __FILE__)
        );
    }

    public function save_image($term_id)
    {
        $attachment_id = isset($_POST['categoryimage_attachment']) ? (int) $_POST['categoryimage_attachment'] : null;

        if (! is_null($attachment_id) && $attachment_id > 0 && !empty($attachment_id)) {
            update_option('categoryimage_'.$term_id, $attachment_id);
            return;
        }

        delete_option('categoryimage_'.$term_id);
    }

    public function get_attachment_id($term_id)
    {
        return get_option('categoryimage_'.$term_id);
    }

    public function has_image($term_id)
    {
        return ($this->get_attachment_id($term_id) !== false);
    }

    public function add_taxonomy_field($taxonomy)
    {
        echo $this->taxonomy_field('add-form-option-image', $taxonomy);
    }

    public function edit_taxonomy_field($taxonomy)
    {
        echo $this->taxonomy_field('edit-form-option-image', $taxonomy);
    }

    public function taxonomy_field($template, $taxonomy)
    {
        $params = array(
            'label'  => array(
                'image'        => __('Image', 'wpcustom-category-image'),
                'upload_image' => __('Upload/Edit Image', 'wpcustom-category-image'),
                'remove_image' => __('Remove image', 'wpcustom-category-image')
            ),
            'categoryimage_attachment' => null
        );


        if (isset($taxonomy->term_id) && $this->has_image($taxonomy->term_id)) {
            $image = self::get_category_image(array(
                'term_id' => $taxonomy->term_id
            ), true);

            $attachment_id = $this->get_attachment_id($taxonomy->term_id);

            $params = array_replace_recursive($params, array(
                'categoryimage_image'      => $image,
                'categoryimage_attachment' => $attachment_id,
            ));
        }

        return ___template($template, $params, false);
    }
}
