<?php

/*
 * Загрузка стилей и скриптов
 */
function load_style_script() {

    wp_enqueue_style( 'style', get_template_directory_uri() . '/style.css', '', '0.1' );
    //wp_enqueue_style( 'fonts', '', false, null );
    // for datepicker
    wp_enqueue_style( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );
    wp_enqueue_script( 'jquery-ui-datepicker' );

}
// Инициализация
add_action( 'wp_enqueue_scripts', 'load_style_script' );


/*
 * datepicker
 * version: 1
 * init ...
 */
datepicker_js();

function datepicker_js() {

    // инициализируем datepicker
    if ( is_admin() )
        add_action( 'admin_footer', 'init_datepicker', 99 );
    else
        add_action( 'wp_footer', 'init_datepicker', 99 );

    function init_datepicker() {

        ?><script type="text/javascript">
            jQuery(document).ready(function($) {
                'use strict';
                // default
                $.datepicker.setDefaults({
                    closeText: 'Закрыть', 
                    prevText: '<Пред', 
                    nextText: 'След>', 
                    currentText: 'Сегодня', 
                    monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'], 
                    monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'], 
                    dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'], 
                    dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'], 
                    dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'], 
                    weekHeader: 'Нед', 
                    dateFormat: 'dd-mm-yy', 
                    firstDay: 1, 
                    showAnim: 'slideDown', 
                    isRTL: false, 
                    showMonthAfterYear: false, 
                    yearSuffix: ''
                });

                // Инициализация
                $( 'input[name*="date"], .datepicker' ).datepicker({ dateFormat: 'dd:mm:yy' });
            });
        </script><?php
    }
}


/*
 * Let WordPress manage the document title.
 * By adding theme support, we declare that this theme does not use a
 * hard-coded <title> tag in the document head, and expect WordPress to
 * provide it for us.
 */
add_theme_support( 'title-tag' );


/*
 * Custom templates
 */
add_filter( 'template_include', 'custom_temp' );

function custom_temp( $template ) {
    // Main
    if ( is_page( 'publ') ) {
        if ( $temp_main = locate_template( array( '/custom_temp/main.php' ) ) )
            return $temp_main;
    }
}


/*
 * Class for displaying posts 
 * taking into account filtering
 */
class all_Posts {
    private $base_url;

    public function __construct($base_url) {
        $this->base_url = $base_url;
    }

    public function get_posts($params = []) {

        $url = $this->base_url . '/wp/v2/posts';
        // плюсуем параметрі к урлу, если таковіе есть ...
        if ( !empty($params) ) {
            $url = add_query_arg($params, $url);
        }

        // получим пості
        $response = wp_remote_get($url);

        if ( is_wp_error($response) ) {
            // посмотрим наличие ошибок при запросе
            return [];
        }
        // декодим ответ в массив
        $posts = json_decode(wp_remote_retrieve_body($response), true);

        return $posts;
    }

    public function display_posts($posts) {



        if ( !empty($posts) ) {
            // !!!переписать под селект + привязать віпадалку (?)
            echo '<ul>';

            foreach ($posts as $post) {

                echo '<li>';
                echo '<h2>' . esc_html($post['title']['rendered']) . '</h2>';
                echo '<p>' . esc_html($post['excerpt']['rendered']) . '</p>';
                echo '</li>';

            }

            echo '</ul>';

        } else {

            echo 'No posts found.';
        }
    }

    public function get_pagination_links($total_pages, $current_page) {

        $links = '<div class="pagination">';

        for ($i = 1; $i <= $total_pages; $i++) {

            $active_class = ($i == $current_page) ? 'active' : '';

            $links .= '<a href="?page=' . $i . '" class="' . $active_class . '">' . $i . '</a>';
        }

        $links .= '</div>';

        return $links;
    }

    public function get_categories() {

        $url = $this->base_url . '/wp/v2/categories';
        $response = wp_remote_get($url);

        if ( is_wp_error($response) ) {
            return [];
        }

        $categories = json_decode(wp_remote_retrieve_body($response), true);

        return $categories;
    }

    public function display_categories($categories) {

        if ( !empty($categories) ) {

            echo '<ul class="categories">';

            foreach ($categories as $category) {
                echo '<li><a href="?category=' . $category['id'] . '">' . esc_html($category['name']) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo 'No categories found.';
        }
    }
    // Метод для отображения формы фильтрации по дате
    public function display_date_filter() {

        echo '<form method="get" action="">';
        echo '<input type="date" name="date_filter">';
        echo '<input type="submit" value="Filter">';
        echo '</form>';
    }

    // Метод для получения и вывода постов с использованием AJAX
    public function get_posts_ajax($params = []) {

        $url = $this->base_url . '/wp-json/wp/v2/posts';

        if ( !empty($params) ) {
            $url = add_query_arg($params, $url);
        }

        $response = wp_remote_get($url);

        if ( is_wp_error($response) ) {
            wp_send_json_error();
        }

        $posts = json_decode(wp_remote_retrieve_body($response), true);

        wp_send_json_success($posts);
    }
}

// AJAX + фильтрі старт
add_action('wp_ajax_get_filtered_posts', 'get_filtered_posts');
add_action('wp_ajax_nopriv_get_filtered_posts', 'get_filtered_posts');

function get_filtered_posts() {
    // ща переменніе для вівода запилим
    $wordpress_api = new all_Posts('http://wp.local/wp-json');

    $category_filter = isset($_GET['category']) ? intval($_GET['category']) : null;

    $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : null;

    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    // хоба
    $params = [
        'page' => $current_page, 
        'categories' => $category_filter, 
        'date' => $date_filter, 
    ];

    $posts = $wordpress_api->get_posts_ajax($params);

    wp_die(); // done
}