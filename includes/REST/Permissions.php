<?php

namespace CopywriteCat\REST;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Permissions {
	public static function must_be_logged_in() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'cwc_forbidden', 'You must be logged in.', [ 'status' => 401 ] );
		}
		return true;
	}
}
