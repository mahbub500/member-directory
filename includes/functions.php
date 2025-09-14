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
