<?php

namespace CopywriteCat\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Schema {
	public static function tables(): array {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$versions = $wpdb->prefix . 'cwc_slot_versions';
		$comments = $wpdb->prefix . 'cwc_slot_comments';
		$activity = $wpdb->prefix . 'cwc_activity_log';

		return [
			"CREATE TABLE {$versions} (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				slot_id BIGINT(20) UNSIGNED NOT NULL,
				created_at DATETIME NOT NULL,
				created_by BIGINT(20) UNSIGNED NOT NULL,
				source VARCHAR(20) NOT NULL DEFAULT 'manual',
				insights_json LONGTEXT NULL,
				draft_text LONGTEXT NULL,
				rulecheck_json LONGTEXT NULL,
				notes TEXT NULL,
				PRIMARY KEY  (id),
				KEY slot_id (slot_id),
				KEY created_at (created_at)
			) {$charset_collate};",
			"CREATE TABLE {$comments} (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				slot_id BIGINT(20) UNSIGNED NOT NULL,
				created_at DATETIME NOT NULL,
				created_by BIGINT(20) UNSIGNED NOT NULL,
				comment_text TEXT NOT NULL,
				parent_id BIGINT(20) UNSIGNED NULL,
				PRIMARY KEY  (id),
				KEY slot_id (slot_id),
				KEY created_at (created_at)
			) {$charset_collate};",
			"CREATE TABLE {$activity} (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				project_id BIGINT(20) UNSIGNED NULL,
				slot_id BIGINT(20) UNSIGNED NULL,
				actor_user_id BIGINT(20) UNSIGNED NOT NULL,
				action VARCHAR(50) NOT NULL,
				metadata_json LONGTEXT NULL,
				created_at DATETIME NOT NULL,
				PRIMARY KEY  (id),
				KEY project_id (project_id),
				KEY slot_id (slot_id),
				KEY created_at (created_at)
			) {$charset_collate};",
		];
	}
}
