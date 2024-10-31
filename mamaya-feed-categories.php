<?php
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; 

if (isset($_GET['slug'])) {

  echo "<data><ids>";

  $args = array( 'post_type' => 'product', 'posts_per_page' => 999,'product_cat' => $_GET['slug'] );
  $loop = new WP_Query( $args );
  $first=true;
  while ( $loop->have_posts() ) : $loop->the_post();
    global $product; 
    if ($first) { $first = false; } else { echo ","; }
    echo $product->id;
  endwhile;

  echo "</ids></data>";

} else {

?>


 

<data>
  <?php
  
  $taxonomy = 'product_cat';
  $orderby = 'name';
  $empty = 0;
  $args = array('taxonomy' => $taxonomy, 'orderby' => $orderby, 'hide_empty'   => $empty);
  $all_categories = get_categories($args);
  foreach ($all_categories as $cat) {
    if($cat->category_parent == 0) {
    
      $category_id = $cat->term_id;       
      echo '<cat href="'. get_term_link($cat->slug, 'product_cat') .'" name="'. htmlspecialchars($cat->name) .'" id="'. $cat->cat_ID .'"></cat>';

      $args2 = array(
              'taxonomy'     => $taxonomy,
              'child_of'     => 0,
              'parent'       => $category_id,
              'orderby'      => $orderby,
              'hide_empty'   => $empty
      );
      $sub_cats = get_categories( $args2 );
      if($sub_cats) {
        foreach($sub_cats as $sub_category) {
            echo $sub_category->name ;
        }
      }
    }
  }
  ?>

</data>

<?php } ?>