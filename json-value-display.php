<?php
/**
 * Plugin Name: JSON Value Display
 * Plugin URI: https://example.com
 * Description: Display the color palette from theme.json on a settings page with circular color swatches, a gentle light border, added spacing, and a refined look. CSS is loaded from an external file.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: json-value-display
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') or die();
require_once plugin_dir_path(__FILE__) . 'primary-color-strip.php';

function jvd_add_admin_menu() {
    add_menu_page(
        __('JSON Value Display', 'json-value-display'),
        __('JSON Value Display', 'json-value-display'),
        'manage_options',
        'json-value-display',
        'jvd_display',
        'dashicons-art',
        66
    );
}

add_action('admin_menu', 'jvd_add_admin_menu');

function jvd_enqueue_styles($hook) {
    // Only add to our own page
    if ('toplevel_page_json-value-display' !== $hook) {
        return;
    }
    wp_enqueue_style('json-value-display-css', plugins_url('css/json-value-display.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'jvd_enqueue_styles');

function jvd_display() {
    $theme_json_path = get_template_directory() . '/theme.json';
    
    if (!file_exists($theme_json_path)) {
        echo '<div class="wrap"><h1>' . esc_html__('JSON Value Display', 'json-value-display') . '</h1><p>' . esc_html__('theme.json file not found.', 'json-value-display') . '</p></div>';
        return;
    }

    $theme_json_contents = file_get_contents($theme_json_path);
    $theme_json_data = json_decode($theme_json_contents, true);
    $color_palette = $theme_json_data['settings']['color']['palette'] ?? null;

    echo '<div class="wrap"><h1>' . esc_html__('JSON Value Display', 'json-value-display') . '</h1>';
    if (!empty($color_palette)) {
        echo '<ul>';
        foreach ($color_palette as $color) {
            $sanitized_name = sanitize_text_field($color['name']);
            $sanitized_color = sanitize_hex_color($color['color']);
            echo sprintf(
                '<li class="color-item"><span class="color-name">%s</span><div class="color-swatch" style="background-color:%s;"></div><pre class="color-json">%s</pre></li>',
                esc_html($sanitized_name),
                esc_attr($sanitized_color),    
                esc_html(json_encode($color)) // Output the JSON value beside the color
            );
        }
        echo '</ul>';
    } else {
        echo '<p>' . esc_html__('No color palette found in theme.json.', 'json-value-display') . '</p>';
    }
    echo '</div>';
}