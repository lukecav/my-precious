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
 * @param array $args
 * @param string $url
 * @return array|\WP_Error
 */
function clean_request( $preempt, $args, $url ) {
    // only act on requests to api.wordpress.org
    if( strpos( $url, '://api.wordpress.org/' ) !== 5 ) {
        return $preempt;
    }

    // did we clean this request already?
    if( ! empty( $args['_my_precious'] ) ) {
        return $preempt;
    }

    // stop sending # of users to WordPress.org
    $url = remove_query_arg( 'users', $url );

    // strip site URL from headers & user-agent
    unset( $args['headers']['wp_install'] );
    unset( $args['headers']['wp_blog'] );
    $args['user-agent'] = sprintf( 'WordPress/%s', $GLOBALS['wp_version'] );

    // make request
    $args['_my_precious'] = true;
    $result = wp_remote_request( $url, $args );

    return $result;
}

add_filter( 'pre_http_request', 'my_precious\\clean_request', 10, 3 );