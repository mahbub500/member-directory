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

        $this->register_ajax( 'md_delete_member', [ $this, 'delete_member' ] );

        $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );
    }

    /** ================= AJAX ================= */
    public function add_member() {
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'] ) ) {
            wp_send_json_error(['data' => 'Invalid nonce']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_members';

        $profile_image = md_handle_image_upload($_FILES['profile_image'], 'profile');
        $cover_image   = md_handle_image_upload($_FILES['cover_image'], 'cover');

        $wpdb->insert($table, [
            'first_name'     => sanitize_text_field($_POST['first_name']),
            'last_name'      => sanitize_text_field($_POST['last_name']),
            'email'          => sanitize_email($_POST['email']),
            'address'        => sanitize_text_field($_POST['address']),
            'profile_image'  => $profile_image,
            'cover_image'    => $cover_image,
            'favorite_color' => sanitize_text_field($_POST['favorite_color']),
            'status'         => sanitize_text_field($_POST['status']),
            'created_at'     => current_time('mysql'),
        ]);

        $member_id = $wpdb->insert_id;

        // Get the full member data
        $member = $wpdb->get_row("SELECT * FROM {$table} WHERE id = {$member_id}");

        wp_send_json_success([
            'data'   => 'Member added successfully.',
            'member' => $member
        ]);
    }

    function delete_member() {
        // Check nonce
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'] ) ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        if ( empty($_POST['member_id']) ) {
            wp_send_json_error(['message' => 'Member ID is required']);
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_members';
        $id    = intval($_POST['member_id']);

        $deleted = $wpdb->delete($table, ['id' => $id]);

        if ( $deleted ) {
            wp_send_json_success(['message' => 'Member deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete member']);
        }
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