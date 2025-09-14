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

        // $this->register_ajax( 'md_delete_member', [ $this, 'delete_member' ] );

        // $this->register_ajax( 'md_update_member', [ $this, 'update_member' ] );

        // $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );

        // $this->action( 'admin_footer', [$this, 'footer'] );
    }

    public function assign_page(){
        echo "Assign";
    }
}