<?php

/**
 * Example helper function
 */
function md_get_plugin_version() {
    return MD_VERSION;
}

if ( ! function_exists( 'md_handle_image_upload' ) ) {
    /**
     * Handle file upload using WordPress media system and compress image
     *
     * @param array  $file   $_FILES array for the uploaded file
     * @param string $prefix Prefix for the file name
     * @return string|false URL of uploaded file on success, false on failure
     */
    function md_handle_image_upload($file, $prefix = 'file') {
        if (empty($file['name'])) {
            return false;
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Let WP handle the upload
        $uploaded = wp_handle_upload($file, [
            'test_form' => false,
            'mimes'     => [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'          => 'image/png',
                'gif'          => 'image/gif',
                'webp'         => 'image/webp'
            ]
        ]);

        if (isset($uploaded['file'])) {
            $file_path = $uploaded['file'];
            $file_url  = $uploaded['url'];

            // Compress / resize the image using wp_generate_attachment_metadata
            $image_type = wp_check_filetype($file_path);
            if (strpos($image_type['type'], 'image') !== false) {
                $wp_filetype = wp_check_filetype(basename($file_path), null);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => sanitize_file_name($prefix . '_' . time()),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ];
                $attach_id = wp_insert_attachment($attachment, $file_path);

                $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // Return the URL of the uploaded image
                return $file_url;
            }

            return false;
        }

        return false;
    }
}

if ( ! function_exists( 'get_data' ) ) {
    /**
     * Get paginated data from a table
     *
     * @param string $table_name Table name (with or without $wpdb->prefix)
     * @param int $page Current page number
     * @param int $per_page Items per page
     * @return array {
     *     @type array $data List of rows as objects
     *     @type int $total_items Total rows in table
     *     @type int $total_pages Total pages
     *     @type int $current_page Current page
     *     @type int $per_page Items per page
     * }
     */
    function get_data( $table_name, $page = 1, $per_page = 10 ) {
        global $wpdb;

        // Ensure table has prefix
        if (strpos($table_name, $wpdb->prefix) !== 0) {
            $table_name = $wpdb->prefix . $table_name;
        }

        $page = max(1, intval($page));
        $per_page = max(1, intval($per_page));
        $offset = ($page - 1) * $per_page;

        // Fetch data
        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // Total rows
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        $total_pages = ceil($total_items / $per_page);

        return [
            'data'         => $data,
            'total_items'  => $total_items,
            'total_pages'  => $total_pages,
            'current_page' => $page,
            'per_page'     => $per_page,
        ];
    }

}
