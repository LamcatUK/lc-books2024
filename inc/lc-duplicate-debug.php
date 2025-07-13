<?php
defined('ABSPATH') || exit;

/**
 * Duplicate Detection Debug Tool
 */

// Add debug menu
add_action('admin_menu', 'lc_duplicate_debug_menu');

function lc_duplicate_debug_menu() {
    add_management_page(
        'Duplicate Debug',
        'Duplicate Debug',
        'manage_options',
        'duplicate-debug',
        'lc_duplicate_debug_page'
    );
}

function lc_duplicate_debug_page() {
    ?>
    <div class="wrap">
        <h1>Duplicate Detection Debug</h1>
        
        <?php
        // Check posts 7 and 323 specifically
        $post_7 = get_post(7);
        $post_323 = get_post(323);
        ?>
        
        <div class="card">
            <h2>Post Comparison</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Post 7 (Original)</th>
                        <th>Post 323 (Duplicate)</th>
                        <th>Match?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>ID</strong></td>
                        <td><?php echo $post_7 ? $post_7->ID : 'Not found'; ?></td>
                        <td><?php echo $post_323 ? $post_323->ID : 'Not found'; ?></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Title</strong></td>
                        <td><?php echo $post_7 ? esc_html($post_7->post_title) : 'Not found'; ?></td>
                        <td><?php echo $post_323 ? esc_html($post_323->post_title) : 'Not found'; ?></td>
                        <td><?php echo ($post_7 && $post_323 && $post_7->post_title === $post_323->post_title) ? '✅ YES' : '❌ NO'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Title Length</strong></td>
                        <td><?php echo $post_7 ? strlen($post_7->post_title) : 'N/A'; ?></td>
                        <td><?php echo $post_323 ? strlen($post_323->post_title) : 'N/A'; ?></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Title (Raw Bytes)</strong></td>
                        <td><?php echo $post_7 ? bin2hex($post_7->post_title) : 'N/A'; ?></td>
                        <td><?php echo $post_323 ? bin2hex($post_323->post_title) : 'N/A'; ?></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Post Status</strong></td>
                        <td><?php echo $post_7 ? $post_7->post_status : 'N/A'; ?></td>
                        <td><?php echo $post_323 ? $post_323->post_status : 'N/A'; ?></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Post Type</strong></td>
                        <td><?php echo $post_7 ? $post_7->post_type : 'N/A'; ?></td>
                        <td><?php echo $post_323 ? $post_323->post_type : 'N/A'; ?></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Created Date</strong></td>
                        <td><?php echo $post_7 ? $post_7->post_date : 'N/A'; ?></td>
                        <td><?php echo $post_323 ? $post_323->post_date : 'N/A'; ?></td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>Database Query Test</h2>
            <?php
            if ($post_7) {
                $title_to_search = $post_7->post_title;
                
                global $wpdb;
                $found_posts = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID, post_title, post_status, post_date FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'post' AND post_status IN ('publish', 'draft', 'private') ORDER BY ID ASC",
                    $title_to_search
                ));
                
                echo '<p><strong>Searching for title:</strong> "' . esc_html($title_to_search) . '"</p>';
                echo '<p><strong>Found ' . count($found_posts) . ' posts:</strong></p>';
                
                if ($found_posts) {
                    echo '<ul>';
                    foreach ($found_posts as $found_post) {
                        echo '<li>ID: ' . $found_post->ID . ' | Title: "' . esc_html($found_post->post_title) . '" | Status: ' . $found_post->post_status . ' | Date: ' . $found_post->post_date . '</li>';
                    }
                    echo '</ul>';
                }
            }
            ?>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>All Potential Duplicates</h2>
            <?php
            // Find all potential duplicate titles
            global $wpdb;
            $duplicates = $wpdb->get_results("
                SELECT post_title, COUNT(*) as count, GROUP_CONCAT(ID ORDER BY ID) as post_ids
                FROM {$wpdb->posts} 
                WHERE post_type = 'post' 
                AND post_status IN ('publish', 'draft', 'private')
                GROUP BY post_title 
                HAVING COUNT(*) > 1
                ORDER BY count DESC, post_title
            ");
            
            if ($duplicates) {
                echo '<p><strong>Found ' . count($duplicates) . ' duplicate titles:</strong></p>';
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Title</th><th>Count</th><th>Post IDs</th><th>Actions</th></tr></thead>';
                echo '<tbody>';
                foreach ($duplicates as $duplicate) {
                    echo '<tr>';
                    echo '<td>' . esc_html($duplicate->post_title) . '</td>';
                    echo '<td>' . $duplicate->count . '</td>';
                    echo '<td>' . $duplicate->post_ids . '</td>';
                    echo '<td>';
                    $ids = explode(',', $duplicate->post_ids);
                    foreach ($ids as $id) {
                        echo '<a href="' . admin_url('post.php?post=' . trim($id) . '&action=edit') . '" target="_blank">Edit ' . trim($id) . '</a> ';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No duplicates found.</p>';
            }
            ?>
        </div>
    </div>
    <?php
}
