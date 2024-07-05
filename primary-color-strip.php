<?php
/**
 * Get the theme color from the theme.json file.
 *
 * @param string $color_type The type of color to retrieve (default: 'primary').
 * @return string|null The theme color or null if not found.
 */
function jvd_get_theme_color($color_type = 'primary') {
    $theme_json_path = get_theme_file_path('theme.json');
    if (!file_exists($theme_json_path)) {
        error_log('Theme JSON file not found.');
        return null;
    }

    $theme_json_data = json_decode(file_get_contents($theme_json_path), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to decode theme.json: ' . json_last_error_msg());
        return null;
    }

    $found_color = null;
    foreach ($theme_json_data as $key => $value) {
        if (is_array($value)) {
            $found_color = jvd_get_color_recursive($value, $color_type);
            if ($found_color) {
                break;
            }
        }
    }

    if ($found_color) {
        error_log(ucfirst($color_type) . ' Color Found: ' . $found_color);
        return $found_color;
    } else {
        error_log(ucfirst($color_type) . ' color not found in theme.json.');
        return null;
    }
}

/**
 * Recursively search for the color in the data array.
 *
 * @param array $data The data array to search.
 * @param string $color_type The type of color to search for.
 * @return string|null The color value or null if not found.
 */
function jvd_get_color_recursive($data, $color_type) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $found_color = jvd_get_color_recursive($value, $color_type);
            if ($found_color) {
                return $found_color;
            }
        } elseif (($key === 'slug' || $key === 'name') && strpos(strtolower($value), $color_type) !== false) {
            return $data['color'] ?? null;
        }
    }
    return null;
}

/**
 * Enqueue styles and add inline CSS based on the theme colors.
 */
function jvd_primary_color_strip_enqueue_styles() {
    $primary_color = jvd_get_theme_color('primary');
    $accent_color = jvd_get_theme_color('accent');

    if ($primary_color || $accent_color) {
        wp_enqueue_style('jvd-primary-color-strip', plugin_dir_url(__FILE__) . 'css/primary-color-strip.css');
        $custom_css = "";
        if ($primary_color) {
            $custom_css .= "#primary-color-strip { background-color: " . esc_attr($primary_color) . "; }";
        }
        if ($accent_color) {
            $custom_css .= " #accent-color-strip { background-color: " . esc_attr($accent_color) . "; }";
        }
        wp_add_inline_style('jvd-primary-color-strip', $custom_css);
    }
}
add_action('wp_enqueue_scripts', 'jvd_primary_color_strip_enqueue_styles');

/**
 * Display the primary color strip in the footer.
 */
function jvd_display_primary_color_strip() {
    $primary_color = jvd_get_theme_color('primary');
    $accent_color = jvd_get_theme_color('accent');

    $color_to_use = $primary_color ?: $accent_color;

    error_log('jvd_display_primary_color_strip called. Color used: ' . ($color_to_use ?: 'None'));

    if ($color_to_use) {
        echo '<div id="primary-color-strip" style="background-color: ' . esc_attr($color_to_use) . ';"><a href="#footer">Click here</a> to go to the footer.</div>';
    } else {
        echo '<div id="primary-color-strip">No suitable color found.</div>';
    }
}
add_action('wp_footer', 'jvd_display_primary_color_strip');