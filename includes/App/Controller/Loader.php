<?php
namespace MemberDirectory\App\Controller;
use MemberDirectory\App\Src\Admin;
use MemberDirectory\App\Src\Front;
use MemberDirectory\App\Src\Member;
use MemberDirectory\App\Src\Team;

class Loader {

    public function run() {
        // Load Admin menus only in dashboard
        if ( is_admin() ) {
            new Admin();
            new Member();
            new Team();
        }else{
            new Front();
        }
    }
}
