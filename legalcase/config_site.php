<?php

/*
	This file is part of the Legal Case Management System (LCM).
	(C) 2004-2005 Free Software Foundation, Inc.

	This program is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by the 
	Free Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	This program is distributed in the hope that it will be useful, but 
	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
	or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License along 
	with this program; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA  02111-1307, USA

	$Id$
*/

include ("inc/inc.php");

// TODO: We should keep a log of modifications
// For example, if currency changed many times, it will allow
// to track when a currency had which value (silly, but can avoid crisis)

function show_config_form($panel) {
	echo "<p class='normal_text'><img src='images/jimmac/icon_warning.gif' alt='' align='right'
		height='48' width='48' />" . _T('siteconf_warning') . "</p>\n";

/*
	if ($panel == 'collab')
		$html_collab = " &lt;--";
	else if ($panel == 'policy')
		$html_policy = " &lt;--";
	else if ($panel == 'regional')
		$html_regional = " &lt;--";
	else 
		$html_general = " &lt;--";

	echo '<ul class="simple_list">' . "\n";
	echo "<li><a href='config_site.php?panel=general' class='content_link'>" . _T('siteconf_subtitle_general_info') . "</a>" . $html_general . "</li>\n";
	echo "<li><a href='config_site.php?panel=collab' class='content_link'>" . _T('siteconf_subtitle_collab_work') . "</a>" . $html_collab . "</li>\n";
	echo "<li><a href='config_site.php?panel=policy' class='content_link'>" .  _T('siteconf_subtitle_policy') . "</a>" . $html_policy . "</li>\n";
	echo "<li><a href='config_site.php?panel=regional' class='content_link'>" . _T('siteconf_subtitle_regional') . "</a>" . $html_regional . "</li>\n";
	echo "</ul>\n";
*/
	// Show tabs
	$groups = array('general' => _T('siteconf_subtitle_general_info'),
			'collab' => _T('siteconf_subtitle_collab_work'),
			'policy' => _T('siteconf_subtitle_policy'),
			'regional' => _T('siteconf_subtitle_regional'));
	$tab = ( isset($_GET['tab']) ? $_GET['tab'] : 'general' );
	//show_tabs($groups,$tab,$_SERVER['REQUEST_URI']);
	show_tabs($groups,$tab,$_SERVER['SCRIPT_NAME']);

	echo "<form name='upd_site_profile' method='post' action='config_site.php'>\n";
/*
	if ($panel == 'collab')
		show_config_form_collab();
	else if ($panel == 'policy')
		show_config_form_policy();
	else if ($panel == 'regional')
		show_config_form_regional();
	else
		show_config_form_general();
*/
	switch ($tab) {
		case 'general' :
			show_config_form_general();
			break;
		case 'collab' :
			show_config_form_collab();
			break;
		case 'policy' :
			show_config_form_policy();
			break;
		case 'regional' :
			show_config_form_regional();
			break;
	}
	
	echo "</form>\n";
}

function show_config_form_general() {
	global $lcm_lang_right;

	$site_name = read_meta('site_name');
	$site_desc = read_meta('site_description');
	$site_address = read_meta('site_address');
	$email_sysadmin = read_meta('email_sysadmin');

	if (empty($site_name))
		$site_name = _T('title_software');

	echo "\t<input type='hidden' name='conf_modified_general' value='yes'/>\n";
	echo "\t<input type='hidden' name='panel' value='general'/>\n";

	echo "<fieldset class='conf_info_box'>\n";
	echo "<div class='prefs_column_menu_head'><label for='site_name'>" . _T('siteconf_input_site_name') . "</label></div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_site_name') . "</small></p>\n";
	echo "<p><input type='text' id='site_name' name='site_name' value='$site_name' size='40' class='search_form_txt' /></p>\n";

	echo "<div class='prefs_column_menu_head'><label for='site_desc'>" . _T('siteconf_input_site_desc') . "</label></div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_site_desc') . "</small></p>\n";
	echo "<p><input type='text' id='site_desc' name='site_desc' value='$site_desc' size='40' class='search_form_txt' /></p>\n";
	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate1' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";

	echo "<fieldset class='conf_info_box'>\n";
	echo "<div class='prefs_column_menu_head'><label for='site_address'>" . _T('siteconf_input_site_address') . "</label></div>\n";
	echo "<p><input type='text' id='site_address' name='site_address' value='$site_address' size='40' class='search_form_txt' /></p>\n";

	echo "<div class='prefs_column_menu_head'><label for='email_sysadmin'>" . _T('siteconf_input_admin_email') . "</label></div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_admin_email') . "</small></p>\n";
	echo "<p><input type='text' id='email_sysadmin' name='email_sysadmin' value='$email_sysadmin' size='40' class='search_form_txt' /></p>\n";
	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate2' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";
}

