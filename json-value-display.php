<?php
/**
 * Plugin Name: JSON Value Display
 * Description: Display the color palette from theme.json on a settings page with circular color swatches, a gentle light border, added spacing, and a refined look. CSS is loaded from an external file.
 * Version: 1.0
 * Author: Your Name
 */

defined('ABSPATH') or die();
require_once plugin_dir_path(__FILE__) . 'primary-color-strip.php';

function json_value_display_add_admin_menu() {
    add_menu_page(
        'JSON Value Display',
        'JSON Value Display',
        'manage_options',
        'json-value-display',
        'json_value_display',
        'dashicons-art',
        66
    );
}

add_action('admin_menu', 'json_value_display_add_admin_menu');

function json_value_display_enqueue_styles($hook) {
    // Only add to our own page
    if ('toplevel_page_json-value-display' !== $hook) {
        return;
    }
    wp_enqueue_style('json-value-display-css', plugins_url('json-value-display.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'json_value_display_enqueue_styles');

function json_value_display() {
    $theme_json_path = get_template_directory() . '/theme.json';
    
    if (!file_exists($theme_json_path)) {
        echo '<div class="wrap"><h1>JSON Value Display</h1><p>theme.json file not found.</p></div>';
        return;
    }

    $theme_json_contents = file_get_contents($theme_json_path);
    $theme_json_data = json_decode($theme_json_contents, true);
    $color_palette = $theme_json_data['settings']['color']['palette'] ?? null;

    echo '<div class="wrap"><h1>JSON Value Display</h1>';
    if (!empty($color_palette)) {
        echo '<ul>';
        foreach ($color_palette as $color) {
            echo sprintf(
                '<li class="color-item"><span class="color-name">%s</span><div class="color-swatch" style="background-color:%s;"></div><pre class="color-json">%s</pre></li>',
                esc_html($color['name']),
                esc_attr($color['color']),    
                esc_html(json_encode($color)) // Output the JSON value beside the color
            );
        }
        echo '</ul>';
    } else {
        echo '<p>No color palette found in theme.json.</p>';
    }
    echo '</div>';
}