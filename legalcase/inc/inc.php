<?php

// Test if LCM is installed
if (!@file_exists('inc/config/inc_connect.php')) {
	header('Location: install.php');
	exit;
}

include ('inc/inc_version.php');

include_lcm('inc_auth');
include_lcm('inc_presentation');
include_lcm('inc_text');
include_lcm('inc_filters');
include_lcm('inc_calendar');

if (!@file_exists('data/inc_meta_cache.php'))
	write_metas();


//
// Preferences for presentation
// Can be done from any screen, but for now most is in config_author.php
//

// [ML] This is very important (but dirty hack) to change the language
// from config_author.php, without passing by lcm_cookie.php
if ($sel_language)
	$lang = $sel_language;
else
	$lang = $GLOBALS['HTTP_COOKIE_VARS']['lcm_lang'];

if ($lang AND $lang <> $author_session['lang'] AND lcm_set_language($lang)) {
	lcm_query("UPDATE lcm_author
				SET   lang = '".addslashes($lang)."'
				WHERE id_author  = " .$author_session['id_author']);
	$author_session['lang'] = $lang;
	lcm_add_session($author_session, $lcm_session);
}

if ($sel_theme) {
	// XSS risk: Theme names can only be alpha-numeric, "-" and "_"
	$sel_theme = preg_replace("/[^-_a-zA-Z0-9]/", '', $sel_theme);

	if (file_exists("styles/lcm_ui_" . $sel_theme . ".css")) {
		$prefs['theme'] = ($sel_theme);
		$prefs_mod = true;
	}
}

// Set rows per page preference
if ($page_rows) {
	$prefs['page_rows'] = $page_rows;
	$prefs_mod = true;
}

// Update user preferences if modified
if ($prefs_mod) {
	lcm_query("UPDATE lcm_author
				SET   prefs = '".addslashes(serialize($prefs))."'
				WHERE id_author = " . $author_session['id_author']);
}


//
// Database version management
//

write_metas();
$installed_db_version = read_meta('lcm_db_version');

if ($installed_db_version <> $lcm_db_version) {
	lcm_page_start("Database upgrade", "install");
	if (!$installed_version)
		$installed_version = "old version";

	echo "<div class='box_warning'>\n";
	echo "<p><b>" . _T('title_technical_message') . _T('typo_column') . "</b> The
		format of the database has changed. <a href='lcm_upgrade.php'>To
		proceed with the automatic upgrade, click here</a>. You are also
		encouraged to make a backup before proceeding.</p>\n";
	echo "</div>\n";

	lcm_page_end();
	exit;
}


//
// Fetch the administration cookie
$cookie_admin = $HTTP_COOKIE_VARS['lcm_admin'];


?>