function show_config_form_collab() {
	global $lcm_lang_right;

	$case_default_read = read_meta('case_default_read');
	$case_default_write = read_meta('case_default_write');
	$case_read_always = read_meta('case_read_always');
	$case_write_always = read_meta('case_write_always');

	echo "\t<input type='hidden' name='conf_modified_collab' value='yes'/>\n";
	echo "\t<input type='hidden' name='panel' value='collab'/>\n";
	
	echo '<fieldset class="conf_info_box">' . "\n";
	echo '<div class="prefs_column_menu_head">' . _T('siteconf_subtitle_collab_work') . "</div>\n";

	// READ ACCESS
	echo "<p><b>" . _T('siteconf_input_access_read_choice') . "</b></p>\n";

	echo "<ul>";
	// If case_default_read == 'yes' (public)
	echo "<li style='list-style-type: none;'><input type='radio' name='case_default_read' id='case_default_read_1' value='yes'";
	if ($case_default_read == 'yes') echo ' checked="checked"';
	echo "><label for='case_default_read_1'>" .  _T('siteconf_input_access_read_choice_public') . "</label></input></li>\n";

	// If case_default_read != 'yes' (private)
	echo "<li style='list-style-type: none;'><input type='radio' name='case_default_read' id='case_default_read_2' value=''";
	if ($case_default_read == 'no') echo ' checked="checked"';
	echo "><label for='case_default_read_2'>" . _T('siteconf_input_access_read_choice_private') . "</label></input></li>\n";
	echo "</ul>\n";

	// READ ACCESS POLICY
	echo "<p><b>" . _T('siteconf_input_access_read_global') . "</b></p>\n";

	echo "<ul>";
	// Anyone can change the setting (case_read_always != yes)
	echo '<li style="list-style-type: none;"><input type="radio" name="case_read_always" id="case_read_always_2" value=""';
	if ($case_read_always == 'no') echo ' checked="checked"';
	echo '><label for="case_read_always_2">' . _T('siteconf_input_access_read_global_no') . "</label></input></li>\n";

	// Only the admin can change the setting (case_read_always == yes)
	echo '<li style="list-style-type: none;"><input type="radio" name="case_read_always" id="case_read_always_1" value="yes"';
	if ($case_read_always == 'yes') echo ' checked="checked"';
	echo '><label for="case_read_always_1">' . _T('siteconf_input_access_read_global_yes') . "</label></input></li>\n";
	echo "</ul>\n";

	echo "<hr>\n";

	// WRITE ACCESS
	echo "<p><b>" . _T('siteconf_input_access_write_choice') . "</b></p>\n";

	echo "<ul>";
	// If by default write set to public (case_default_write == 'yes')
	echo "<li style='list-style-type: none;'><input type='radio' name='case_default_write' id='case_default_write_1' value='yes'";
	if ($case_default_write == 'yes') echo ' checked="checked"';
	echo '><label for="case_default_write_1">' . _T('siteconf_input_access_write_choice_public') . "</label></input></li>\n";

	// If by default write not set to public (case_default_write != 'yes')
	echo "<li style='list-style-type: none;'><input type='radio' name='case_default_write' id='case_default_write_2' value=''";
	if ($case_default_write == 'no') echo ' checked="checked"';
	echo '><label for="case_default_write_2">' . _T('siteconf_input_access_write_choice_private') . "</label></input></li>\n";
	echo "</ul>\n";

	// WRITE ACCESS POLICY
	echo "<p><b>" . _T('siteconf_input_access_write_global') . "</b></p>\n";

	echo "<ul>";
	// Anyone can change the setting (case_write_always != yes)
	echo "<li style='list-style-type: none;'><input type='radio' name='case_write_always' id='case_write_always_2' value=''";
	if ($case_write_always == 'no') echo ' checked="checked"';
	echo '><label for="case_write_always_2">' . _T('siteconf_input_access_write_global_no') . "</label></input></li>\n";

	// Only the admin can change the setting (case_write_always == yes)
	echo "<li style='list-style-type: none;'><input type='radio' name='case_write_always' id='case_write_always_1' value='yes'";
	if ($case_write_always == 'yes') echo ' checked="checked"';
	echo '><label for="case_write_always_1">' . _T('siteconf_input_access_write_global_yes') . "</label></input></li>\n";
	echo "</ul>\n";
	echo "</fieldset>";

	//
	// *** SELF-REGISTRATION
	//
	$site_open_subscription = read_meta('site_open_subscription');
	
	echo "<fieldset class='conf_info_box'>\n";
	echo "<div class=\"prefs_column_menu_head\">". _T('siteconf_subtitle_self_registration') ."</div>";

	echo "<p>" . _T('siteconf_info_self_registration') . "</p>\n";
	echo "<ul>";

	// moderated
	echo "<li style='list-style-type: none;'><input type='radio' name='site_open_subscription' id='site_open_subscription_2' value='moderated'";
	if ($site_open_subscription == 'moderated') echo ' checked="checked"';
	echo " /><label for='site_open_subscription_2'>" . _T('siteconf_input_selfreg_moderated') . "</label></input></li>\n";

	// un-moderated (yes)
	echo "<li style='list-style-type: none;'>";
	echo "<input type='radio' name='site_open_subscription' id='site_open_subscription_1' value='yes'";
	if ($site_open_subscription == 'yes') echo ' checked="checked"';
	echo " /><label for='site_open_subscription_1'>" . _T('siteconf_input_selfreg_yes') . "</label></input></li>\n";

	// no
	echo "<li style='list-style-type: none;'><input type='radio' name='site_open_subscription' id='site_open_subscription_3' value='no'";
	if ($site_open_subscription == 'no') echo ' checked="checked"';
	echo "><label for='site_open_subscription_3'>" . _T('siteconf_input_selfreg_no') . "</label></input></li>\n";

	echo "</ul>\n";
	echo "</fieldset>";
	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate3' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";

}

