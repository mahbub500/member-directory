<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ajax {

    use Hook;

    public function __construct() {
        $this->register_ajax( 'md_add_member', [ $this, 'add_member' ] );

        $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );
    }

    /** ================= AJAX ================= */
    public function add_member() {
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce']  ) ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_members';

        // Optional: check if email exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE email=%s", $_POST['email']));
        if($exists) {
            wp_send_json_error(['message'=>'Email already exists']);
            return;
        }

        // Handle images
        $profile_image = md_handle_image_upload($_FILES['profile_image'], 'profile');
        $cover_image   = md_handle_image_upload($_FILES['cover_image'], 'cover');

        $wpdb->insert($table, [
            'first_name'     => sanitize_text_field($_POST['first_name']),
            'last_name'      => sanitize_text_field($_POST['last_name']),
            'email'          => sanitize_email($_POST['email']),
            'profile_image'  => $profile_image,
            'cover_image'    => $cover_image,
            'favorite_color' => sanitize_text_field($_POST['favorite_color']),
            'status'         => sanitize_text_field($_POST['status']),
            'created_at'     => current_time('mysql'),
        ]);

        wp_send_json_success([ 'message' => 'Member added successfully.' ]);
    }


    public function add_team() {
        // check_ajax_referer( 'md_nonce', 'security' );
        global $wpdb;

        $table = $wpdb->prefix . 'md_teams';
        $wpdb->insert( $table, [
            'name'              => sanitize_text_field($_POST['name']),
            'short_description' => sanitize_textarea_field($_POST['short_description']),
        ] );

        wp_send_json_success([ 'message' => 'Team added successfully.' ]);
    }
}