<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Assign {

    use Hook;

    public function __construct() {
        $this->register_ajax( 'md_add_member', [ $this, 'add_member' ] );
        $this->register_ajax( 'md_assign_to_team', [ $this, 'assign_to_team' ] );
        $this->register_ajax( 'md_remove_from_team', [ $this, 'remove_from_team' ] );

        // $this->register_ajax( 'md_delete_member', [ $this, 'delete_member' ] );

        // $this->register_ajax( 'md_update_member', [ $this, 'update_member' ] );

        // $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );

        // $this->action( 'admin_footer', [$this, 'footer'] );
    }

    public function assign_to_team() {
        // ✅ Nonce check
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'] ) ) {
            wp_send_json_error([ 'message' => 'Invalid nonce' ]);
            return;
        }

        $member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;
        $team_id   = isset($_POST['team_id'])   ? intval($_POST['team_id'])   : 0;

        if ( empty($member_id) || empty($team_id) ) {
            wp_send_json_error([ 'message' => 'Invalid data.' ]);
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_member_team_relations'; // relation table

        // ✅ Get existing members for this team
        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT member_ids FROM $table WHERE team_id=%d", $team_id)
        );

        // Convert to array
        $members = $existing ? explode(',', $existing) : [];

        // Check for duplicate
        if (in_array($member_id, $members)) {
            wp_send_json_error([ 'message' => 'Member already in this team.' ]);
            return;
        }

        // Add new member
        $members[] = $member_id;
        $member_ids_str = implode(',', $members);

        // Update if exists, else insert new row
        if ($existing) {
            $updated = $wpdb->update(
                $table,
                ['member_ids' => $member_ids_str, 'assigned_at' => current_time('mysql')],
                ['team_id' => $team_id],
                ['%s', '%s'],
                ['%d']
            );

            if ($updated === false) {
                wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
                return;
            }
        } else {
            $inserted = $wpdb->insert(
                $table,
                ['team_id' => $team_id, 'member_ids' => $member_ids_str, 'assigned_at' => current_time('mysql')],
                ['%d', '%s', '%s']
            );

            if ($inserted === false) {
                wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
                return;
            }
        }

        wp_send_json_success([
            'message' => 'Member assigned successfully.',
            'member_ids' => $member_ids_str
        ]);
    }

    public function remove_from_team() {
        // Nonce check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'] )) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        $member_id = intval($_POST['member_id']);
        $team_id   = intval($_POST['team_id']);

        if (!$member_id || !$team_id) {
            wp_send_json_error(['message' => 'Invalid data.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_member_team_relations';

        // Get existing member_ids
        $row = $wpdb->get_row($wpdb->prepare("SELECT member_ids FROM $table WHERE team_id=%d", $team_id));

        if (!$row) {
            wp_send_json_error(['message' => 'Team not found.']);
        }

        $member_ids = explode(',', $row->member_ids);
        $member_ids = array_map('trim', $member_ids);

        // Remove the member ID
        $member_ids = array_diff($member_ids, [$member_id]);

        if (empty($member_ids)) {
            // No members left, optionally delete the row
            $deleted = $wpdb->delete($table, ['team_id' => $team_id]);
        } else {
            // Update the member_ids list
            $updated = $wpdb->update(
                $table,
                ['member_ids' => implode(',', $member_ids)],
                ['team_id' => $team_id]
            );
        }

        wp_send_json_success(['message' => 'Member removed from team.']);
    }




    public function assign_page () {
        /**
         * Pagination data loading (already in your code)
         */
        $page    = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
        $members = get_data('md_members', $page, 10);
        $page      = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
        $per_page  = 10;
        $offset    = ($page - 1) * $per_page;

        // Get all members (users with md_meta = 'md')
        $members_args = [
            'meta_key'   => 'md_meta',
            'meta_value' => 'md',
            'number'     => $per_page,
            'offset'     => $offset,
            'orderby'    => 'ID',
            'order'      => 'ASC',
            'fields'     => 'all_with_meta',
        ];
        $all_members = get_users( $members_args );

        $teams = get_data( 'md_teams' );

        ?>

        <div class="container-fluid p-4">
          <div class="row">

            <!-- Members List -->
        <div class="col-md-5">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">All Members</div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
              <ul id="members-list" class="list-group">
                  <?php foreach ($all_members as $m): ?>
                    <li class="list-group-item member-item d-flex align-items-center" data-id="<?php echo esc_attr($m->ID); ?>">
                        <img src="<?php echo get_user_profile_image( $m->ID ) ?>" alt="Profile" class="rounded-circle me-2" width="30" height="30">
                        <span class="member-name"><?php echo get_user_full_name( $m->ID ) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
            </div>
          </div>
        </div>

            <!-- Teams List -->
            <div class="col-md-7">
              <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">Teams</div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                  <?php foreach ($teams['data'] as $t): ?>
                    <div class="team-container mb-3 p-2 border rounded" data-team-id="<?php echo esc_attr($t->id); ?>">
                      <h5><?php echo esc_html($t->name); ?></h5>

                      <!-- Team Members -->
                      <ul class="team-members list-group mb-2" style="max-height: 200px; overflow-y: auto;">
                        <?php
                        $team_members = get_members_by_team($t->id);
                        foreach ($team_members as $tm):
                            // echo $tm;
                        $profile_img = get_user_meta($tm->ID, 'profile_image', true) ?: 'https://via.placeholder.com/40';
                        ?>
                          <li class="list-group-item member-item d-flex align-items-center"
                              data-id="<?php echo esc_attr($tm->ID); ?>">

                            <!-- Mini Profile Image -->
                            <img src="<?php echo get_user_profile_image( $tm->ID ) ?>"
                                 alt="Profile"
                                 class="rounded-circle me-2"
                                 width="30" height="30">

                            <!-- Member Name -->
                            <span><?php echo get_user_full_name( $tm->ID ); ?></span>

                            <!-- Remove Button -->
                            <button class="btn btn-sm btn-danger ms-auto md-remove-member"
                                    data-member-id="<?php echo esc_attr($tm->ID); ?>"
                                    data-team-id="<?php echo esc_attr($t->id); ?>">×</button>
                          </li>
                        <?php endforeach; ?>
                        <li class="team-members-li"></li>
                      </ul>

                      <small class="text-muted">Drag members here</small>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

          </div>
        </div>
        <?php
    }
}