<?php
namespace MemberDirectory\App\Controller;
use MemberDirectory\App\Src\Admin;

class Loader {

    public function run() {
        // Load Admin menus only in dashboard
        if ( is_admin() ) {
            new Admin();
        }
    }
}
