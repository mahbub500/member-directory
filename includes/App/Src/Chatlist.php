<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Chatlist {

    use Hook;

    public function __construct() {
        $this->action( 'wp_head', [ $this, 'head' ] );        
    }

    public function chatlist(){
        echo "chatlist";
    }
}