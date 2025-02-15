<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
$img = get_the_post_thumbnail(get_the_ID(), 'full', array('class' => 'single-blog__image'));

add_action('wp_head', function () {
    global $schema;
    echo $schema;
});

get_header();
// $img = get_the_post_thumbnail_url(get_the_ID(),'full');

?>
<main id="main" class="single-blog">
    <?php
    $content = get_the_content();
    $blocks = parse_blocks($content);
    $sidebar = array();
    $after;
    ?>
    <section class="breadcrumbs text-white fs-200 container-xl pt-4 pb-2">
        <?php
        if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
        }
        ?>
    </section>
    <div class="container-xl">
        <div class="row g-4 pb-5 justify-content-center">
            <div class="col-lg-9 bg-white text-black">
                <div class="row">
                    <div class="col-md-3">
                        <?= $img ?>
                    </div>
                    <div class="col-md-9">
                        <h1 class="h1"><?= get_the_title() ?></h1>
                        <div class="author_meta">
                            <div><strong>Author</strong></div>
                            <div><?= get_field('author') ?></div>
                            <?php
                            if (get_field('series') ?? null) {
                            ?>
                                <div><strong>Series</strong></div>
                                <div><?= get_field('series') ?>
                                    <?php
                                    if (get_field('series_number') ?? null) {
                                    ?>
                                        <?= get_field('series_number') ?>
                                <?php
                                    }
                                }
                                ?>
                                </div>
                                <div><strong>Purchased</strong></div>
                                <div><?= get_the_date() ?></div>
                        </div>
                    </div>
                </div>
                <?php
                foreach ($blocks as $block) {
                    if ($block['blockName'] == 'core/heading') {
                        if (!array_key_exists('level', $block['attrs'])) {
                            $heading = strip_tags($block['innerHTML']);
                            $id = acf_slugify($heading);
                            echo '<a id="' . $id . '" class="anchor"></a>';
                            $sidebar[$heading] = $id;
                        }
                    }
                    // echo render_block($block);
                    echo apply_filters('the_content', render_block($block));
                }
                ?>
            </div>
        </div>
        <?php

        $current_series = get_field('series') ?? null;
        $current_number = get_field('series_number') ?? null;

        if ($current_series && $current_number) {

            // Query for the previous book in the series
            $prev_book = new WP_Query(array(
                'post_type' => 'post', // Default WordPress post type
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'series',
                        'value' => $current_series,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'series_number',
                        'value' => $current_number,
                        'compare' => '<',
                        'type' => 'NUMERIC'
                    )
                ),
                'orderby' => array(
                    'meta_value_num' => 'DESC'
                ),
                'meta_key' => 'series_number'
            ));

            // Display the previous book link if found
            if ($prev_book->have_posts()) {
                while ($prev_book->have_posts()) : $prev_book->the_post();
                    echo '<a href="' . get_permalink() . '">&larr; Previous Book: ' . get_the_title() . '</a><br>';
                endwhile;
                wp_reset_postdata();
            }

            // Query for the next book in the series
            $next_book = new WP_Query(array(
                'post_type' => 'post', // Default WordPress post type
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'series',
                        'value' => $current_series,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'series_number',
                        'value' => $current_number,
                        'compare' => '>',
                        'type' => 'NUMERIC'
                    )
                ),
                'orderby' => array(
                    'meta_value_num' => 'ASC'
                ),
                'meta_key' => 'series_number'
            ));

            // Display the next book link if found
            if ($next_book->have_posts()) {
                while ($next_book->have_posts()) : $next_book->the_post();
                    echo '<a href="' . get_permalink() . '">Next Book: ' . get_the_title() . ' &rarr;</a><br>';
                endwhile;
                wp_reset_postdata();
            }
        } else {
            echo '<p>This book is not part of a series.</p>';
        }


        $author = get_field('author');

        $q = new WP_Query(array(
            'post_type' => 'post',
            'posts_per_page' => 6,
            'post__not_in' => array(get_the_ID()),
            'meta_query' => array(
                array(
                    'key' => 'author',
                    'value' => $author,
                    'compare' => '='
                )
            )
        ));

        if ($q->have_posts()) {
        ?>
            <hr class="mt-4">
            <section class="latest_posts mt-4">
                <h3 class="fs-700"><span>More by <?= $author ?></h3>
                <div class="row mb-4">
                    <?php
                    while ($q->have_posts()) {
                        $q->the_post();
                        $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        if (!$img) {
                            $img = get_stylesheet_directory_uri() . '/img/default-blog.jpg';
                        }

                    ?>
                        <div class="col-sm-6 col-xl-2">
                            <a href="<?= get_the_permalink(get_the_ID()) ?>" class="latest_posts__card">
                                <?= get_the_post_thumbnail(get_the_ID(), 'large', array('class' => 'latest_posts__card_bg')) ?>
                                <h3><?= get_the_title(get_the_ID()) ?></h3>
                            </a>
                        </div>
                    <?php
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
        <?php
        }
        ?>
    </div>
</main>
<?php
get_footer();
?>