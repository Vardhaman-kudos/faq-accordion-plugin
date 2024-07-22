<?php
/*
Plugin Name: FAQ Accordion
Description: Full-featured FAQ and accordion plugin, simple UI and easy-to-use FAQ blocks and shortcodes.
Version: 1.0
Author: KudosIntech
*/

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue styles and scripts
function faq_accordion_enqueue_scripts() {
    wp_enqueue_style('faq-accordion-faq', plugin_dir_url(__FILE__) . 'assets/css/faq.css');
    wp_enqueue_script('faq-accordion-custom', plugin_dir_url(__FILE__) . 'assets/js/accordion.js', array('jquery'), null, true);

    // Add custom CSS with settings
    $title_color = get_option('faq_accordion_title_color', '#000000');
    $active_color = get_option('faq_accordion_active_color', '#000000');
    $default_bg_color = get_option('faq_accordion_default_bg_color', '#ffffff');
    $active_bg_color = get_option('faq_accordion_active_bg_color', '#f0f0f0');
    $icon_shape_default_bg_color = get_option('faq_accordion_icon_shape_default_bg_color', '#cccccc');
    $icon_shape_active_bg_color = get_option('faq_accordion_icon_shape_active_bg_color', '#aaaaaa');
    $enable_border = get_option('faq_accordion_enable_border', 'no');
    $border_color = get_option('faq_accordion_border_color', '#cccccc');

    $custom_css = "
        .faq_container {
            " . ($enable_border == 'yes' ? 'border-bottom: 1px solid ' . $border_color . ';' : '') . "
        }
    ";

    if (!empty($icon_shape_default_bg_color)) {
        $custom_css .= "
        .faq_container .icon .icon-shape::before,
        .faq_container .icon .icon-shape::after {
            background-color: {$icon_shape_default_bg_color};
        }";
    }

    if (!empty($icon_shape_active_bg_color)) {
        $custom_css .= "
        .faq_container.active .icon .icon-shape::before,
        .faq_container.active .icon .icon-shape::after {
            background-color: {$icon_shape_active_bg_color};
        }";
    }

    if (!empty($active_color)) {
        $custom_css .= "
        .faq_container.active .faq_question-text {
            color: {$active_color};
        }";
    }

    if (!empty($title_color)) {
        $custom_css .= "
        .faq_question-text {
            color: {$title_color};
        }";
    }

    if (!empty($default_bg_color)) {
        $custom_css .= "
        .faq_container .faq_question {
            background-color: {$default_bg_color};
        }";
    }

    if (!empty($active_bg_color)) {
        $custom_css .= "
        .faq_container.active .faq_question {
            background-color: {$active_bg_color};
        }";
    }

    wp_add_inline_style('faq-accordion-faq', $custom_css);
}
add_action('wp_enqueue_scripts', 'faq_accordion_enqueue_scripts');

// Register Custom Post Type for FAQ
function faq_accordion_register_cpt() {
    $labels = array(
        'name'               => _x('FAQs', 'post type general name', 'faq-accordion'),
        'singular_name'      => _x('FAQ', 'post type singular name', 'faq-accordion'),
        'menu_name'          => _x('FAQs', 'admin menu', 'faq-accordion'),
        'name_admin_bar'     => _x('FAQ', 'add new on admin bar', 'faq-accordion'),
        'add_new'            => _x('Add New', 'faq', 'faq-accordion'),
        'add_new_item'       => __('Add New FAQ', 'faq-accordion'),
        'new_item'           => __('New FAQ', 'faq-accordion'),
        'edit_item'          => __('Edit FAQ', 'faq-accordion'),
        'view_item'          => __('View FAQ', 'faq-accordion'),
        'all_items'          => __('All FAQs', 'faq-accordion'),
        'search_items'       => __('Search FAQs', 'faq-accordion'),
        'parent_item_colon'  => __('Parent FAQs:', 'faq-accordion'),
        'not_found'          => __('No FAQs found.', 'faq-accordion'),
        'not_found_in_trash' => __('No FAQs found in Trash.', 'faq-accordion')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'faq'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor'),
        'taxonomies'         => array('faq_category')
    );

    register_post_type('faq', $args);
}
add_action('init', 'faq_accordion_register_cpt');

