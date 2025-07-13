<?php
/**
 * Main index template for displaying books with filters by author, series, year, and rating.
 *
 * @package lc-books2024
 */

defined( 'ABSPATH' ) || exit;

get_header();

$pp = get_option( 'page_for_posts' );

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
                $series  = array();
                $years   = array();
                $ratings = array();

                while ( have_posts() ) {
                    the_post();
                    $author = get_field( 'author' );
                    $year   = get_the_date( 'Y' );
                    $rating = get_field( 'rating' );

                    if ( isset( $authors[ $author ] ) ) {
                        ++$authors[ $author ];
                    } else {
                        $authors[ $author ] = 1;
                    }

                    if ( get_field( 'series' ) ?? null ) {
                        if ( isset( $series[ get_field( 'series' ) ] ) ) {
                            ++$series[ get_field( 'series' ) ];
                        } else {
                            $series[ get_field( 'series' ) ] = 1;
                        }
                    }

                    if ( isset( $years[ $year ] ) ) {
                        ++$years[ $year ];
                    } else {
                        $years[ $year ] = 1;
                    }

                    if ( $rating ) {
                        if ( isset( $ratings[ $rating ] ) ) {
                            ++$ratings[ $rating ];
                        } else {
                            $ratings[ $rating ] = 1;
                        }
                    }
                }

                $total_posts = $wp_query->found_posts;
                ?>
                <div class="total-books">
                    <strong>Total Books:</strong> <?= esc_html( $total_posts ); ?>
                </div>
                <div class="sort-buttons">
                    <div class="sort-buttons__title collapsible-title">
                        Sort by: <span class="collapse-icon">−</span>
                    </div>
                    <ul class="collapsible-content">
                        <li class="sort-btn active" data-sort="default">Default</li>
                        <li class="sort-btn" data-sort="title">Title (A-Z)</li>
                        <li class="sort-btn" data-sort="title-desc">Title (Z-A)</li>
                        <li class="sort-btn" data-sort="date">Date (Newest)</li>
                        <li class="sort-btn" data-sort="date-desc">Date (Oldest)</li>
                        <li class="sort-btn" data-sort="rating">Rating (High)</li>
                        <li class="sort-btn" data-sort="rating-desc">Rating (Low)</li>
                    </ul>
                </div>
                <div class="author-buttons">
                    <div class="author-buttons__title collapsible-title">
                        By Author: <span class="collapse-icon">−</span>
                    </div>
                    <ul class="collapsible-content">
                        <li class="filter-btn" data-filter="all">All (<?= esc_html( count( $authors ) ); ?>)</li>
                        <?php
                        ksort( $authors );
                        foreach ( $authors as $author => $c ) {
                            ?>
                            <li class="filter-btn" data-filter="<?= esc_attr( sanitize_title( $author ) ); ?>"><?= esc_html( $author ); ?> (<?= esc_html( $c ) ?>)</li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="series-buttons">
                    <div class="series-buttons__title collapsible-title">
                        By Series: <span class="collapse-icon">−</span>
                    </div>
                    <ul class="collapsible-content">
                        <li class="filter-btn" data-filter="all">All (<?= esc_html( count( $series ) ); ?>)</li>
                        <?php
                        ksort( $series );
                        foreach ( $series as $ss => $v ) {
                            ?>
                            <li class="filter-btn" data-filter="<?= esc_html( sanitize_title( $ss) ); ?>"><?= esc_html( $ss ); ?> (<?= esc_html( $v ); ?>)</li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="year-buttons">
                    <div class="year-buttons__title collapsible-title">
                        By Year: <span class="collapse-icon">−</span>
                    </div>
                    <ul class="collapsible-content">
                        <li class="filter-btn" data-filter="all">All (<?= esc_html( count( $years ) ); ?>)</li>
                        <?php
                        krsort( $years ); // Sort descending for newest first.
                        foreach ( $years as $pyear => $count ) {
                        ?>
                            <li class="filter-btn" data-filter="year-<?= esc_attr( $pyear ); ?>"><?= esc_html( $pyear ); ?> (<?= esc_html( $count ); ?>)</li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="rating-buttons">
                    <div class="rating-buttons__title collapsible-title">
                        By Rating: <span class="collapse-icon">−</span>
                    </div>
                    <ul class="collapsible-content">
                        <li class="filter-btn" data-filter="all">All (<?= count($ratings) ?>)</li>
                        <?php
                        // Create "X stars and over" filters
                        for ($star_threshold = 5; $star_threshold >= 1; $star_threshold--) {
                            // Count books with this rating and above
                            $count_threshold = 0;
                            foreach ($ratings as $rating => $count) {
                                if ($rating >= $star_threshold) {
                                    $count_threshold += $count;
                                }
                            }
                            
                            if ($count_threshold > 0) {
                                $stars = '';
                                
                                // Add full stars for the threshold
                                for ($i = 1; $i <= $star_threshold; $i++) {
                                    $stars .= '<i class="fa-solid fa-star"></i>';
                                }
                                
                                // Add empty stars to make 5 total
                                $remaining = 5 - $star_threshold;
                                for ($i = 1; $i <= $remaining; $i++) {
                                    $stars .= '<i class="fa-regular fa-star"></i>';
                                }
                        ?>
                            <li class="filter-btn" data-filter="rating-<?= $star_threshold ?>-plus"><?= $stars ?> &amp; up</li>
                        <?php
                            }
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
                        $flashcat = sanitize_title($category[0]);
                        $catclass = implode(' ', array_map('sanitize_title', $category));
                        $category = implode(', ', $category);

                        $the_date = get_the_date('jS M, Y');
                        $rating = get_field('rating');

                    ?>
                        <a class="grid__card <?= sanitize_title(get_field('author')) ?> <?= sanitize_title(get_field('series') ?? null) ?> year-<?= get_the_date('Y') ?> <?= $rating ? 'rating-' . str_replace('.', '-', $rating) : '' ?>"
                            href="<?= get_the_permalink(get_the_ID()) ?>"
                            data-title="<?= esc_attr(get_the_title()) ?>"
                            data-date="<?= esc_attr(get_the_date('Y-m-d')) ?>"
                            data-rating="<?= esc_attr($rating ? $rating : '0') ?>"
                            data-author="<?= esc_attr(get_field('author')) ?>">
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
                                    <?php
                                    $rating = get_field('rating');
                                    if ($rating) {
                                    ?>
                                        <div class="card__rating">
                                            <?php
                                            $full_stars = floor($rating);
                                            $has_half = ($rating - $full_stars) >= 0.5;
                                            
                                            // Display full stars
                                            for ($i = 1; $i <= $full_stars; $i++) {
                                                echo '<i class="fa-solid fa-star"></i>';
                                            }
                                            
                                            // Display half star if needed
                                            if ($has_half) {
                                                echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                            }
                                            
                                            // Display empty stars
                                            $remaining = 5 - $full_stars - ($has_half ? 1 : 0);
                                            for ($i = 1; $i <= $remaining; $i++) {
                                                echo '<i class="fa-regular fa-star"></i>';
                                            }
                                            ?>
                                            <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                        </div>
                                    <?php
                                    }
                                    ?>
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
        const filterButtons = document.querySelectorAll('.filter-btn');
        const sortButtons = document.querySelectorAll('.sort-btn');
        const cards = document.querySelectorAll('.grid__card');
        const grid = document.querySelector('.grid');
        
        // Store original order of cards
        const originalOrder = Array.from(cards);
        
        let currentFilter = 'all';
        let currentSort = 'default';

        // Collapse/Expand functionality
        const collapsibleTitles = document.querySelectorAll('.collapsible-title');
        collapsibleTitles.forEach(title => {
            title.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('.collapse-icon');
                
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    icon.textContent = '−';
                } else {
                    content.style.display = 'none';
                    icon.textContent = '+';
                }
            });
        });

        // Filter functionality
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all filter buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                currentFilter = this.getAttribute('data-filter');
                applyFilterAndSort();
            });
        });

        // Sort functionality
        sortButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all sort buttons
                sortButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                currentSort = this.getAttribute('data-sort');
                applyFilterAndSort();
            });
        });

        function applyFilterAndSort() {
            // Start with original order
            let cardsToProcess = [...originalOrder];
            
            // Filter cards
            const filteredCards = cardsToProcess.filter(card => {
                if (currentFilter === 'all') {
                    return true;
                } else if (currentFilter.startsWith('rating-') && currentFilter.endsWith('-plus')) {
                    // Handle "X stars and over" filtering
                    const threshold = parseInt(currentFilter.replace('rating-', '').replace('-plus', ''));
                    const cardRating = parseFloat(card.getAttribute('data-rating'));
                    return cardRating >= threshold;
                } else {
                    // Handle other filters (author, series, year)
                    return card.classList.contains(currentFilter);
                }
            });

            // Sort filtered cards (if not default)
            if (currentSort !== 'default') {
                filteredCards.sort((a, b) => {
                    switch (currentSort) {
                        case 'title':
                            return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
                        case 'title-desc':
                            return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
                        case 'date':
                            return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
                        case 'date-desc':
                            return new Date(a.getAttribute('data-date')) - new Date(b.getAttribute('data-date'));
                        case 'rating':
                            return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
                        case 'rating-desc':
                            return parseFloat(a.getAttribute('data-rating')) - parseFloat(b.getAttribute('data-rating'));
                        default:
                            return 0;
                    }
                });
            }

            // Hide all cards first
            originalOrder.forEach(card => card.style.display = 'none');

            // Remove all cards from grid
            originalOrder.forEach(card => {
                if (card.parentNode) {
                    card.remove();
                }
            });
            
            // Append filtered and sorted cards back to grid in correct order
            filteredCards.forEach(card => {
                card.style.display = 'block';
                grid.appendChild(card);
            });
        }
    });
</script>
<?php
get_footer();
?>