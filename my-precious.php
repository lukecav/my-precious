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
 * @param array $args
 * @param string $url
 * @return array
 */
function clean_http_request_args( $args, $url ) {
    // only act on requests to api.wordpress.org
    if( strpos( $url, '://api.wordpress.org/' ) !== 5 ) {
        return $args;
    }

    // strip site URL from headers & user-agent
    unset( $args['headers']['wp_install'] );
    unset( $args['headers']['wp_blog'] );

    if( ! empty( $args['user-agent'] ) ) {
        $args['user-agent'] = sprintf( 'WordPress/%s', $GLOBALS['wp_version'] );
    }

    if( ! empty( $args['headers']['User-Agent'] ) ) {
        $args['user-agent'] = sprintf( 'WordPress/%s', $GLOBALS['wp_version'] );
    }

    return $args;
}

/**
 * @param false|array|\WP_Error $preempt
 * @param array $args
 * @param string $url
 * @return array|\WP_Error
 */
function pre_version_check_http_request( $preempt, $args, $url ) {

    // TODO: If something else pre-fired this request than re-running this request makes no sense. The "damage" has already been done at this point.
    if( $preempt !== false ) {
        return $preempt;
    }

    // only act on requests to api.wordpress.org
    if( strpos( $url, '://api.wordpress.org/core/version-check' ) !== 5 ) {
        return $preempt;
    }

    // did we clean this request already?
    if( ! empty( $args['_my_precious'] ) ) {
        return $preempt;
    }

    // stop sending # of users to WordPress.org
    $url = remove_query_arg( 'users', $url );

    // make request
    $args['_my_precious'] = true;
    $result = wp_remote_request( $url, $args );

    return $result;
}

add_filter( 'http_request_args', 'my_precious\\clean_http_request_args', 10, 2 );
add_filter( 'pre_http_request', 'my_precious\\pre_version_check_http_request', 10, 3 );