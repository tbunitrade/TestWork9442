<?php

function register_city_weather_block() {
    wp_register_script(
        'city-weather-block',
        get_stylesheet_directory_uri() . '/blocks/city-weather-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor')
    );

    register_block_type('storefront-child/city-weather-block', array(
        'editor_script' => 'city-weather-block',
        'render_callback' => 'render_city_weather_block',
    ));
}
add_action('init', 'register_city_weather_block');

function render_city_weather_block($attributes) {
    $city_manager = new City_Manager();
    $cities = $city_manager->get_cities_with_temperature(); 
    ob_start();

    if (!empty($cities)) {
        echo '<ul>';
        foreach ($cities as $city) {
            echo '<li>' . esc_html($city['name']) . ': ' . esc_html($city['temperature']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo __('No cities found.', 'storefront-child');
    }

    return ob_get_clean();
}
