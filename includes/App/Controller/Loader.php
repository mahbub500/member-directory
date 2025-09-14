<?php
namespace MemberDirectory\App\Controller;
use MemberDirectory\App\Src\Admin;
use MemberDirectory\App\Src\Front;
use MemberDirectory\App\Src\Member;

class Loader {

    public function run() {
        // Load Admin menus only in dashboard
        if ( is_admin() ) {
            new Admin();
        }else{
            new Front();
        }
    }
}
