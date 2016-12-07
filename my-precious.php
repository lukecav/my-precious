<?php
/*
Plugin Name: My Precious
Description: Quit leaking sensitive information to WordPress.org.
Version: 1.0
Author: ibericode
Author URI: https://ibericode.com/
License: GPL v3

My Precious - quit leaking sensitive information to WordPress.org
Copyright (C) 2016, Danny van Kooten, danny@ibericode.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace my_precious;

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

/**
 * @param false|array|\WP_Error $preempt
 * @param array $r
 * @param string $url
 * @return array|\WP_Error
 */
function clean_request( $preempt, $r, $url ) {

    if( strpos( $url, '://api.wordpress.org/core/version-check/' ) !== 5 ) {
        return $preempt;
    }

    // stop sending # of users to WordPress.org
    $url = remove_query_arg( 'users', $url );

    // remove filter temporarily
    filter_off();

    // make request
    $result = wp_remote_post( $url, $r );

    // re-add filter
    filter_on();

    return $result;
}

function filter_on() {
    add_filter( 'pre_http_request', 'my_precious\\clean_request', 10, 3 );
}

function filter_off() {
    remove_filter( 'pre_http_request', 'my_precious\\clean_request' );
}

filter_on();