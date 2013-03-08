<?php
/*
Plugin Name: WP Basic Auth
Plugin URI: https://github.com/wokamoto/wp-basic-auth
Description: Enabling this plugin allows you to set up Basic authentication on your site using your WordPress's user name and password. 
Author: wokamoto
Version: 1.0.0
Author URI: http://dogmap.jp/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html
  Copyright 2013 wokamoto (email : wokamoto1973@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function basic_auth(){
    nocache_headers();
    if ( is_user_logged_in() )
        return;

    $user = isset($_SERVER["PHP_AUTH_USER"]) ? $_SERVER["PHP_AUTH_USER"] : '';
    $pwd  = isset($_SERVER["PHP_AUTH_PW"])   ? $_SERVER["PHP_AUTH_PW"]   : '';
    if ( !is_wp_error(wp_authenticate($user, $pwd)) ) {
        return;
    }

    header('WWW-Authenticate: Basic realm="Please Enter Your Password"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authorization Required';
    die();
}
add_action('template_redirect','basic_auth');
