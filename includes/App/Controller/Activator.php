<?php
namespace MemberDirectory\App\Controller;

use wpdb;

class Activator {

    public static function activate() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $table_members = $wpdb->prefix . 'md_members';
        $table_teams   = $wpdb->prefix . 'md_teams';
        $table_rel     = $wpdb->prefix . 'md_member_team_relations';

        // Members Table
        $sql1 = "CREATE TABLE $table_members (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            profile_image VARCHAR(255) DEFAULT NULL,
            cover_image VARCHAR(255) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            favorite_color VARCHAR(20) DEFAULT NULL,
            status ENUM('active','draft') DEFAULT 'draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Teams Table
        $sql2 = "CREATE TABLE $table_teams (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(150) NOT NULL,
            short_description TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Relation Table
        $sql3 = "CREATE TABLE $table_rel (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            member_ids TEXT NOT NULL, 
            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (team_id) REFERENCES $table_teams(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta($sql1);
        dbDelta($sql2);
        dbDelta($sql3);
    }
}
