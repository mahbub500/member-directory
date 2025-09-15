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

if ( ! function_exists( 'get_members_by_team' ) ) {
    /**
     * Get all members for a specific team.
     *
     * @param int $team_id The team ID.
     * @return array List of member objects
     */
    function get_members_by_team( $team_id ) {
        global $wpdb;

        $rel_table = $wpdb->prefix . 'md_member_team_relations';
        $members_table = $wpdb->prefix . 'md_members';

        // Get member_ids string
        $member_ids_str = $wpdb->get_var(
            $wpdb->prepare("SELECT member_ids FROM $rel_table WHERE team_id = %d", $team_id)
        );

        if (!$member_ids_str) {
            return []; // No members
        }

        $member_ids = array_map('intval', explode(',', $member_ids_str));

        // Fetch member details
        $placeholders = implode(',', array_fill(0, count($member_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT * FROM $members_table WHERE id IN ($placeholders)",
            ...$member_ids
        );

        return $wpdb->get_results($query);
    }
}

if ( ! function_exists( 'get_member_full_name' ) ) {
    /**
     * Get member full name by ID
     */
    function get_member_full_name( $member_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'md_members';

        $member = $wpdb->get_row(
            $wpdb->prepare("SELECT first_name, last_name FROM $table WHERE id = %d", $member_id)
        );

        return $member ? trim($member->first_name . ' ' . $member->last_name) : null;
    }

}

if ( ! function_exists( 'get_all_ids' ) ) {
    /**
     * Get all IDs from members or teams
     *
     * @param string $type 'member' or 'team'
     * @return array List of IDs
     */
    function get_all_ids( $type = 'member' ) {
        global $wpdb;

        if ( $type === 'team' ) {
            $table = $wpdb->prefix . 'teams';
        } else {
            $table = $wpdb->prefix . 'members';
        }

        $ids = $wpdb->get_col( "SELECT id FROM $table ORDER BY id ASC" );

        return $ids ? array_map( 'intval', $ids ) : [];
    }
}

if ( ! function_exists( 'get_member_profile_image_by_id' ) ) {
    /**
     * Get member profile image by ID
     */
    function get_member_profile_image_by_id( $member_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'md_members';

        $profile_image = $wpdb->get_var(
            $wpdb->prepare("SELECT profile_image FROM $table WHERE id = %d", $member_id)
        );

        return $profile_image ? esc_url($profile_image) : 'https://via.placeholder.com/40';
    }
}
