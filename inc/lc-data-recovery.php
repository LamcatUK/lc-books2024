<?php
defined('ABSPATH') || exit;

/**
 * Emergency Data Recovery Tool
 */

// Add recovery menu
add_action('admin_menu', 'lc_data_recovery_menu');

function lc_data_recovery_menu() {
    add_management_page(
        'Data Recovery',
        'Data Recovery',
        'manage_options',
        'data-recovery',
        'lc_data_recovery_page'
    );
}

function lc_data_recovery_page() {
    ?>
    <div class="wrap">
        <h1>Emergency Data Recovery</h1>
        
        <div class="card">
            <h2>Check Post Revisions</h2>
            <p>This will list all posts and their available revisions to help recover lost content.</p>
            
            <?php
            // Get all posts that might have been affected
            $posts_query = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'post_status' => array('publish', 'draft'),
                'orderby' => 'modified',
                'order' => 'DESC'
            ));
            
            if ($posts_query->have_posts()):
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Post Title</th>
                            <th>Current Content Length</th>
                            <th>Last Modified</th>
                            <th>Revisions Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($posts_query->have_posts()):
                            $posts_query->the_post();
                            $post_id = get_the_ID();
                            $content_length = strlen(get_the_content());
                            $revisions = wp_get_post_revisions($post_id);
                            $revision_count = count($revisions);
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html(get_the_title()); ?></strong>
                                    <br><small>ID: <?php echo $post_id; ?></small>
                                </td>
                                <td>
                                    <?php 
                                    echo $content_length; 
                                    if ($content_length == 0) {
                                        echo ' <span style="color: red;">(EMPTY!)</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo get_the_modified_date('Y-m-d H:i:s'); ?></td>
                                <td>
                                    <?php echo $revision_count; ?>
                                    <?php if ($revision_count > 0): ?>
                                        <br><a href="<?php echo admin_url('revision.php?revision=' . array_keys($revisions)[0]); ?>" target="_blank">View Latest Revision</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>" target="_blank">Edit Post</a>
                                    <?php if ($revision_count > 0): ?>
                                        <br><a href="<?php echo admin_url('edit.php?post_type=revision&p=' . $post_id); ?>" target="_blank">View All Revisions</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Database Backup Check</h2>
            <p>If you have recent database backups, you may be able to restore the content from there.</p>
            <ul>
                <li>Check your hosting provider's backup system</li>
                <li>Look for WordPress backup plugins (UpdraftPlus, BackWPup, etc.)</li>
                <li>Check for manual database exports</li>
            </ul>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Recently Modified Posts (Last 24 Hours)</h2>
            <?php
            // Get recently modified posts
            $recent_posts = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 20,
                'date_query' => array(
                    'after' => '24 hours ago'
                ),
                'orderby' => 'modified',
                'order' => 'DESC'
            ));
            
            if ($recent_posts->have_posts()):
            ?>
                <p>These posts were modified in the last 24 hours and may have been affected:</p>
                <ul>
                    <?php while ($recent_posts->have_posts()): $recent_posts->the_post(); ?>
                        <li>
                            <strong><?php echo esc_html(get_the_title()); ?></strong> 
                            - Modified: <?php echo get_the_modified_date('Y-m-d H:i:s'); ?>
                            - Content Length: <?php echo strlen(get_the_content()); ?>
                            <a href="<?php echo admin_url('post.php?post=' . get_the_ID() . '&action=edit'); ?>" target="_blank">[Edit]</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No posts were modified in the last 24 hours.</p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
}