function show_config_form_regional() {
	global $lcm_lang_right;

	$default_language = read_meta('default_language');
	$currency = read_meta('currency');

	// If no currency format set, get default format from the language translation
	if (empty($currency)) {
		$current_lang = $GLOBALS['lcm_lang'];
		$GLOBALS['lcm_lang'] = $default_language;
		$currency = _T('currency_default_format');
		$GLOBALS['lcm_lang'] = $current_lang;
	}

	echo "\t<input type='hidden' name='conf_modified_regional' value='yes'/>\n";
	echo "\t<input type='hidden' name='panel' value='regional'/>\n";

	echo "<fieldset class='conf_info_box'>\n";
	echo "<div class='prefs_column_menu_head'>" . _T('siteconf_input_default_lang') . "</div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_default_lang') . "</small></p>\n";
	echo "<p align='center'>" . menu_languages('default_language', $default_language) . "</p>\n";

	echo "<div class='prefs_column_menu_head'><label for='currency'>" . _T('siteconf_input_currency') . "</label></div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_currency') . "</small></p>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_warning_currency') . "</small></p>\n";
	echo "<p align='center'><input type='text' id='currency' name='currency' value='$currency' size='5' class='search_form_txt' /></p>\n";
	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate4' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";

	echo "<fieldset class='conf_info_box'>\n";
	echo "<div class='prefs_column_menu_head'><label for='available_regional'>" . _T('siteconf_input_available_languages') . "</label></div>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_available_languages') . "</small></p>\n";
	// echo "<p><input type='text' id='site_name' name='site_name' value='$site_name' size='40' class='search_form_txt' /></p>\n";

	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate5' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";
}