// Register Custom Taxonomy for FAQ Category
function faq_accordion_register_taxonomy() {
    $labels = array(
        'name'              => _x('FAQ Categories', 'taxonomy general name', 'faq-accordion'),
        'singular_name'     => _x('FAQ Category', 'taxonomy singular name', 'faq-accordion'),
        'search_items'      => __('Search FAQ Categories', 'faq-accordion'),
        'all_items'         => __('All FAQ Categories', 'faq-accordion'),
        'parent_item'       => __('Parent FAQ Category', 'faq-accordion'),
        'parent_item_colon' => __('Parent FAQ Category:', 'faq-accordion'),
        'edit_item'         => __('Edit FAQ Category', 'faq-accordion'),
        'update_item'       => __('Update FAQ Category', 'faq-accordion'),
        'add_new_item'      => __('Add New FAQ Category', 'faq-accordion'),
        'new_item_name'     => __('New FAQ Category Name', 'faq-accordion'),
        'menu_name'         => __('FAQ Categories', 'faq-accordion'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq-category'),
    );

    register_taxonomy('faq_category', array('faq'), $args);
}
add_action('init', 'faq_accordion_register_taxonomy');

// Add Custom Settings Submenu for FAQ Accordion
function faq_accordion_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=faq',
        'FAQ Accordion Settings',
        'Accordion Settings',
        'manage_options',
        'faq-accordion-settings',
        'faq_accordion_settings_page'
    );
}
add_action('admin_menu', 'faq_accordion_settings_submenu');

