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

        // 4. Get WP users by IDs
        $members = [];
        if ( ! empty( $member_ids ) ) {
            $members = get_users([
                'include'    => $member_ids, 
                'orderby'    => 'display_name',
                'order'      => 'ASC',
                'meta_query' => [
                    [
                        'key'   => 'status',
                        'value' => 'active',
                    ],
                ],
            ]);

        }

        // 5. Query all users with md_meta = 'md'
        $args = [
            'meta_key'       => 'md_meta',
            'meta_value'     => 'md',
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'fields'         => 'all_with_meta',
        ];
        $all_members = get_users( $args );

        // 6. Output
        ob_start();
        ?>
        <div class="my-team-dashboard container my-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <?php echo esc_html__( 'Your Team:', 'member-directory' ) . ' ' . esc_html( $team_name ) ; ?>
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="teamTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="team-members-tab" data-bs-toggle="tab" data-bs-target="#team-members" type="button" role="tab" aria-controls="team-members" aria-selected="true">
                                <?php esc_html_e( 'Team Members', 'member-directory' ); ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab" aria-controls="chat" aria-selected="false">
                                <?php esc_html_e( 'Team Chat', 'member-directory' ); ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-teams-tab" data-bs-toggle="tab" data-bs-target="#all-teams" type="button" role="tab" aria-controls="all-teams" aria-selected="false">
                                <?php esc_html_e( 'All Teams', 'member-directory' ); ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-members-tab" data-bs-toggle="tab" data-bs-target="#all-members" type="button" role="tab" aria-controls="all-members" aria-selected="false">
                                <?php esc_html_e('All Members','member-directory'); ?>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="teamTabsContent">

                        <!-- Team Members -->
                        <div class="tab-pane fade show active" id="team-members" role="tabpanel" aria-labelledby="team-members-tab">
                            <?php if ( ! empty( $members ) ) : ?>
                                <ul class="list-group list-group-flush mb-3">
                                    <?php foreach ( $members as $m ): ?>
                                        <li class="list-group-item d-flex align-items-center">
                                            <a href="<?php echo esc_url( get_user_profile_url( $m->ID ) ); ?>" class="d-flex align-items-center text-decoration-none w-100">
                                                <img src="<?php echo esc_url( get_user_profile_image( $m->ID ) ); ?>" 
                                                     alt="<?php echo esc_attr( get_user_full_name( $m->ID ) ); ?>" 
                                                     class="rounded-circle me-3" 
                                                     width="50" height="50">
                                                <div>
                                                    <span class="fw-medium"><?php echo esc_html( get_user_full_name( $m->ID ) ); ?></span> 
                                                    <br>
                                                    <small class="text-muted"><?php echo esc_html( get_user_email( $m->ID ) ); ?></small>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p class="text-muted"><?php esc_html_e( 'No members in your team yet.', 'member-directory' ); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Team Chat -->
                        <div class="tab-pane fade" id="chat" role="tabpanel" aria-labelledby="chat-tab">
                            <div class="card border shadow-sm mb-3">
                                <div id="chat-messages" class="p-3" style="height:250px; overflow-y:auto; background:#f8f9fa;">
                                    <!-- Messages will load here via AJAX -->
                                </div>
                                <form id="team-chat-form" class="d-flex border-top p-2" data-team-id="<?php echo esc_attr($team_id); ?>">
                                    <input type="text" id="chat-message-input" class="form-control me-2" placeholder="<?php esc_attr_e('Type your message…','member-directory'); ?>" required>
                                    <button type="submit" class="btn btn-success"><?php esc_html_e('Send','member-directory'); ?></button>
                                </form>
                            </div>
                        </div>

                        <!-- All Teams -->
                        <div class="tab-pane fade" id="all-teams" role="tabpanel" aria-labelledby="all-teams-tab">
                            <ul class="list-group list-group-flush">
                                <?php
                                $all_teams = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}md_teams ORDER BY name ASC" );
                                if ( $all_teams ) :
                                    foreach ( $all_teams as $team ) :
                                ?>
                                        <li class="list-group-item">
                                            <span><?php echo $team->name ?></span>
                                        </li>
                                <?php
                                    endforeach;
                                else :
                                    echo '<li class="list-group-item text-muted">' . esc_html__( 'No teams found.', 'member-directory' ) . '</li>';
                                endif;
                                ?>
                            </ul>
                        </div>

                        <!-- All Members -->
                        <div class="tab-pane fade" id="all-members" role="tabpanel" aria-labelledby="all-members-tab">
                            <div class="list-group">
                                <?php foreach ( $all_members as $member ) : ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo esc_html( $member->display_name ); ?></span>
                                        <small class="text-muted"><?php echo esc_html( $member->user_email ); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div><!-- .tab-content -->
                </div><!-- .card-body -->
            </div><!-- .card -->
        </div><!-- .container -->
        <?php

    return ob_get_clean();
}




}