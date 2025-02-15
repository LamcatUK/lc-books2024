<?php
/**
 * The header for the theme
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package lc-books2024
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta
        charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="preload"
        href="<?=get_stylesheet_directory_uri()?>/fonts/poppins-v21-latin-300.woff2"
        as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload"
        href="<?=get_stylesheet_directory_uri()?>/fonts/poppins-v21-latin-700.woff2"
        as="font" type="font/woff2" crossorigin="anonymous">
    <?php
    if (get_field('gtm_property','options')) {
        if (!is_user_logged_in()) {
            ?>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?=get_field('gtm_property','options')?>');</script>
<!-- End Google Tag Manager -->
            <?php
        }
    }
    if (get_field('ga_property', 'options')) {
        if (!is_user_logged_in()) {
            ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async
        src="https://www.googletagmanager.com/gtag/js?id=<?=get_field('ga_property', 'options')?>">
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config',
            '<?=get_field('ga_property', 'options')?>'
        );
    </script>
        <?php
        }
    }
if (get_field('google_site_verification', 'options')) {
    echo '<meta name="google-site-verification" content="' . get_field('google_site_verification', 'options') . '" />';
}
if (get_field('bing_site_verification', 'options')) {
    echo '<meta name="msvalidate.01" content="' . get_field('bing_site_verification', 'options') . '" />';
}
?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="overlay"></div>
    <?php
do_action('wp_body_open');

if (get_field('gtm_property', 'options')) {
    if (!is_user_logged_in()) {
        ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe
            src="https://www.googletagmanager.com/ns.html?id=<?=get_field('ga_property', 'options')?>"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
        <?php
    }
}
?>
    <header id="navigation">
        <nav id="main-nav" class="navbar navbar-expand-lg d-block px-0" aria-labelledby="main-nav-label">
            <div class="container-xl">
                    <a href="/" class="navbar-brand" rel="home" aria-label="Home Page">
                        Lamcat Books
                    </a>
                    <button class="navbar-toggler input-button text-white" id="navToggle" data-bs-toggle="collapse"
                      data-bs-target=".navbars" type="button" aria-label="Navigation"><i
                          class="fa fa-navicon"></i></button>

                <?php

                      wp_nav_menu(
                          array(
'theme_location'  => 'primary_nav',
'container_class' => 'collapse navbar-collapse navbars',
'container_id'    => 'primaryNav',
'menu_class'      => 'navbar-nav w-100 justify-content-start justify-content-lg-end align-items-center gap-md-4 gap-xl-5',
'fallback_cb'     => '',
'menu_id'         => 'main-menu',
'depth'           => 2,
'walker'          => new Understrap_WP_Bootstrap_Navwalker(),
)
                      );
?>
            </div>
        </nav>
    </header>
