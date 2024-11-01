<?php
/*
Plugin Name: subversion-log
Plugin URI: http://www.internetcollaboratif.info/index.php/categories/projets/subversion-log/
Description: This plugin print out last log comment from an external subversion repository.
Version: 0.2
Author: Mathieu Lory
Author URI: http://www.internetcollaboratif.info/
*/
/*
subversion-log
Copyright (C) 2008-2009 Mathieu Lory <mathieu@internetcollaboratif.info>
This file is part of subversion-log.

subversion-log is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

subversion-log is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with subversion-log. If not, see <http://www.gnu.org/licenses/>.
*/

function getRepositoryLogs($repo=NULL, $login=NULL, $pass=NULL, $qty=5) {
	require(dirname(__FILE__)."/includes/phpsvnclient/phpsvnclient.php");
	$svn  = new phpsvnclient;
	$svn->setRepository($repo);
	
	if ( isset($login) && isset($pass) )
	{
		$svn->setAuth($login, $pass);
	}
	$v = $svn->getVersion();
	$r = $v-$qty-1;
	$logs = $svn->getRepositoryLogs($r);
	$out = '<div class="widget"><div id="subversion_logWrapper"><a href="' . $repo . '" target="_blank">';
	$out .= '<h2 class="widgettitle" style="font-size:18px;">Subversion Repository<br/>';
	$current_revision = $logs[$qty-1]['version'] + 1;
	$out .= '<span style="font-size:12px;">Currently at revision #' . $current_revision . '</span></h2></a>';
	$out .= '<ul>';
	//.__('', 'subversion-log').' ' 
	//.__('Commentaire :', 'subversion-log').' '
	//
	if ( is_array($logs) ) {
		foreach(array_reverse($logs) as $log) {
			$time = new DateTime($log['date']);
			$out .= '<li>';
			$out .= '<strong><span class="version">';
			$out .= '<a href="' . $repo . '!svn/bc/' . $log['version'] . '/" target="_blank">';
			$out .= 'Rev #' . $log['version'] . ', ' . $time->format(get_option('date_format')) . '</a></span></strong>';
			$out .= '<span class="comment">' . $log['comment'] . '</span>';
			$out .= '<span class="author">'.__('Author:', 'subversion-log').' ' . $log['author'] . '</span>';
			$out .= "</li>";
		}
	}
	
	$out .= '</ul></div></div><br />';
	return $out;
}

function subversion_log_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('options-general.php', __('subversion_log Configuration', 'subversion-log'), __('subversion_log Configuration', 'subversion-log'), 8, __FILE__, 'subversion_log_conf');
}

function subversion_log_conf() {
	$options = get_option('subversion_log');

	if (!isset($options['subversion_log_url'])) $options['subversion_log_url'] = null;
	if (!isset($options['subversion_log_login'])) $options['subversion_log_login'] = null;
	if (!isset($options['subversion_log_password'])) $options['subversion_log_password'] = null;
	if (!isset($options['subversion_log_qty'])) $options['subversion_log_qty'] = 5;
	
	$updated = false;
	if ( isset($_POST['submit']) ) {
		check_admin_referer();
		
		if (isset($_POST['subversion_log_url'])) {
			$subversion_log_url = $_POST['subversion_log_url'];
		} else {
			$subversion_log_url = null;
		}
		if (isset($_POST['subversion_log_login'])) {
			$subversion_log_login = $_POST['subversion_log_login'];
		} else {
			$subversion_log_login = null;
		}
		if (isset($_POST['subversion_log_password'])) {
			$subversion_log_password = $_POST['subversion_log_password'];
		} else {
			$subversion_log_password = null;
		}
		if (isset($_POST['subversion_log_qty'])) {
			$subversion_log_qty = $_POST['subversion_log_qty'];
		} else {
			$subversion_log_qty = null;
		}
		
		$options['subversion_log_url'] = $subversion_log_url;
		$options['subversion_log_login'] = $subversion_log_login;
		$options['subversion_log_password'] = $subversion_log_password;
		$options['subversion_log_qty'] = $subversion_log_qty;
		
		update_option('subversion_log', $options);
		
		$updated = true;
	}
?>



<div class="wrap">
<?php
if ($updated) {
	echo "<div id='message' class='updated fade'><p>";
	_e('Configuration updated.');
	echo "</p></div>";
}
?>
<h2><?php _e('subversion_log Configuration', 'subversion-log'); ?></h2>
<div style="float: right; width: 350px">
	<h3><?php _e('subversion-log Title', 'subversion-log'); ?></h3>
	<p><?php _e('This plugin displays last log comment from an external subversion repository.', 'subversion-log')?></p>
</div>

<form action="" method="post" id="subversion_log-conf">
<h3><label for="subversion_log_url"><?php _e('Repository Url', 'subversion-log'); ?></label></h3>
<p><input id="subversion_log_url" name="subversion_log_url" size="60" type="text" value="<?php echo $options['subversion_log_url']; ?>" /></p>

<h3><label for="subversion_log_login"><?php _e('login', 'subversion-log'); ?></label></h3>
<p><input id="subversion_log_login" name="subversion_log_login" size="60" type="text" value="<?php echo $options['subversion_log_login']; ?>" /></p>

<h3><label for="subversion_log_password"><?php _e('password', 'subversion-log'); ?></label></h3>
<p><input id="subversion_log_password" name="subversion_log_password" size="60" type="text" value="<?php echo $options['subversion_log_password']; ?>" /></p>

<h3><label for="subversion_log_qty"><?php _e('qty', 'subversion-log'); ?></label></h3>
<p><input id="subversion_log_qty" name="subversion_log_qty" size="3" type="text" value="<?php echo $options['subversion_log_qty']; ?>" /></p>

<p class="submit" style="text-align: left"><input type="submit" name="submit" value="<?php _e('Save &raquo;', 'subversion-log'); ?>" /></p>
</form>
</div>
<?php
}






function widget_wp_subversion_log() {
	$options = get_option('subversion_log');
	print getRepositoryLogs($options['subversion_log_url'], $options['subversion_log_login'], $options['subversion_log_password'], $options['subversion_log_qty']);
}




// Run code and init
wp_register_style('wp_subversion_log_css_styles', WP_PLUGIN_URL . '/subversion-log/style.css');
wp_enqueue_style('wp_subversion_log_css_styles');
add_action('admin_menu', 'subversion_log_config_page');
load_plugin_textdomain('subversion-log', WP_PLUGIN_URL . '/subversion-log/languages', 'subversion-log/languages');
register_sidebar_widget(array('subversion-log', 'widgets'), 'widget_wp_subversion_log');
?>
