<?php

class City_Manager {

    public function __construct() {
        // Подключаем хуки WordPress
        add_action('init', [$this, 'register_city_post_type']); // Регистрируем кастомный тип записи
        add_action('init', [$this, 'register_country_taxonomy']); // Регистрируем таксономию
        add_action('add_meta_boxes', [$this, 'add_city_meta_boxes']); // Добавляем мета-боксы
        add_action('save_post', [$this, 'save_city_meta']); // Сохраняем мета-поля
        add_action('widgets_init', [$this, 'register_city_weather_widget']); // Регистрируем виджет погоды
        add_action('wp_ajax_city_search', [$this, 'ajax_city_search']); // Обработка AJAX-запроса для поиска городов
        add_action('wp_ajax_nopriv_city_search', [$this, 'ajax_city_search']); // Обработка AJAX-запроса для гостей
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']); // Подключаем скрипты
    }

    // Регистрация пользовательского типа записи "Cities"
    public function register_city_post_type() {
        register_post_type('city', [
            'labels' => [
                'name' => __('Cities', 'storefront-child'),
                'singular_name' => __('City', 'storefront-child'),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'cities'],
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
    }

    // Регистрация таксономии "Countries"
    public function register_country_taxonomy() {
        register_taxonomy('country', 'city', [
            'label' => __('Countries', 'storefront-child'),
            'rewrite' => ['slug' => 'country'],
            'hierarchical' => true,
        ]);
    }

    // Добавление мета-боксов для широты и долготы
    public function add_city_meta_boxes() {
        add_meta_box('city_coordinates', __('Coordinates', 'storefront-child'), [$this, 'render_city_coordinates_meta_box'], 'city', 'side', 'default');
    }

    // Отображение мета-боксов
    public function render_city_coordinates_meta_box($post) {
        $latitude = get_post_meta($post->ID, 'city_latitude', true);
        $longitude = get_post_meta($post->ID, 'city_longitude', true);
        ?>
        <label for="city_latitude"><?php _e('Latitude:', 'storefront-child'); ?></label>
        <input type="text" id="city_latitude" name="city_latitude" value="<?php echo esc_attr($latitude); ?>" />
        <br/>
        <label for="city_longitude"><?php _e('Longitude:', 'storefront-child'); ?></label>
        <input type="text" id="city_longitude" name="city_longitude" value="<?php echo esc_attr($longitude); ?>" />
        <?php
    }

    // Сохранение мета-полей
    public function save_city_meta($post_id) {
        if (isset($_POST['city_latitude'])) {
            update_post_meta($post_id, 'city_latitude', sanitize_text_field($_POST['city_latitude']));
        }
        if (isset($_POST['city_longitude'])) {
            update_post_meta($post_id, 'city_longitude', sanitize_text_field($_POST['city_longitude']));
        }
    }

    // Регистрация виджета
    public function register_city_weather_widget() {
        register_widget('City_Weather_Widget');
    }

    // Обработка AJAX-запроса для поиска городов
    public function ajax_city_search() {
        // Логика для поиска и возврата результатов
    }

    // Подключение скриптов
    public function enqueue_scripts() {
        wp_enqueue_script('city-temperature-script', get_stylesheet_directory_uri() . '/js/city-temperature.js', array('jquery'), null, true);
        wp_localize_script('city-temperature-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('advanced_search_nonce')));
    }

    // Новый метод для получения городов с температурой
    public function get_cities_with_temperature() {
        $api_key = get_option('city_temperature_api_key');
        $cities_query = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $cities = array();
        if ($cities_query->have_posts()) {
            while ($cities_query->have_posts()) {
                $cities_query->the_post();
                $city_name = get_the_title();
                $latitude = get_post_meta(get_the_ID(), 'city_latitude', true);
                $longitude = get_post_meta(get_the_ID(), 'city_longitude', true);

                if (empty($latitude) || empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city_name}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['coord']['lat']) && isset($data['coord']['lon'])) {
                            $latitude = $data['coord']['lat'];
                            $longitude = $data['coord']['lon'];
                            update_post_meta(get_the_ID(), 'city_latitude', $latitude);
                            update_post_meta(get_the_ID(), 'city_longitude', $longitude);
                        }
                    }
                }

                $temperature = 'N/A';
                if (!empty($latitude) && !empty($longitude)) {
                    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$api_key}&units=metric";
                    $response = wp_remote_get($url);

                    if (!is_wp_error($response)) {
                        $data = json_decode(wp_remote_retrieve_body($response), true);
                        if (isset($data['main']['temp'])) {
                            $temperature = $data['main']['temp'] . '°C';
                        }
                    }
                }

                $cities[] = array(
                    'name' => $city_name,
                    'temperature' => $temperature,
                );
            }
            wp_reset_postdata();
        }

        return $cities;
    }
}

// Инициализация класса
new City_Manager();
