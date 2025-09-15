<?php
namespace MemberDirectory\App\Controller;
use MemberDirectory\App\Src\Admin;
use MemberDirectory\App\Src\Front;
use MemberDirectory\App\Src\Member;
use MemberDirectory\App\Src\Team;
use MemberDirectory\App\Src\Assign;
use MemberDirectory\App\Src\Shortcode;
use MemberDirectory\App\Src\Common;

class Loader {

    public function run() {
        // Load Admin menus only in dashboard
        if ( is_admin() ) {
            new Admin();
            new Member();
            new Team();
            new Assign();
        }else{
            new Shortcode();
            new Front();
        }
            new Common();
    }
}
