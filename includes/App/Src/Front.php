<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front {

    use Hook;

    public function __construct() {
        $this->action( 'wp_head', [ $this, 'head' ] );
    }

    public function head(){
        // $members = get_members_by_team(4);

        // Utility::pri( $members );
        // get_team_members( 4 );
    }
}