<?php

// Подключаем классы
require_once get_stylesheet_directory() . '/inc/class-city-manager.php';
require_once get_stylesheet_directory() . '/inc/class-city-weather-widget.php';
require_once get_stylesheet_directory() . '/inc/block-city-weather.php';

// Подключаем обработчики AJAX-запросов и скрипты
function my_enqueue_scripts() {
    // Подключаем jQuery и кастомный скрипт
    wp_enqueue_script(
        'city-temperature-script',
        get_stylesheet_directory_uri() . '/js/city-temperature.js',
        array('jquery'),  // Задаем зависимость от jQuery
        null,
        true
    );

    // Передаем параметры как массив в wp_localize_script
    wp_localize_script('city-temperature-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('advanced_search_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'my_enqueue_scripts');
