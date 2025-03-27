<?php
/**
 * Plugin Name: Custom Slick Slider Settings
 * Description: Adds settings to customize Slick slider dots, arrows, autoplay, and title display. Use Shortcode [custom_post_slider]
 * Version: 1.0
 * Author: Shubham Dixit
 */

if (!defined('ABSPATH')) exit;

// Enqueue Slick CSS & JS
function enqueue_slick_slider() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css');
    // wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js', array('jquery'), null, true);
    wp_enqueue_style('custom-slick-css', plugin_dir_url(__FILE__) . 'css/custom-slick.css');

    // Localize settings to JS
    $slider_settings = array(
        'post_type' => get_option('post_type', 'gallery'),
        'image_height' => get_option('slick_image_height', '400'),
        'dots' => get_option('slick_show_dots', '1'),
        'arrows' => get_option('slick_show_arrows', '1'),
        'autoplay' => get_option('slick_autoplay', '1'),
        'show_title' => get_option('slick_show_title', '0'),
        'title_color' => get_option('slick_title_color', '#ffffff'),
        'dot_color' => get_option('slick_dot_color', '#aaaaaa'),
        'active_dot' => get_option('slick_active_dot_color', '#787d33'),
        'arrow_image' => get_option('slick_arrow', plugin_dir_url(__FILE__) . 'img/default-arrow.png')
    );

$arrow_image = get_option('slick_arrow', '');
if (empty($arrow_image)) {
    $arrow_image = plugin_dir_url(__FILE__) . 'img/default-arrow.png'; // Default fallback
}

    wp_enqueue_script('custom-slick-js', plugin_dir_url(__FILE__) . 'js/custom-slick.js', array('jquery', 'slick-js'), null, true);
    wp_localize_script('custom-slick-js', 'slickSliderOptions', $slider_settings);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider');

function fetch_categories_by_post_type() {
    if (!isset($_POST['post_type'])) {
        wp_die();
    }

    $post_type = sanitize_text_field($_POST['post_type']);
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $taxonomy = !empty($taxonomies) ? array_key_first($taxonomies) : 'category';
    $categories = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);

    if (!empty($categories)) {
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
        }
    } else {
        echo '<option value="">No categories found</option>';
    }

    wp_die();
}
add_action('wp_ajax_fetch_categories_by_post_type', 'fetch_categories_by_post_type');


//Color Picker
function slick_slider_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_custom-slick-slider-settings') {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('slick-slider-color-picker', plugin_dir_url(__FILE__) . 'js/color-picker.js', array('wp-color-picker', 'jquery'), false, true);
}
add_action('admin_enqueue_scripts', 'slick_slider_enqueue_admin_scripts');

