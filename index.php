<?php
// Exit if accessed directly.
defined('ABSPATH') || exit;
get_header();

$pp = get_option('page_for_posts');

?>
<main id="main" class="pb-5">
    <section class="hero pt-5">
        <div class="container-xl pt-5 text-center">
            <h1>Books</h1>
        </div>
    </section>
    <div class="container-xl py-5 mb-5">
        <div class="row">
            <div class="col-md-2">
                <?php
                $authors = array();
                $series = array();
                $years = array();

                while (have_posts()) {
                    the_post();
                    $author = get_field('author');
                    $year = get_the_date('Y');

                    if (isset($authors[$author])) {
                        $authors[$author]++;
                    } else {
                        $authors[$author] = 1;
                    }

                    if (get_field('series') ?? null) {
                        if (isset($series[get_field('series')])) {
                            $series[get_field('series')]++;
                        } else {
                            $series[get_field('series')] = 1;
                        }
                    }

                    if (isset($years[$year])) {
                        $years[$year]++;
                    } else {
                        $years[$year] = 1;
                    }
                }

                $total_posts = $wp_query->found_posts;
                ?>
                <div class="total-books">
                    <strong>Total Books:</strong> <?= $total_posts ?>
                </div>
                <div class="author-buttons">
                    <div class="author-buttons__title">By Author:</div>
                    <ul>
                        <li class="filter-btn" data-author="all">All (<?= count($authors) ?>)</li>
                        <?php
                        ksort($authors);
                        foreach ($authors as $author => $c) {
                        ?>
                            <li class="filter-btn" data-author="<?= acf_slugify($author) ?>"><?= $author ?> (<?= $c ?>)</li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="series-buttons">
                    <div class="series-buttons__title">By Series:</div>
                    <ul>
                        <li class="filter-btn" data-author="all">All (<?= count($series) ?>)</li>
                        <?php
                        ksort($series);
                        foreach ($series as $s => $v) {
                        ?>
                            <li class="filter-btn" data-author="<?= acf_slugify($s) ?>"><?= $s ?> (<?= $v ?>)</li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="year-buttons">
                    <div class="year-buttons__title">By Year:</div>
                    <ul>
                        <li class="filter-btn" data-filter="all">All (<?= count($years) ?>)</li>
                        <?php
                        krsort($years); // Sort descending for newest first
                        foreach ($years as $year => $count) {
                        ?>
                            <li class="filter-btn" data-filter="year-<?= $year ?>"><?= $year ?> (<?= $count ?>)</li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-10">
                <div class="grid">
                    <?php
                    while (have_posts()) {
                        the_post();
                        $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        if (!$img) {
                            $img = get_stylesheet_directory_uri() . '/img/default-blog.jpg';
                        }
                        $cats = get_the_category();
                        $category = wp_list_pluck($cats, 'name');
                        $flashcat = acf_slugify($category[0]);
                        $catclass = implode(' ', array_map('acf_slugify', $category));
                        $category = implode(', ', $category);

                        $the_date = get_the_date('jS M, Y');

                    ?>
                        <a class="grid__card <?= acf_slugify(get_field('author')) ?> <?= acf_slugify(get_field('series') ?? null) ?> year-<?= get_the_date('Y') ?>"
                            href="<?= get_the_permalink(get_the_ID()) ?>">
                            <div class="card">
                                <div class="card__image_container">
                                    <?= get_the_post_thumbnail(get_the_ID(), 'large', array('class' => 'card__image')) ?>
                                </div>
                                <div class="card__inner">
                                    <h3 class="card__title mb-0">
                                        <?= get_the_title() ?>
                                    </h3>
                                    <div class="card__date"><i class="fa-solid fa-user"></i> <?= get_field('author') ?></div>
                                    <div class="card__date"><i class="fa-solid fa-calendar-check"></i> <?= $the_date ?></div>
                                    <div class="card__content">
                                        <?php
                                        if (get_field('series') ?? null) {
                                            echo '<i class="fa-solid fa-list-ol"></i> ' . get_field('series');
                                            if (get_field('series_number') ?? null) {
                                                echo ' #' . get_field('series_number');
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.grid__card');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const filterValue = this.getAttribute('data-filter');

                cards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'block';
                    } else {
                        if (card.classList.contains(filterValue)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });
    });
</script>
<?php
get_footer();
?>