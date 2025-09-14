<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Member {

    use Hook;

    public function __construct() {
        $this->register_ajax( 'md_add_member', [ $this, 'add_member' ] );

        $this->register_ajax( 'md_delete_member', [ $this, 'delete_member' ] );

        $this->register_ajax( 'md_update_member', [ $this, 'update_member' ] );

        $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );

        $this->action( 'admin_footer', [$this, 'footer'] );
    }

    public function members_page() {
    // Pagination setup
    $page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;

    // Fetch paginated data
    $result         = get_data('md_members', $page, 10);
    $members        = $result['data'];
    $total_pages    = $result['total_pages'];
    $current_page   = $result['current_page'];
    $total_members  = $result['total_items'];
    ?>

    <div class="container-fluid p-4" id="md-all-members">
        <h1 class="mb-4"><?php esc_html_e('Members', 'member-directory'); ?></h1>

        <div class="row g-4">

            <!-- Left Column: Add Member Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <?php esc_html_e('Add Member', 'member-directory'); ?>
                    </div>
                    <div class="card-body">
                        <form id="md-add-member-form" class="row g-3" enctype="multipart/form-data">
                            <?php wp_nonce_field('md_nonce', 'security'); ?>

                            <!-- First Name -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('First Name', 'member-directory'); ?></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>

                            <!-- Last Name -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('Last Name', 'member-directory'); ?></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('Email', 'member-directory'); ?></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <!-- Profile Image -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('Profile Image', 'member-directory'); ?></label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                <div id="profile-image-preview" class="mt-2"></div>
                            </div>

                            <!-- Cover Image -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('Cover Image', 'member-directory'); ?></label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <div id="cover-image-preview" class="mt-2"></div>
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label"><?php esc_html_e('Address', 'member-directory'); ?></label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>

                            <!-- Favorite Color -->
                            <div class="col-6">
                                <label class="form-label"><?php esc_html_e('Favorite Color', 'member-directory'); ?></label>
                                <input type="color" name="favorite_color" class="form-control form-control-color" value="#000000">
                            </div>

                            <!-- Status -->
                            <div class="col-6">
                                <label class="form-label"><?php esc_html_e('Status', 'member-directory'); ?></label>
                                <select name="status" class="form-select">
                                    <option value="active"><?php esc_html_e('Active', 'member-directory'); ?></option>
                                    <option value="draft"><?php esc_html_e('Draft', 'member-directory'); ?></option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <?php esc_html_e('Add Member', 'member-directory'); ?>
                                </button>
                            </div>
                        </form>

                        <div id="md-member-message" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Members Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <?php esc_html_e('All Members', 'member-directory'); ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-hover align-middle md-members-table">
                            <thead class="table-dark">
                                <tr>
                                    <th><?php esc_html_e('ID', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Profile', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Cover', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Name', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Email', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Address', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Color', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Status', 'member-directory'); ?></th>
                                    <th><?php esc_html_e('Actions', 'member-directory'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="md-members-list">
                                <?php foreach ($members as $m): ?>
                                    <tr class="md-member-row"
                                        data-id="<?php echo esc_attr($m->id); ?>"
                                        data-firstname="<?php echo esc_attr($m->first_name); ?>"
                                        data-lastname="<?php echo esc_attr($m->last_name); ?>"
                                        data-email="<?php echo esc_attr($m->email); ?>"
                                        data-address="<?php echo esc_attr($m->address); ?>"
                                        data-color="<?php echo esc_attr($m->favorite_color); ?>"
                                        data-status="<?php echo esc_attr($m->status); ?>"
                                        data-profile="<?php echo esc_url($m->profile_image); ?>"
                                        data-cover="<?php echo esc_url($m->cover_image); ?>"
                                    >
                                        <td><?php echo esc_html($m->id); ?></td>

                                        <!-- Profile -->
                                        <td>
                                            <?php if (!empty($m->profile_image)): ?>
                                                <img src="<?php echo esc_url($m->profile_image); ?>" 
                                                     alt="Profile" 
                                                     style="width:40px;height:40px;border-radius:50%;cursor:pointer;">
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Cover -->
                                        <td>
                                            <?php if (!empty($m->cover_image)): ?>
                                                <img src="<?php echo esc_url($m->cover_image); ?>" 
                                                     alt="Cover" 
                                                     style="width:60px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;">
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Name & Email -->
                                        <td><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></td>
                                        <td><?php echo esc_html($m->email); ?></td>
                                        <td><?php echo esc_html($m->address ?? ''); ?></td>

                                        <!-- Color -->
                                        <td>
                                            <span style="background:<?php echo esc_attr($m->favorite_color); ?>;
                                                         padding:5px 15px;display:inline-block;border-radius:4px;">
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td><?php echo esc_html($m->status); ?></td>

                                        <!-- Actions -->
                                        <td>
                                            <button class="btn btn-sm btn-primary md-edit-member" 
                                                    data-id="<?php echo esc_attr($m->id); ?>">
                                                <?php esc_html_e('Edit', 'member-directory'); ?>
                                            </button>
                                            <button class="btn btn-sm btn-danger md-delete-member" 
                                                    data-id="<?php echo esc_attr($m->id); ?>">
                                                <?php esc_html_e('Delete', 'member-directory'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination mt-3">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo add_query_arg('page_num', $i); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div><!-- .row -->
    </div><!-- .container-fluid -->

    <?php
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


    function update_member() {
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'md_nonce') ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_members';

        $id = intval($_POST['id']);
        $data = [
            'first_name'     => sanitize_text_field($_POST['first_name']),
            'last_name'      => sanitize_text_field($_POST['last_name']),
            'email'          => sanitize_email($_POST['email']),
            'address'        => sanitize_text_field($_POST['address']),
            'favorite_color' => sanitize_text_field($_POST['favorite_color']),
            'status'         => sanitize_text_field($_POST['status']),
        ];

        // ✅ Handle Profile Image
        if (!empty($_FILES['profile_image']['name'])) {
            $upload = wp_handle_upload($_FILES['profile_image'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $data['profile_image'] = esc_url($upload['url']);
            }
        }

        // ✅ Handle Cover Image
        if (!empty($_FILES['cover_image']['name'])) {
            $upload = wp_handle_upload($_FILES['cover_image'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $data['cover_image'] = esc_url($upload['url']);
            }
        }

        $updated = $wpdb->update($table, $data, ['id' => $id]);

        if ($updated !== false) {
            wp_send_json_success([
                'message' => 'Member updated successfully',
                'member'  => array_merge(['id' => $id], $data)
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to update member']);
        }
    }

    public function footer(){
        ?>
         <div class="modal fade" id="md-image-modal" tabindex="-1" aria-labelledby="mdImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 overflow-hidden">
            
            <!-- Cover Image -->
            <div class="position-relative">
                <img src="" id="md-modal-cover" class="img-fluid w-100" style="height:200px; object-fit:cover;" alt="Cover">
                
                <!-- Profile Image -->
                <img src="" id="md-modal-profile" class="rounded-circle border border-white position-absolute"
                     style="width:120px; height:120px; bottom:-60px; left:30px; object-fit:cover; background:#fff;" alt="Profile">
            </div>
            
    <div class="modal-body pt-5">
        <h5 class="modal-title mb-3 mt-3" id="mdImageModalLabel">Member Details</h5>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th style="width:150px;">Name:</th>
                <td><span id="md-modal-name"></span></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
              <td><span id="md-modal-email"></span></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><span id="md-modal-address"></span></td>
            </tr>
            <tr>
                <th>Favorite Color:</th>
                <td>
                    <span id="md-modal-color" style="padding:5px 15px; display:inline-block; border-radius:4px;"></span>
                </td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><span id="md-modal-status"></span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
    </div>
</div>
                        <!-- Edit Modal -->
        <div class="modal fade" id="md-edit-modal" tabindex="-1" aria-labelledby="mdEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="md-edit-member-form" enctype="multipart/form-data">
          <input type="hidden" name="id" id="md-edit-id">

          <div class="row g-3">
            <!-- Profile Image -->
            <div class="col-md-6 text-center">
              <label class="form-label d-block">Profile Image</label>
              <img id="md-edit-profile-preview" 

              src="" class="img-thumbnail mb-2" style="width:120px;height:120px;object-fit:cover;">
              <input type="file" name="profile_image" class="form-control" accept="image/*">
            </div>

            <!-- Cover Photo -->
            <div class="col-md-6 text-center">
              <label class="form-label d-block">Cover Photo</label>
              <img id="md-edit-cover-preview" src="" class="img-thumbnail mb-2"
              
              style="width:100%;height:120px;object-fit:cover;">
              <input type="file" name="cover_image" class="form-control" accept="image/*">
            </div>

            <!-- Text Fields -->
            <div class="col-md-6">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" id="md-edit-firstname" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" id="md-edit-lastname" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="md-edit-email" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea name="address" id="md-edit-address" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Favorite Color</label>
              <input type="color" name="favorite_color" id="md-edit-color" class="form-control form-control-color">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="md-edit-status" class="form-select">
                <option value="active">Active</option>
                <option value="draft">Draft</option>
              </select>
            </div>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
        <?php  
    }

}