//
function load_wp_media_files($hook) {
    if ($hook !== 'toplevel_page_custom-slick-slider-settings') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('admin-custom-js', plugin_dir_url(__FILE__) . 'js/admin-custom.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'load_wp_media_files');

// Shortcode for slider
function custom_post_slider() {
    $post_type = get_option('post_type', 'gallery');
    $selected_categories = get_option('slick_selected_categories', []);

    if (!is_array($selected_categories) || empty($selected_categories)) {
        $selected_categories = [];
    }

    $args = array(
        'post_type'      => $post_type,
        'posts_per_page' => 5,
        'post_status'    => 'publish',
    );

    if (!empty($selected_categories)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => get_object_taxonomies($post_type)[0],
                'field'    => 'term_id',
                'terms'    => $selected_categories,
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start(); ?>
        <div class="custom-slider-container">
            <div class="custom-slider">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="slide">
                        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title(); ?>">
                        <?php if (get_option('slick_show_title', '0')): ?>
                            <div class="slide-overlay-text"><?php the_title(); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- <button id="slick-play-pause" class="slick-play">❚❚</button>  -->
        </div>
        <?php wp_reset_postdata();
        return ob_get_clean();
    }
}
add_shortcode('custom_post_slider', 'custom_post_slider');



// Admin Menu for Settings
function slick_slider_settings_menu() {
    add_menu_page('Custom Slick Slider Settings', 'Custom Slick Slider', 'manage_options', 'custom-slick-slider-settings', 'custom_slick_slider_settings_page');
}
add_action('admin_menu', 'slick_slider_settings_menu');


// Admin Page
function custom_slick_slider_settings_page() {
    $post_types = get_post_types(array('public' => true), 'objects'); ?>
    <div class="wrap">
        <h2>Custom Slick Slider Settings</h2>
        <form method="post" action="options.php" class="slick-slider-settings-form">
            <?php settings_fields('slick-slider-settings-group');
            do_settings_sections('slick-slider-settings-group'); ?>

            <!-- Post Settings Section -->
            <div class="settings-section">
                <h3>Post Settings</h3>

                <label>
                    <span>Post Type</span>
                    <select name="post_type" id="post_type_selector">
                        <?php foreach ($post_types as $post_type): ?>
                            <option value="<?php echo $post_type->name; ?>" <?php selected(get_option('post_type'), $post_type->name); ?>>
                                <?php echo $post_type->label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <span>Categories to Display</span>
                    <select name="slick_selected_categories[]" id="category_selector" multiple style="width: 100%;">
                        <?php 
                        $selected_post_type = get_option('post_type', 'post');
                        $taxonomies = get_object_taxonomies($selected_post_type, 'objects');
                        $taxonomy = !empty($taxonomies) ? array_key_first($taxonomies) : 'category';
                        $categories = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);

                        $selected_categories = get_option('slick_selected_categories', []);
                        if (!is_array($selected_categories)) {
                            $selected_categories = [];
                        }

                        foreach ($categories as $category): ?>
                            <option value="<?php echo $category->term_id; ?>" <?php echo in_array($category->term_id, $selected_categories) ? 'selected' : ''; ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>


            </div>

            <!-- Display Options -->
            <div class="settings-section">
                <h3>
                    <input type="checkbox" name="slick_show_title" value="1" <?php checked(1, get_option('slick_show_title', 0)); ?>>
                    Show Post Title
                </h3>

                <div class="wp-picker-container">
                    <label for="slick_title_color">Post Title Color</label>
                    <input type="text" class="color-picker" id="slick_title_color" name="slick_title_color" value="<?php echo esc_attr(get_option('slick_title_color', '#ffffff')); ?>" data-default-color="#ffffff">
                </div>
            </div>

            <!-- Slider Settings -->
            <div class="settings-section">
                <h3>Slider Settings</h3>

                <label>
                    <input type="checkbox" name="slick_autoplay" value="1" <?php checked(1, get_option('slick_autoplay', 1)); ?>>
                    Autoplay Slides
                </label>

                <label>
                    <span>Slider Image Height (px)</span>
                    <input type="number" name="slick_image_height" value="<?php echo esc_attr(get_option('slick_image_height', '400')); ?>" min="100" max="1000">
                </label>
            </div>

            <!-- Dots Settings -->
            <div class="settings-section">
                <h3>
                    <input type="checkbox" name="slick_show_dots" value="1" <?php checked(1, get_option('slick_show_dots', 1)); ?>>
                    Show Slider Dots
                </h3>

                <div class="wp-picker-container">
                    <label for="slick_dot_color">Inactive Dot Color</label>
                    <input type="text" class="color-picker" id="slick_dot_color" name="slick_dot_color" value="<?php echo esc_attr(get_option('slick_dot_color', '#aaaaaa')); ?>" data-default-color="#aaaaaa">
                </div>

                <div class="wp-picker-container">
                    <label for="slick_active_dot_color">Active Dot Color</label>
                    <input type="text" class="color-picker" id="slick_active_dot_color" name="slick_active_dot_color" value="<?php echo esc_attr(get_option('slick_active_dot_color', '#787d33')); ?>" data-default-color="#787d33">
                </div>
            </div>

            <!-- Arrow Settings -->
            <div class="settings-section">
                <h3>
                    <input type="checkbox" name="slick_show_arrows" value="1" <?php checked(1, get_option('slick_show_arrows', 1)); ?>>
                    Show Slider Arrows
                </h3>

                <label>
                    <span>Arrow Image</span>
                    <input type="hidden" id="slick_arrow" name="slick_arrow" value="<?php echo esc_attr(get_option('slick_arrow')); ?>" />
                    <button type="button" class="button upload_arrow_button">Upload Custom Arrow Image</button>
                    <img id="slick_arrow_preview" src="<?php echo esc_attr(get_option('slick_arrow')); ?>" style="width: 30px; <?php echo get_option('slick_arrow') ? '' : 'display:none;'; ?>" />
                    <button type="button" class="button remove_arrow_button">Remove</button>
                </label>
            </div>

            <?php submit_button(); ?>
        </form>


<style type="text/css">
    .slick-slider-settings-form {
    max-width: 600px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.settings-section {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f9f9f9;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.settings-section h3 {
    margin-top: 0;
    font-size: 18px;
    width: 100%;
}

.settings-section label {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    margin-right: 10px;
    font-weight: 500; 
}

.settings-section input,
.settings-section select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    /*width: 100%;
    max-width: 400px;*/
}
.wp-picker-container{ display: flex; }


</style>

    </div>
<?php }
function slick_slider_register_settings() {
    register_setting('slick-slider-settings-group', 'post_type');
    register_setting('slick-slider-settings-group', 'slick_selected_categories');
    register_setting('slick-slider-settings-group', 'slick_image_height');
    register_setting('slick-slider-settings-group', 'slick_show_title');
    register_setting('slick-slider-settings-group', 'slick_title_color');
    register_setting('slick-slider-settings-group', 'slick_show_dots');
    register_setting('slick-slider-settings-group', 'slick_show_arrows');
    register_setting('slick-slider-settings-group', 'slick_arrow', array('sanitize_callback' => 'sanitize_text_field'));
    register_setting('slick-slider-settings-group', 'slick_autoplay');
    register_setting('slick-slider-settings-group', 'slick_dot_color');
    register_setting('slick-slider-settings-group', 'slick_active_dot_color');
}
add_action('admin_init', 'slick_slider_register_settings');
