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

        $this->action( 'admin_footer', [$this, 'footer'] );
    }

    public function members_page() {
        // Pagination setup
        $page       = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
        $per_page   = 10;
        $offset     = ($page - 1) * $per_page;

        // Query all users with md_meta = 'md'
        $args = [
            'meta_key'       => 'md_meta',
            'meta_value'     => 'md',
            'number'         => $per_page,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'fields'         => 'all_with_meta',
        ];

        $all_users = get_users( $args );

        // Total users for pagination
        $total_members = count( get_users([
            'meta_key'   => 'md_meta',
            'meta_value' => 'md',
            'fields'     => 'ID',
        ]) );

        $total_pages = ceil($total_members / $per_page);
        $current_page = $page;
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
                           
<table class="table table-striped table-hover align-middle members-table">
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
        <?php foreach ($all_users as $user): 
            $profile_image = md_get_user_profile_image( $user->id );
            $cover_image   = get_user_meta($user->ID, 'cover_image', true) ?: 'https://via.placeholder.com/60x40';
            $address       = get_user_meta($user->ID, 'address', true);
            $color         = get_user_meta($user->ID, 'favorite_color', true);
            $status        = get_user_meta($user->ID, 'status', true);
        ?>
        <tr class="md-member-row" data-id="<?php echo esc_attr($user->ID); ?>">
            <td><?php echo esc_html($user->ID); ?></td>

            <!-- Profile -->
            <td>
                <img src="<?php echo esc_url($profile_image); ?>" 
                     alt="Profile" 
                     style="width:40px;height:40px;border-radius:50%;cursor:pointer;">
            </td>

            <!-- Cover -->
            <td>
                <img src="<?php echo esc_url($cover_image); ?>" 
                     alt="Cover" 
                     style="width:60px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;">
            </td>

            <!-- Name & Email -->
            <td><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></td>
            <td><?php echo esc_html($user->user_email); ?></td>
            <td><?php echo esc_html($address ?? ''); ?></td>

            <!-- Color -->
            <td>
                <span style="background:<?php echo esc_attr($color); ?>;
                             padding:5px 15px;display:inline-block;border-radius:4px;">
                </span>
            </td>

            <!-- Status -->
            <td><?php echo esc_html($status); ?></td>

            <!-- Actions -->
            <td>
                <button class="btn btn-sm btn-primary edit-member" 
                        data-id="<?php echo esc_attr($user->ID); ?>">
                    <?php esc_html_e('Edit', 'member-directory'); ?>
                </button>
                <button class="btn btn-sm btn-danger delete-member" 
                        data-id="<?php echo esc_attr($user->ID); ?>">
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

    /**
     * Add a new member as a WordPress user with all meta data (no custom table)
     */
    public function add_member() {

        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'] ) ) {
            wp_send_json_error([
                'data' => 'Invalid nonce',
            ]);
        }

        // Sanitize input
        $first_name     = sanitize_text_field( $_POST['first_name'] );
        $last_name      = sanitize_text_field( $_POST['last_name'] );
        $email          = sanitize_email( $_POST['email'] );
        $address        = sanitize_text_field( $_POST['address'] );
        $favorite_color = sanitize_text_field( $_POST['favorite_color'] );
        $status         = sanitize_text_field( $_POST['status'] );

        // Handle file uploads
        $profile_image = md_handle_image_upload( $_FILES['profile_image'], 'profile' );
        $cover_image   = md_handle_image_upload( $_FILES['cover_image'], 'cover' );

        // Check if email already exists
        if ( email_exists( $email ) ) {
            wp_send_json_error([
                'data' => 'A user with this email already exists.',
            ]);
        }

        // Generate username and password
        $user_login = sanitize_user( $first_name . '_' . wp_generate_password( 4, false ) );
        $password   = '12345';

        // Create WordPress user
        $user_id = wp_create_user( $user_login, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error([
                'data' => $user_id->get_error_message(),
            ]);
        }

        // Update user meta and basic info
        wp_update_user([
            'ID'         => $user_id,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'subscriber',
        ]);

        // Store all additional meta
        update_user_meta( $user_id, 'address', $address );
        update_user_meta( $user_id, 'favorite_color', $favorite_color );
        update_user_meta( $user_id, 'status', $status );
        update_user_meta( $user_id, 'md_meta', 'md' ); // for filtering
        if ( $profile_image ) {
            update_user_meta( $user_id, 'profile_image', esc_url( $profile_image ) );
        }
        if ( $cover_image ) {
            update_user_meta( $user_id, 'cover_image', esc_url( $cover_image ) );
        }

        // Prepare member data to match JS expectations
        $member_data = [
            'id'             => $user_id,
            'first_name'     => $first_name,
            'last_name'      => $last_name,
            'email'          => $email,
            'address'        => $address,
            'favorite_color' => $favorite_color,
            'status'         => $status,
            'profile_image'  => $profile_image ? esc_url( $profile_image ) : '',
            'cover_image'    => $cover_image ? esc_url( $cover_image ) : '',
        ];

        // Return success response compatible with your JS
        wp_send_json_success([
            'data'   => 'Member added successfully.',
            'member' => $member_data,
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

    public function update_member() {

        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], ) ) {
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
        if (!empty($_FILES['edit_profile_image']['name'])) {
            $upload = wp_handle_upload($_FILES['edit_profile_image'], ['test_form' => false]);
            if (!isset($upload['error'])) {
                $data['profile_image'] = esc_url($upload['url']);
            }
        }

        // ✅ Handle Cover Image
        if (!empty($_FILES['edit_cover_image']['name'])) {
            $upload = wp_handle_upload($_FILES['edit_cover_image'], ['test_form' => false]);
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
              <input type="file" name="edit_profile_image" class="form-control" accept="image/*">
            </div>

            <!-- Cover Photo -->
            <div class="col-md-6 text-center">
              <label class="form-label d-block">Cover Photo</label>
              <img id="md-edit-cover-preview" src="" class="img-thumbnail mb-2"
              
              style="width:100%;height:120px;object-fit:cover;">
              <input type="file" name="edit_cover_image" class="form-control" accept="image/*">
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

        <!-- Delete Member Modal -->
        <div class="modal fade" id="md-delete-member-modal" tabindex="-1" aria-labelledby="mdDeleteMemberModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="mdDeleteMemberModalLabel">Delete Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <p>Are you sure you want to delete this member?</p>
                <input type="hidden" id="md-delete-member-id">
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="md-confirm-delete-member" class="btn btn-danger">Yes, Delete</button>
              </div>

            </div>
          </div>
        </div>

        <?php  
    }

}