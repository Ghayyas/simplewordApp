=== WPCustom Category Image ===
Tags: wordpress, category, wordpress category image , custom category image
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Requires at least: 3.5
Tested up to: 4.5.2
Stable tag: 2.1.5
Donate link: https://goo.gl/puwy8c

The WPCustom Category Image plugin allow users to upload their very own custom category image.

== Description ==

The **WP Custom Category Image** plugin allow users to upload their very own custom category (taxonomy) image to obtain a much more personalized look and feel.

**Requires WordPress 3.0+ and PHP 5.3+**

Questions? Suggestions? ....? [twitter.com/eduardostuart](https://twitter.com/eduardostuart)

= Usage =

Go to `Wp-Admin -> Posts(or post type) -> Categories (or taxonomy)` to see Custom Category Image options

**Example #1**
[gist.github.com/eduardostuart/b88d6845a1afb78c296c](https://gist.github.com/eduardostuart/b88d6845a1afb78c296c)

**Example #2 (Shortcode)**
You can use a shortcode now (since 2.1.0):

    echo do_shortcode('[wp_custom_image_category onlysrc="false" size="full" term_id="123" alt="alt :)"]');


**Example #3**

    foreach( get_categories(['hide_empty' => false]) as $category) {
        echo $category->name . '<br>';
        echo do_shortcode(sprintf('[wp_custom_image_category term_id="%s"]',$category->term_id));
    }


== Installation ==

You can either install it automatically from the WordPress admin, or do it manually:

1. Unzip the archive and put the `wpcustom-category-image` folder into your plugins folder (/wp-content/plugins/).
2. Activate the plugin from the Plugins menu.

== Frequently Asked Questions ==

= T_STATIC Error =

If you see this: `Parse error: syntax error, unexpected T_STATIC, expecting T_STRING or T_VARIABLE or '$'`
you are using a PHP 5.3 < Version.

== Screenshots ==

1. Add New Category
2. Edit Category
3. Choose Category Image

== Changelog ==

1. v2.1.1

- Bugfixes
- Pt_BR translation

1. v2.1.0

- Bugfixes
- Shortcode added!

1. v1.1.3

- Bug fixes; Thanks to @webkupas and @iranodust.
- Added custom images to custom taxonomies edit page.

1. v1.1.2

- Bug fixes; Thanks: Thiago Otaviani;

1. v1.1.0

- Bug fixes;
- Display current image (admin);

1. v1.0.1 Bug fixes