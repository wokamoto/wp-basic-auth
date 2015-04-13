<?php
/*
Plugin Name: WP BASIC Auth
Plugin URI: https://github.com/wokamoto/wp-basic-auth
Description: Enabling this plugin allows you to set up Basic authentication on your site using your WordPress's user name and password. 
Author: wokamoto
Version: 1.1.3
Author URI: http://dogmap.jp/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html
  Copyright 2013-2015 wokamoto (email : wokamoto1973@gmail.com)

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

class wp_basic_auth {
	const HTACCES_REWRITE_RULE = '
# BEGIN WP BASIC Auth
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
</IfModule>
# END WP BASIC Auth
';

	static $instance;

	function __construct(){
		self::$instance = $this;

		add_action('template_redirect', array($this, 'basic_auth'), 1);

		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
	}

	public function activate(){
		if (!file_exists(ABSPATH.'.htaccess'))
			return;
		$htaccess = file_get_contents(ABSPATH.'.htaccess');
		if (strpos($htaccess, self::HTACCES_REWRITE_RULE) !== false)
			return;
		file_put_contents(ABSPATH.'.htaccess', self::HTACCES_REWRITE_RULE . $htaccess);
	}

	public function deactivate(){
		if (!file_exists(ABSPATH.'.htaccess'))
			return;
		$htaccess = file_get_contents(ABSPATH.'.htaccess');
		if (strpos($htaccess, self::HTACCES_REWRITE_RULE) === false)
			return;
		file_put_contents(ABSPATH.'.htaccess', str_replace(self::HTACCES_REWRITE_RULE, '', $htaccess));
	}

	public function basic_auth(){
		nocache_headers();
		if ( is_user_logged_in() )
			return;

		$usr = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$pwd = isset($_SERVER['PHP_AUTH_PW'])   ? $_SERVER['PHP_AUTH_PW']   : '';
		if (empty($usr) && empty($pwd) && isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION']) {
			list($type, $auth) = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
			if (strtolower($type) === 'basic') {
				list($usr, $pwd) = explode(':', base64_decode($auth));
			}
		}

		$is_authenticated = wp_authenticate($usr, $pwd);
		if ( !is_wp_error( $is_authenticated ) )
			return;

		header('WWW-Authenticate: Basic realm="Please Enter Your Password"');
		wp_die(
			'You need to enter a Username and a Password if you want to see this website.',
			'Authorization Required',
			array( 'response' => 401 )
			);
	}
}
new wp_basic_auth();