function get_yes_no($name, $value = '') {
	$ret = '';

	$yes = ($value == 'yes' ? ' selected="selected"' : '');
	$no = ($value == 'no' ? ' selected="selected"' : '');
	$other = ($yes || $no ? '' : ' selected="selected"');

	// until we format with tables, better to keep the starting space
	$ret .= ' <select name="' . $name . '" class="sel_frm">' . "\n";
	$ret .= '<option value="yes"' . $yes . '>' . _T('info_yes') . '</option>';
	$ret .= '<option value="no"' . $no . '>' . _T('info_no') . '</option>';

	if ($other)
		$ret .= '<option value=""' . $other . '> </option>';

	$ret .= '</select>' . "\n";

	return $ret;
}

function show_config_form_policy() {
	global $lcm_lang_right;

	$client_name_middle = read_meta('client_name_middle');
	$client_citizen_number = read_meta('client_citizen_number');
	$case_court_archive = read_meta('case_court_archive');
	$case_assignment_date = read_meta('case_assignment_date');
	$case_alledged_crime = read_meta('case_alledged_crime');
	$case_allow_modif = read_meta('case_allow_modif');
	$fu_sum_billed = read_meta('fu_sum_billed');
	$fu_allow_modif = read_meta('fu_allow_modif');
	$hide_emails = read_meta('hide_emails');

	echo "\t<input type='hidden' name='conf_modified_policy' value='yes'/>\n";
	echo "\t<input type='hidden' name='panel' value='policy'/>\n";

	// ** CLIENTS
	echo "<fieldset class='conf_info_box'>\n";
	echo '<p class="prefs_column_menu_head"><b>' . _T('siteconf_subtitle_client_fields') . "</b></p>\n";
	echo '<p><small class="sm_11">' . _T('siteconf_info_client_fields') . "</small></p>\n";

	echo '<table width="99%" class="tbl_usr_dtl">' . "\n";
	echo '<tr><td width="300">' . _T('siteconf_input_name_middle') ."</td>\n"
		. "<td>" . get_yes_no('client_name_middle', $client_name_middle) .  "</td>\n"
		. "</tr>\n";

	echo "<tr><td>" . _T('siteconf_input_citizen_number') ."</td>"
		. "<td>" . get_yes_no('client_citizen_number', $client_citizen_number) . "</td>"
		. "</tr>\n";

	echo "<tr><td>" . _T('siteconf_info_hide_emails') . "</td>\n"
		. "<td>" . get_yes_no('hide_emails', $hide_emails) . "</td>"
		. "</tr>\n";
	echo "</table>\n";

	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate6' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";

	// ** CASES
	echo "<fieldset class='conf_info_box'>\n";
	echo "<p class='prefs_column_menu_head'><b>" . _T('siteconf_subtitle_case_fields') . "</b></p>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_case_fields') . "</small></p>\n";

	echo "<table width=\"99%\" class=\"tbl_usr_dtl\">";
	echo "<tr><td width=\"300\">" . _T('siteconf_input_court_archive') ."</td><td>"
		. get_yes_no('case_court_archive', $case_court_archive)
		. "</td></tr>\n";
	echo "<tr><td>" . _T('siteconf_input_assignment_date') ."</td><td>"
		. get_yes_no('case_assignment_date', $case_assignment_date)
		. "</td></tr>\n";
	echo "<tr><td> " . _T('siteconf_input_alledged_crime') ."</td><td>"
		. get_yes_no('case_alledged_crime', $case_alledged_crime)
		. "</td></tr>\n";
	echo "<tr><td>" . _T('siteconf_input_case_allow_modif') ."</td><td>"
		. get_yes_no('case_allow_modif', $case_allow_modif)
		. "</td></tr>\n";
	echo "</table>\n";

	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate6' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";

	// ** FOLLOW-UPS
	echo "<fieldset class='conf_info_box'>\n";
	echo "<p class='prefs_column_menu_head'><b>" . _T('siteconf_subtitle_followup_fields') . "</b></p>\n";
	echo "<p><small class='sm_11'>" . _T('siteconf_info_followups_fields') . "</small></p>\n";

	echo "<table width=\"99%\" class=\"tbl_usr_dtl\">";
	echo "<tr><td width=\"300\">" . _T('siteconf_input_sum_billed') ."</td><td>"
		. get_yes_no('fu_sum_billed', $fu_sum_billed)
		. "</td></tr>\n";
	echo "<tr><td>" . _T('siteconf_input_fu_allow_modif') ."</td><td>"
		. get_yes_no('fu_allow_modif', $fu_allow_modif)
		. "</td></tr>\n";
	echo "</table>\n";

	echo "<p align='$lcm_lang_right'><button type='submit' name='Validate' id='Validate7' class='simple_form_btn'>" .  _T('button_validate') . "</button></p>\n";
	echo "</fieldset>\n";

}