// Render Settings Page
function faq_accordion_settings_page() {
    ?>
    <div class="wrap">
        <h1>FAQ Accordion Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('faq_accordion_settings_group');
            do_settings_sections('faq-accordion-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register Settings
function faq_accordion_register_settings() {
    register_setting('faq_accordion_settings_group', 'faq_accordion_title_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_active_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_default_bg_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_active_bg_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_icon_shape_default_bg_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_icon_shape_active_bg_color');
    register_setting('faq_accordion_settings_group', 'faq_accordion_enable_border');
    register_setting('faq_accordion_settings_group', 'faq_accordion_border_color');

    add_settings_section(
        'faq_accordion_settings_section',
        'Accordion Settings',
        'faq_accordion_settings_section_callback',
        'faq-accordion-settings'
    );

    add_settings_field(
        'faq_accordion_title_color',
        'Accordion Title Color',
        'faq_accordion_title_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_active_color',
        'Accordion Title Active Color',
        'faq_accordion_active_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_default_bg_color',
        'Accordion Default Background Color',
        'faq_accordion_default_bg_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_active_bg_color',
        'Accordion Active Background Color',
        'faq_accordion_active_bg_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_icon_shape_default_bg_color',
        'Icon Shape Default Background Color',
        'faq_accordion_icon_shape_default_bg_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_icon_shape_active_bg_color',
        'Icon Shape Active Background Color',
        'faq_accordion_icon_shape_active_bg_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_enable_border',
        'Enable Accordion Border',
        'faq_accordion_enable_border_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );

    add_settings_field(
        'faq_accordion_border_color',
        'Accordion Border Color',
        'faq_accordion_border_color_callback',
        'faq-accordion-settings',
        'faq_accordion_settings_section'
    );
}
add_action('admin_init', 'faq_accordion_register_settings');

// Section Callback
function faq_accordion_settings_section_callback() {
    echo 'Customize the appearance of the FAQ accordion.';
}

// Title Color Field Callback
function faq_accordion_title_color_callback() {
    $title_color = get_option('faq_accordion_title_color', '#000000');
    echo '<input type="text" id="faq_accordion_title_color" name="faq_accordion_title_color" value="' . esc_attr($title_color) . '" class="faq-accordion-color-picker" />';
}

// Active Color Field Callback
function faq_accordion_active_color_callback() {
    $active_color = get_option('faq_accordion_active_color', '#000000');
    echo '<input type="text" id="faq_accordion_active_color" name="faq_accordion_active_color" value="' . esc_attr($active_color) . '" class="faq-accordion-color-picker" />';
}

// Default Background Color Field Callback
function faq_accordion_default_bg_color_callback() {
    $default_bg_color = get_option('faq_accordion_default_bg_color', '#ffffff');
    echo '<input type="text" id="faq_accordion_default_bg_color" name="faq_accordion_default_bg_color" value="' . esc_attr($default_bg_color) . '" class="faq-accordion-color-picker" />';
}

// Active Background Color Field Callback
function faq_accordion_active_bg_color_callback() {
    $active_bg_color = get_option('faq_accordion_active_bg_color', '#f0f0f0');
    echo '<input type="text" id="faq_accordion_active_bg_color" name="faq_accordion_active_bg_color" value="' . esc_attr($active_bg_color) . '" class="faq-accordion-color-picker" />';
}

// Icon Shape Default Background Color Field Callback
function faq_accordion_icon_shape_default_bg_color_callback() {
    $icon_shape_default_bg_color = get_option('faq_accordion_icon_shape_default_bg_color', '#cccccc');
    echo '<input type="text" id="faq_accordion_icon_shape_default_bg_color" name="faq_accordion_icon_shape_default_bg_color" value="' . esc_attr($icon_shape_default_bg_color) . '" class="faq-accordion-color-picker" />';
}

// Icon Shape Active Background Color Field Callback
function faq_accordion_icon_shape_active_bg_color_callback() {
    $icon_shape_active_bg_color = get_option('faq_accordion_icon_shape_active_bg_color', '#aaaaaa');
    echo '<input type="text" id="faq_accordion_icon_shape_active_bg_color" name="faq_accordion_icon_shape_active_bg_color" value="' . esc_attr($icon_shape_active_bg_color) . '" class="faq-accordion-color-picker" />';
}

// Enable Border Field Callback
function faq_accordion_enable_border_callback() {
    $enable_border = get_option('faq_accordion_enable_border', 'no');
    echo '<input type="checkbox" id="faq_accordion_enable_border" name="faq_accordion_enable_border" value="yes"' . checked($enable_border, 'yes', false) . ' />';
}

// Border Color Field Callback
function faq_accordion_border_color_callback() {
    $border_color = get_option('faq_accordion_border_color', '#cccccc');
    echo '<input type="text" id="faq_accordion_border_color" name="faq_accordion_border_color" value="' . esc_attr($border_color) . '" class="faq-accordion-color-picker" />';
}

// Enqueue color picker scripts in admin
function faq_accordion_admin_scripts($hook_suffix) {
    if ('faq_page_faq-accordion-settings' !== $hook_suffix) {
        return;
    }
}
add_action('admin_enqueue_scripts', 'faq_accordion_admin_scripts');

// Shortcode for FAQ Accordion
function faq_accordion_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'category' => '',
        ),
        $atts
    );

    // query args based on whether a category is specified
    $args = array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
    );

    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'faq_category',
                'field' => 'slug',
                'terms' => $atts['category'],
            ),
        );
    }

    $faqs = new WP_Query($args);

    $default_bg_color = get_option('faq_accordion_default_bg_color', '#ffffff');

    ob_start();
    ?>
        <div class="faq_main_container">
            <?php if ($faqs->have_posts()) : ?>
                <?php while ($faqs->have_posts()) : $faqs->the_post(); ?>
                    <div class="faq_container<?php echo $default_bg_color ? ' has-background' : ''; ?>">
                        <div class="faq_question">
                            <div class="faq_question-text">
                                <?php the_title(); ?>
                            </div>
                            <div class="icon">
                                <div class="icon-shape"></div>
                            </div>
                        </div>
                        <div class="answercont">
                            <div class="answer">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <p><?php _e('No FAQs found.', 'faq-accordion'); ?></p>
            <?php endif; ?>
        </div>
    <?php
    return ob_get_clean();
}
add_shortcode('faq_accordion', 'faq_accordion_shortcode');

// Add a new column to the FAQ category list
function faq_accordion_add_shortcode_column($columns) {
    $columns['faq_shortcode'] = __('Shortcode', 'faq-accordion');
    return $columns;
}
add_filter('manage_edit-faq_category_columns', 'faq_accordion_add_shortcode_column');

// Populate the new column with the shortcode
function faq_accordion_shortcode_column_content($content, $column_name, $term_id) {
    if ($column_name === 'faq_shortcode') {
        $term = get_term($term_id);
        $shortcode = '[faq_accordion category="' . $term->slug . '"]';
        $content = '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr($shortcode) . '" class="large-text code">';
    }
    return $content;
}
add_filter('manage_faq_category_custom_column', 'faq_accordion_shortcode_column_content', 10, 3);
?>
