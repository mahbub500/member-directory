<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Shortcode {

    use Hook;

    public function __construct() {
        $this->shortcode( 'team_dashboard', [ $this, 'team' ] );
    }

    public function team() {

        if ( ! is_user_logged_in() ) {
            return esc_html__( 'Please log in to see your team.', 'member-directory' );
        }

        $current_user_id = get_current_user_id();
        global $wpdb;

        // 1. Get user's team
        $rel_table = $wpdb->prefix . 'md_member_team_relations';

        $team_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT team_id FROM $rel_table WHERE FIND_IN_SET(%d, member_ids)",
                $current_user_id
            )
        );

        if ( ! $team_id ) {
            return esc_html__( 'You are not assigned to any team yet.', 'member-directory' );
        }

        // 2. Get team name
        $team_name = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}md_teams WHERE id=%d",
                $team_id
            )
        );

        // 3. Get all team members' IDs
        $member_ids_str = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT member_ids FROM $rel_table WHERE team_id=%d",
                $team_id
            )
        );

        $member_ids = ! empty( $member_ids_str ) ? array_map( 'intval', explode( ',', $member_ids_str ) ) : [];

        if ( empty( $member_ids ) ) {
            return esc_html__( 'No members in your team yet.', 'member-directory' );
        }

        // 4. Get WP users by IDs
        $members = get_users([
            'include' => $member_ids,
            'orderby' => 'display_name',
            'order'   => 'ASC'
        ]);

        // 5. Output
        ob_start();
        ?>
        <div class="my-team-dashboard container my-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo esc_html__( 'Your Team:', 'member-directory' ) . ' ' . esc_html( $team_name ); ?></h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ( $members as $m ): 
                            $profile_img = get_user_profile_image( $m->ID ) ?: 'https://via.placeholder.com/40';
                            $full_name   = get_user_full_name( $m->ID );
                        ?>
                            <li class="list-group-item d-flex align-items-center">
                                <img src="<?php echo esc_url( $profile_img ); ?>" 
                                     alt="<?php echo esc_attr( $full_name ); ?>" 
                                     class="rounded-circle me-3" 
                                     width="50" height="50">
                                <span class="fw-medium"><?php echo esc_html( $full_name ); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


}