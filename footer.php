<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package cb-peoplesafe
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

?>
<footer>
    <div class="container-xl d-flex flex-wrap justify-content-between align-items-center gap-4 pb-4">
        <div id="footer_logo" class="w-100 w-md-auto text-center text-md-start">
            Books
        </div>
        <div id="footer_links" class="text-center w-100 w-md-auto ">
            <?=social_icons()?>
        </div>
        <div class="footer_info text-center text-md-end w-100 w-md-auto">
            &copy; <?=date('Y')?> Lamcat
            <span> | Site by <a href="https://www.lamcat.co.uk/" target="_blank">Lamcat</a></span>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>