function apply_conf_changes_general() {
	$log = array();

	$site_name = $_REQUEST['site_name'];
	$site_desc = $_REQUEST['site_desc'];
	$site_address = $_REQUEST['site_address'];
	$email_sysadmin = $_REQUEST['email_sysadmin'];

	// Site name
	if (! empty($site_name)) {
		$old_name = read_meta('site_name');
		if (! $old_name) $old_name = _T('title_software');

		if ($old_name != $site_name) {
			write_meta('site_name', $site_name);
			array_push($log, "Name of site set to '<tt>$site_name</tt>', was '<tt>$old_name</tt>'.");
		}
	}

	// Site description (may be empty)
	$old_desc = read_meta('site_description');

	if ($old_desc != $site_desc) {
		write_meta('site_description', $site_desc);
		array_push($log, "Description of site set to '<tt>$site_desc</tt>', was '<tt>$old_desc</tt>'.");
	}

	// Site address (Internet or LAN)
	$old_address = read_meta('site_address');

	if ($old_address != $site_address) {
		write_meta('site_address', $site_address);
		array_push($log, "Site Internet or network address set to '<tt>$site_address</tt>', was '<tt>$old_address</tt>'.");
	}

	// Administrator e-mail
	if (! empty($email_sysadmin)) {
		if ($email_sysadmin != read_meta('email_sysadmin')) {
			if (is_valid_email($email_sysadmin)) {
				write_meta('email_sysadmin', $email_sysadmin);
				array_push($log, "Sysadmin e-mail address et to <tt>"
					. addslashes($email_sysadmin) . "</tt>.");
			} else {
				// FIXME not the best way of showing errors... 
				array_push($log, "Sysadmin e-mail address <tt>"
					. addslashes($email_sysadmin) . "</tt> is <b>not</b> a "
					. "valid address. Modification not applied.");
			}
		}
	}

	if (! empty($log))
		write_metas();
	
	return $log;
}

function apply_conf_changes_collab() {
	$log = array();

	$case_default_read  = ($_REQUEST['case_default_read'] == 'yes' ? 'yes' : 'no');
	$case_default_write = ($_REQUEST['case_default_write'] == 'yes' ? 'yes' : 'no');
	$case_read_always   = ($_REQUEST['case_read_always'] == 'yes' ? 'yes' : 'no');
	$case_write_always  = ($_REQUEST['case_write_always'] == 'yes' ? 'yes' : 'no');
	$site_open_subscription = $_REQUEST['site_open_subscription']; // validate later

	// Default read policy
	if ($case_default_read != read_meta('case_default_read')) {
		write_meta('case_default_read', $case_default_read);

		$entry = "Read access to cases set to '<tt>";
		if ($case_default_read == 'yes') $entry .= "public";
		else $entry .= "restricted";
		$entry .= "</tt>'";
		array_push($log, $entry);
	}

	// Default write policy
	if ($case_default_write != read_meta('case_default_write')) {
		write_meta('case_default_write', $case_default_write);

		$entry = "Write access to cases set to '<tt>";
		if ($case_default_write == 'yes') $entry .= "public";
		else $entry .= "restricted";
		$entry .= "</tt>'";
		array_push($log, $entry);
	}

	// Read policy access
	if ($case_read_always != read_meta('case_read_always')) {
		write_meta('case_read_always', $case_read_always);

		$entry = "Read access policy can by changed by <tt>";
		if ($case_read_always == 'yes') $entry .= "admin only";
		else $entry .= "everybody";
		$entry .= "</tt>";
		array_push($log, $entry);
	}

	// Write policy access
	if ($case_write_always != read_meta('case_write_always')) {
		write_meta('case_write_always', $case_write_always);

		$entry = "Write access policy can be changed by <tt>";
		if ($case_write_always == 'yes') $entry .= "admin only";
		else $entry .= "everybody";
		$entry .= "</tt>";
		array_push($log, $entry);
	}

	// Self-registration
	$old_site_open_subscription = read_meta('site_open_subscription');
	if ($site_open_subscription != $old_site_open_subscription) {
		if ($site_open_subscription == 'yes' || 
			$site_open_subscription == 'moderated' || 
			$site_open_subscription == 'no') 
		{
			write_meta('site_open_subscription', $site_open_subscription);
			array_push($log, "New author self-registration changed to "
				. "'$site_open_subscription', was '$old_site_open_subscription'.");
		}
	}

	if (! empty($log))
		write_metas();

	return $log;
}

