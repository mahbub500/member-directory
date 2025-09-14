<?php

/**
 * Example helper function
 */
function md_get_plugin_version() {
    return MD_VERSION;
}

if ( ! function_exists( 'md_handle_image_upload' ) ) {
    /**
     * Handle file upload and save to plugin folder
     *
     * @param array $file $_FILES array for the uploaded file
     * @param string $prefix Prefix for the saved file name (e.g., 'profile', 'cover')
     * @return string|false Relative file path on success, false on failure
     */
    function md_handle_image_upload( $file, $prefix = 'file') {
        if (empty($file['name'])) {
            return false; // No file uploaded
        }

        // Upload folder inside plugin
        $upload_dir = MD_PLUGIN_DIR . 'uploads/members/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir); // create folder if not exists
        }

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = $prefix . '_' . time() . '.' . $ext;
        $dest = $upload_dir . $name;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/members/' . $name; // return relative path
        }

        return false; // failed
    }

}
