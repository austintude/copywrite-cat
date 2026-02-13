<?php

namespace CopywriteCat\DB;

use CopywriteCat\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Migrations {
	public function migrate(): void {
		$installed = (int) get_option( Plugin::OPTION_DB_VERSION, 0 );
		if ( $installed >= Plugin::DB_VERSION ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( Schema::tables() as $sql ) {
			dbDelta( $sql );
		}

		update_option( Plugin::OPTION_DB_VERSION, Plugin::DB_VERSION );
	}
}
