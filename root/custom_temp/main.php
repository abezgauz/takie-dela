<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php wp_head(); ?>

</head>

<body>

<?php
  // hat
  get_header(); ?>

<div class="title">
  <h1><?php the_title(); ?></h1>
</div>

<div class="filter filter-mb">
  <form class="filter__form">
    <div class="filter__form__cont">
      <label for="date">
        Дата публикации
      </label>
      <input class="filter__form__input" name="filter_date">
    </div>
    <div class="filter__form__cont">
      <label for="contact">
        Категория
      </label>
      <?php
        // Костыль меню категорий
        $cat_select = get_terms(array(
          'taxonomy' => 'category', 
          'hide_empty' => false, 
        ));

        if ( !empty($cat_select) ) : 

          $output = '<select class="filter__form__input">';

          foreach ( $cat_select as $category ) {

            if ( $category->parent == 0 ) {

              $output.='<optgroup label="'.esc_attr($category->name).'">';

              foreach ( $cat_select as $subcategory ) {

                if ( $subcategory->parent == $category->term_id ) {

                  $output.= '<option value="'.esc_attr($subcategory->term_id).'">'.esc_html($subcategory->name).'</option>';
                }
              }

              $output.='</optgroup>';
            }
          }

          $output.='</select>';

          echo $output;

        endif; ?>
    </div>
    <div class="filter__form__cont filter__form__cont-wm">
      <button class="filter__form__button">Найти</button>
    </div>
  </form>
</div>

<ul class="grid">
<?php
  // юзаем наш класс all_Posts
  $wordpress_api = new all_Posts('http://wp.local/wp-json');
  // активная страница (дефолт -1)
  $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
  // получим пості с учетом фильтрации и категории ...
  $category_filter = isset($_GET['category']) ? intval($_GET['category']) : null;
  $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : null;
  $params = [
    'page' => $current_page, 
    'categories' => $category_filter, 
    'date' => $date_filter, 
  ];
  // получим пості с учетом фильтрации ...
  $posts = $wordpress_api->get_posts($params);
  // получим и віведем пості с учетом фильтрации и даті ...
  $categories = $wordpress_api->get_categories();
  $wordpress_api->display_categories($categories);
  $wordpress_api->display_date_filter();
  // вівод постов ...
  $wordpress_api->display_posts($posts);
  // пагинация
  $total_pages = $wordpress_api->get_posts(); // кол-во страниц
  echo $wordpress_api->get_pagination_links(count($total_pages), $current_page); // вівод пагинации
  ?>
</ul>

<div class="page_navigation">
  <div class="post_load"><p>Загрузить ещё</p></div>
  <div class="">

  </div>
</div>

<?php
  // shoes
  get_footer(); ?>

<?php wp_footer(); ?>

</body>
</html>