function apply_conf_changes_policy() {
	$log = array();

	$items = array('client_name_middle', 'client_citizen_number', 'hide_emails',
				'case_court_archive', 'case_assignment_date', 'case_alledged_crime',
				'case_allow_modif', 'fu_sum_billed', 'fu_allow_modif');

	foreach ($items as $it) {
		if (isset($_REQUEST[$it])
			AND ($_REQUEST[$it] == 'yes' OR $_REQUEST[$it] == 'no'))
		{
			$old_value = read_meta($it);
			if ($_REQUEST[$it] != $old_value) {
				write_meta($it, $_REQUEST[$it]);
				array_push($log, $it . " set to " . $_REQUEST[$it] . ", was " . $old_value . ".");
			}
		}
	}

	if (! empty($log))
		write_metas();

	return $log;
}

function apply_conf_changes_regional() {
	$log = array();

	$default_language = $_REQUEST['default_language'];
	$currency = $_REQUEST['currency'];

	// Default language
	if (! empty($default_language)) {
		$old_lang = read_meta('default_language');

		if ($old_lang != $default_language) {
			write_meta('default_language', $default_language);
			array_push($log, "Default language set to <tt>"
				. translate_language_name($default_language)
				. "</tt>, previously was <tt>"
				. translate_language_name($old_lang) ."</tt>.");
		}
	}

	// Currency
	if (! empty($currency)) {
		$old_currency = read_meta('currency');

		if ($currency != $old_currency) {
			write_meta('currency', $currency);
			array_push($log, "Currency changed to <tt>$currency</tt>, "
				. "was <tt>$old_currency</tt>.");
		}
	}

	// Force refresh of lcm_meta->available_languaes
	init_languages(true);
	array_push($log, "Language list refreshed.");

	if (! empty($log))
		write_metas();

	return $log;
}

global $author_session;

// Restrict page to administrators
if ($author_session['status'] != 'admin') {
	lcm_page_start("Site configuration");
	echo "<p>Warning: Access denied, not admin.\n";
	lcm_page_end();
	exit;
}

if ($conf_modified_general)
	$log = apply_conf_changes_general();
else if ($conf_modified_collab)
	$log = apply_conf_changes_collab();
else if ($conf_modified_policy)
	$log = apply_conf_changes_policy();
else if ($conf_modified_regional)
	$log = apply_conf_changes_regional();

// Once ready, show the form (must be done after changes are
// applied so that they can be used in the header).
lcm_page_start(_T('title_site_configuration'));

// Show changes on screen
if (! empty($log)) {
	echo "<div align='left' style='border: 1px solid #00ff00; padding: 5px;'>\n";
	echo "<div>Changes made:</div>\n";
	echo "<ul>";

	foreach ($log as $line) {
		echo "<li>" . $line . "</li>\n";
		lcm_log('Author ' . $author_session['id_author'] . ': ' . $line,'config');
	}

	echo "</ul>\n";
	echo "</div>\n";
}

show_config_form($panel);
lcm_page_end();


?>
