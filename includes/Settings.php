<?php

namespace CopywriteCat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	public const OPTION_PORTAL_PAGE_ID = 'cwc_portal_page_id';

	public static function get_portal_url(): string {
		$page_id = (int) get_option( self::OPTION_PORTAL_PAGE_ID, 0 );
		if ( $page_id ) {
			$url = get_permalink( $page_id );
			if ( $url ) {
				return $url;
			}
		}
		return home_url( '/' );
	}
}
