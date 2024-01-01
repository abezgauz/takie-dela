<?php

/**
 * Plugin Name: Ajax для постов
 * Description: в рамках тестового для Таких Дел
 * Plugin URI:  *
 * Author URI:  https://abezgauz.github.io/
 * Author:      mr.abezgauz
 * Version:     1.2
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Network:     true
 */
<?php

// старт
add_action('wp_ajax_get_filtered_posts', 'get_filtered_posts');
add_action('wp_ajax_nopriv_get_filtered_posts', 'get_filtered_posts');
add_action( 'wp_footer', 'init_js_for_ajax');

function init_js_for_ajax() { ?><!-- JavaScript для AJAX -->
    <script>
    jQuery(document).ready(function($) {
      // Ajax ...
      function updatePosts() {

        let category = $('#category-filter').val();
        let date = $('#date-filter').val();
        let page = 1;

        $.ajax({

          url: '<?php echo admin_url('admin-ajax.php'); ?>', 
          type: 'GET', 
          data: {
            action: 'get_filtered_posts', 
            category: category, 
            date_filter: date, 
            page: page, 
          }, 
          success: function(response) {
            // тут будем обновлять контент
          }, 
          error: function() {

          }
        });
      }
      // отслеживаем фильтрі и пр.
      $('#category-filter, #date-filter, .pagination a').on('change click', function(e) {
        e.preventDefault();
        updatePosts();
      });
    });
    </script><?php

}

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