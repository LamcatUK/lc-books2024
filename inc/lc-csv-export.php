<?php
defined('ABSPATH') || exit;

/**
 * Books CSV Export Functionality
 */

// Add admin menu for CSV export
add_action('admin_menu', 'lc_books_csv_export_menu');

function lc_books_csv_export_menu() {
    add_management_page(
        'Books CSV Import/Export',
        'Books CSV Import/Export',
        'manage_options',
        'books-csv-tools',
        'lc_books_csv_tools_page'
    );
}

// Display the import/export tools page
function lc_books_csv_tools_page() {
    ?>
    <div class="wrap">
        <h1>Books CSV Import/Export Tools</h1>
        
        <!-- Import Section -->
        <div class="card" style="margin-bottom: 20px;">
            <h2>Import Books from CSV</h2>
            <p>Upload a CSV file to import books. The CSV should contain columns: Title, Author, Series, Series Number, Rating</p>
            
            <?php if (isset($_GET['import_result'])): ?>
                <div class="notice notice-<?php echo esc_attr($_GET['import_result']); ?>">
                    <p><?php echo esc_html(urldecode($_GET['message'])); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('import_books_csv', 'import_books_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">CSV File</th>
                        <td>
                            <input type="file" name="csv_file" accept=".csv" required>
                            <p class="description">Select a CSV file containing book data.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Import Options</th>
                        <td>
                            <label>
                                <input type="checkbox" name="skip_first_row" value="1" checked>
                                Skip first row (headers)
                            </label><br>
                            <label>
                                <input type="checkbox" name="update_existing" value="1">
                                Update existing posts with same title
                            </label><br>
                            <label>
                                <input type="checkbox" name="preserve_content" value="1" checked>
                                <strong>Preserve existing post content (recommended)</strong>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Import Books CSV', 'primary', 'import_csv'); ?>
            </form>
        </div>
        
        <!-- Export Section -->
        <div class="card">
            <h2>Export Books to CSV</h2>
            <p>Download all books as a CSV file.</p>
            
            <?php
            // Get total book count
            $books_query = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            $total_books = $books_query->found_posts;
            wp_reset_postdata();
            ?>
            
            <div class="notice notice-info">
                <p><strong>Total Books to Export:</strong> <?php echo esc_html($total_books); ?></p>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('export_books_csv', 'export_books_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Export Options</th>
                        <td>
                            <label>
                                <input type="checkbox" name="include_headers" value="1" checked>
                                Include column headers
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Download Books CSV', 'secondary', 'export_csv'); ?>
            </form>
        </div>
        
        <!-- Debug Log Section -->
        <div class="card" style="margin-top: 20px;">
            <h2>Import Debug Log</h2>
            <?php lc_display_debug_log(); ?>
        </div>
    </div>
    <?php
}

// Function to display debug log.
function lc_display_debug_log() {
    $debug_file = sys_get_temp_dir() . '/books_import_debug.txt';
    
    if ( file_exists( $debug_file ) ) {
        $debug_content = file_get_contents( $debug_file );
        echo '<div class="notice notice-info"><h3>Latest Import Debug Log</h3>';
        echo '<pre style="background: #f1f1f1; padding: 15px; max-height: 500px; overflow-y: auto; font-size: 12px;">';
        echo esc_html( $debug_content );
        echo '</pre>';
        echo '<p><em>Log file: ' . esc_html( $debug_file ) . '</em></p>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-warning"><p>No debug log found. Run an import to generate debug information.</p></div>';
    }
}

// Handle CSV import and export
add_action('admin_init', 'lc_handle_books_csv_tools');

function lc_handle_books_csv_tools() {
    // Handle CSV Import
    if (isset($_POST['import_csv']) && isset($_POST['import_books_nonce'])) {
        if (!wp_verify_nonce($_POST['import_books_nonce'], 'import_books_csv')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        lc_handle_books_csv_import();
        return;
    }
    
    // Handle CSV Export
    if (isset($_POST['export_csv']) && isset($_POST['export_books_nonce'])) {
        if (!wp_verify_nonce($_POST['export_books_nonce'], 'export_books_csv')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        lc_generate_books_csv();
        return;
    }
}

function lc_generate_books_csv() {
    // Set headers for CSV download
    $filename = 'books-export-' . date('Y-m-d-H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer connected to output stream
    $output = fopen('php://output', 'w');
    
    // Add headers if requested
    if (isset($_POST['include_headers']) && $_POST['include_headers']) {
        fputcsv($output, array(
            'Title',
            'Author',
            'Series',
            'Series Number',
            'Rating',
            'Publication Date',
            'Post URL'
        ));
    }
    
    // Query all books
    $books_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    if ($books_query->have_posts()) {
        while ($books_query->have_posts()) {
            $books_query->the_post();
            
            $title = get_the_title();
            $author = get_field('author') ?: '';
            $series = get_field('series') ?: '';
            $series_number = get_field('series_number') ?: '';
            $rating = get_field('rating') ?: '';
            $date = get_the_date('Y-m-d');
            $url = get_permalink();
            
            // Add row to CSV
            fputcsv($output, array(
                $title,
                $author,
                $series,
                $series_number,
                $rating,
                $date,
                $url
            ));
        }
    }
    
    wp_reset_postdata();
    fclose($output);
    exit;
}

// Handle CSV import
function lc_handle_books_csv_import() {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(admin_url('tools.php?page=books-csv-tools&import_result=error&message=' . urlencode('File upload failed')));
        exit;
    }
    
    $file_path = $_FILES['csv_file']['tmp_name'];
    $file_handle = fopen($file_path, 'r');
    
    if (!$file_handle) {
        wp_redirect(admin_url('tools.php?page=books-csv-tools&import_result=error&message=' . urlencode('Could not read CSV file')));
        exit;
    }
    
    $skip_first_row = isset($_POST['skip_first_row']) && $_POST['skip_first_row'];
    $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'];
    $imported_count = 0;
    $updated_count = 0;
    $skipped_count = 0;
    $debug_log = array();
    $row_number = 0;
    
    while (($row = fgetcsv($file_handle)) !== false) {
        $row_number++;
        
        // Skip header row if requested
        if ($row_number === 1 && $skip_first_row) {
            continue;
        }
        
        // Validate row has minimum required columns
        if (count($row) < 2) {
            continue;
        }
        
        // Extract data from CSV row
        $title = trim($row[0] ?? '');
        $author = trim($row[1] ?? '');
        $series = trim($row[2] ?? '');
        $series_number = trim($row[3] ?? '');
        $rating = trim($row[4] ?? '');
        
        // Skip if no title
        if (empty($title)) {
            continue;
        }
        
        // Validate rating format (should be decimal with 1 place)
        if (!empty($rating) && !is_numeric($rating)) {
            continue;
        }
        
        $normalized_title = normalize_book_title( $title );
        
        // Create additional normalized versions for matching.
        $lowercase_title = strtolower( $normalized_title );
        $slug_title = sanitize_title( $normalized_title );
        
        // Check if post with same title exists using comprehensive search.
        global $wpdb;
        
        // First, try exact matches.
        $existing_post_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                 WHERE post_title = %s
                 AND post_type = 'post' 
                 AND post_status IN ('publish', 'draft', 'private') 
                 ORDER BY ID ASC LIMIT 1",
                $normalized_title
            )
        );
        
        // If no exact match, try normalized database titles.
        if ( ! $existing_post_id ) {
            $all_posts = $wpdb->get_results(
                "SELECT ID, post_title FROM {$wpdb->posts} 
                 WHERE post_type = 'post' 
                 AND post_status IN ('publish', 'draft', 'private')"
            );
            
            foreach ( $all_posts as $post ) {
                $db_normalized = normalize_book_title( $post->post_title );
                if ( $db_normalized === $normalized_title || 
                    strtolower( $db_normalized ) === $lowercase_title ||
                    sanitize_title( $db_normalized ) === $slug_title ) {
                    $existing_post_id = $post->ID;
                    break;
                }
            }
        }
        
        $existing_post = $existing_post_id ? get_post( $existing_post_id ) : null;
        
        // Enhanced debug logging.
        $debug_info = array(
            'row'             => $row_number,
            'title_csv'       => $title,
            'title_normalized' => $normalized_title,
            'title_lowercase' => $lowercase_title,
            'title_slug'      => $slug_title,
            'found_post_id'   => $existing_post_id,
            'found_title'     => $existing_post ? $existing_post->post_title : 'None',
            'found_normalized' => $existing_post ? normalize_book_title( $existing_post->post_title ) : 'None',
        );
        $debug_log[] = $debug_info;
        
        if ($existing_post && !$update_existing) {
            $skipped_count++;
            continue; // Skip if exists and not updating
        }
        
        // Prepare post data
        $post_data = array(
            'post_title' => $title,
            'post_status' => 'publish',
            'post_type' => 'post',
        );
        
        // Only set empty content for NEW posts, preserve existing content for updates
        if (!($existing_post && $update_existing)) {
            $post_data['post_content'] = ''; // Empty content only for new books
        }
        
        if ($existing_post && $update_existing) {
            $post_data['ID'] = $existing_post->ID;
            $post_id = wp_update_post($post_data);
            $updated_count++;
        } else {
            $post_id = wp_insert_post($post_data);
            $imported_count++;
        }
        
        // Add custom fields if post was created/updated successfully
        if ($post_id && !is_wp_error($post_id)) {
            if (!empty($author)) {
                update_field('author', $author, $post_id);
            }
            if (!empty($series)) {
                update_field('series', $series, $post_id);
            }
            if (!empty($series_number)) {
                update_field('series_number', $series_number, $post_id);
            }
            if (!empty($rating)) {
                update_field('rating', floatval($rating), $post_id);
            }
        }
    }
    
    fclose($file_handle);
    
    // Create detailed debug report.
    $debug_report = "Import Results:\n";
    $debug_report .= "- {$imported_count} books imported (new)\n";
    $debug_report .= "- {$updated_count} books updated (existing)\n";
    $debug_report .= "- {$skipped_count} books skipped\n\n";
    
    $debug_report .= "Debug Details (showing all rows):\n";
    $debug_report .= str_repeat( "=", 80 ) . "\n";
    
    foreach ( $debug_log as $debug ) {
        $debug_report .= "Row {$debug['row']}:\n";
        $debug_report .= "  CSV Title: '{$debug['title_csv']}'\n";
        $debug_report .= "  Normalized: '{$debug['title_normalized']}'\n";
        $debug_report .= "  Lowercase: '{$debug['title_lowercase']}'\n";
        $debug_report .= "  Slug: '{$debug['title_slug']}'\n";
        
        if ( $debug['found_post_id'] ) {
            $debug_report .= "  MATCH FOUND: Post ID {$debug['found_post_id']}\n";
            $debug_report .= "  DB Title: '{$debug['found_title']}'\n";
            $debug_report .= "  DB Normalized: '{$debug['found_normalized']}'\n";
            $debug_report .= "  Action: " . ( $update_existing ? 'UPDATED' : 'SKIPPED' ) . "\n";
        } else {
            $debug_report .= "  NO MATCH FOUND\n";
            $debug_report .= "  Action: CREATED NEW POST\n";
        }
        
        $debug_report .= str_repeat( "-", 40 ) . "\n";
    }
    
    // Also add a section showing existing post titles in the database for comparison.
    $debug_report .= "\nExisting Posts in Database:\n";
    $debug_report .= str_repeat( "=", 80 ) . "\n";
    
    global $wpdb;
    $existing_posts = $wpdb->get_results(
        "SELECT ID, post_title FROM {$wpdb->posts} 
         WHERE post_type = 'post' 
         AND post_status IN ('publish', 'draft', 'private')
         ORDER BY post_title"
    );
    
    foreach ( $existing_posts as $post ) {
        $db_normalized = normalize_book_title( $post->post_title );
        $debug_report .= "ID {$post->ID}: '{$post->post_title}' -> '{$db_normalized}'\n";
    }
    
    // Save debug to temporary file for inspection
    file_put_contents(sys_get_temp_dir() . '/books_import_debug.txt', $debug_report);
    
    // Redirect with success message
    $message = "Import completed: {$imported_count} books imported";
    if ($updated_count > 0) {
        $message .= ", {$updated_count} books updated";
    }
    if ($skipped_count > 0) {
        $message .= ", {$skipped_count} books skipped";
    }
    $message .= ". Debug log saved to temp directory.";
    
    wp_redirect(admin_url('tools.php?page=books-csv-tools&import_result=success&message=' . urlencode($message)));
    exit;
}

// Enhanced title normalization function.
function normalize_book_title( $title ) {
    // Start with the original title.
    $normalized = $title;
    
    // Step 1: Decode HTML entities (all types including numeric).
    $normalized = html_entity_decode( $normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    
    // Step 2: Handle smart quotes and special characters using hex codes.
    $search_chars = array(
        "\xE2\x80\x9C", // Left double quotation mark.
        "\xE2\x80\x9D", // Right double quotation mark.
        "\xE2\x80\x98", // Left single quotation mark.
        "\xE2\x80\x99", // Right single quotation mark (apostrophe).
        "\xE2\x80\x93", // En dash.
        "\xE2\x80\x94", // Em dash.
        "\xE2\x80\xa6", // Horizontal ellipsis.
        chr( 8220 ),     // Left double quote.
        chr( 8221 ),     // Right double quote.
        chr( 8216 ),     // Left single quote.
        chr( 8217 ),     // Right single quote.
        chr( 8211 ),     // En dash.
        chr( 8212 ),     // Em dash.
    );
    $replace_chars = array( '"', '"', "'", "'", '-', '-', '...', '"', '"', "'", "'", '-', '-' );
    $normalized = str_replace( $search_chars, $replace_chars, $normalized );
    
    // Step 3: Convert to UTF-8 if not already.
    if ( ! mb_check_encoding( $normalized, 'UTF-8' ) ) {
        $normalized = mb_convert_encoding( $normalized, 'UTF-8', 'auto' );
    }
    
    // Step 4: Remove all invisible/control characters.
    $normalized = preg_replace( '/[\x00-\x1F\x7F-\x9F\xAD]/', '', $normalized );
    
    // Step 5: Normalize whitespace (including non-breaking spaces).
    $normalized = preg_replace( '/[\s\xA0\x{00A0}\x{2000}-\x{200B}\x{2028}\x{2029}]+/u', ' ', $normalized );
    
    // Step 6: Remove zero-width characters.
    $normalized = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalized );
    
    // Step 7: Trim whitespace.
    $normalized = trim( $normalized );
    
    return $normalized;
}

// Add export link to admin bar (optional)
add_action('admin_bar_menu', 'lc_add_csv_export_admin_bar', 100);

function lc_add_csv_export_admin_bar($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_menu(array(
        'id' => 'books-csv-tools',
        'title' => 'Books CSV Tools',
        'href' => admin_url('tools.php?page=books-csv-tools'),
        'meta' => array(
            'title' => 'Import/Export books CSV files'
        )
    ));
}
