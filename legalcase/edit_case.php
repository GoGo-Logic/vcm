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

include('inc/inc.php');
include_lcm('inc_acc');
include_lcm('inc_filters');

// Read site configuration preferences
$case_court_archive = read_meta('case_court_archive');
$case_assignment_date = read_meta('case_assignment_date');
$case_alledged_crime = read_meta('case_alledged_crime');
$case_allow_modif = read_meta('case_allow_modif');

if (empty($_SESSION['errors'])) {

	// Clear form data
	$_SESSION['case_data'] = array();

	// Set the returning page
	if (isset($ref)) $_SESSION['case_data']['ref_edit_case'] = $ref;
	else $_SESSION['case_data']['ref_edit_case'] = $GLOBALS['HTTP_REFERER'];

	// Register case ID as session variable
	if (!session_is_registered("case"))
		session_register("case");

	$case = intval($_GET['case']);

	// Register case type variable for the session
	if (!session_is_registered("existing"))
		session_register("existing");

	// Find out if this is existing or new case
	$existing = ($case > 0);

	// Set author ID by session data
	// [ML] XXX FIXME: absurd! this is written in the database, there may be many authors
	$_SESSION['case_data']['id_author'] = $GLOBALS['author_session']['id_author'];

	if ($existing) {
		// Check access rights
		if (!allowed($case,'e')) die(_T('error_no_edit_permission'));

		$q = "SELECT *
			FROM lcm_case
			WHERE id_case = $case";

		$result = lcm_query($q);

		if ($row = lcm_fetch_array($result)) {
			foreach ($row as $key => $value) {
				$_SESSION['case_data'][$key] = $value;
			}
		}

		$_SESSION['case_data']['admin'] = allowed($case,'a');

	} else {
		// Set default values for the new case
		$_SESSION['case_data']['public'] = read_meta('case_default_read');
		$_SESSION['case_data']['pub_write'] = read_meta('case_default_write');
		$_SESSION['case_data']['status'] = 'draft';

		$_SESSION['case_data']['admin'] = true;

	}
}

$attach_client = 0;

if (!$existing && isset($_REQUEST['attach_client'])) {
	$attach_client = intval($_REQUEST['attach_client']);

	if ($attach_client) {
		// Fetch name of the client
		$query = "SELECT name_first, name_middle, name_last
					FROM lcm_client
					WHERE id_client = " . $attach_client;

		$result = lcm_query($query);
		if ($info = lcm_fetch_array($result)) {
			$_SESSION['case_data']['title'] = get_person_name($info);
		} else {
			die("No such client #" . $attach_client);
		}
	}
}

$attach_org = 0;

if (!$existing && isset($_REQUEST['attach_org'])) {
	$attach_org = intval($_REQUEST['attach_org']);

	if ($attach_org) {
		// Fetch name of the organisation
		$query = "SELECT name
					FROM lcm_org
					WHERE id_org = " . $attach_org;

		$result = lcm_query($query);
		if ($info = lcm_fetch_array($result)) {
			$_SESSION['case_data']['title'] = $info['name'];
		} else {
			die("No such organisation #" . $attach_org);
		}
	}
}


// Start page and title
if ($existing) lcm_page_start(_T('title_case_edit'));
else lcm_page_start(_T('title_case_new'));

echo "<div style='float: right'>" . lcm_help("case_edit") . "</div>\n";

// Show the errors (if any)
echo show_all_errors($_SESSION['errors']);

if ($attach_client || $attach_org)
	show_context_start();

if ($attach_client) {
	$query = "SELECT id_client, name_first, name_middle, name_last
				FROM lcm_client
				WHERE id_client = " . $attach_client;
	$result = lcm_query($query);
	while ($row = lcm_fetch_array($result))  // should be only once
		echo '<li style="list-style-type: none;">' . _Ti('fu_input_involving_clients') . get_person_name($row) . "</li>\n";
	
}

if ($attach_org) {
	$query = "SELECT id_org, name
				FROM lcm_org
				WHERE id_org = " . $attach_org;
	$result = lcm_query($query);
	while ($row = lcm_fetch_array($result))  // should be only once
		echo '<li style="list-style-type: none;">' . _Ti('fu_input_involving_clients') . $row['name'] . "</li>\n";
}

if ($attach_client || $attach_org)
	show_context_end();

// Start edit case form
echo "<form action=\"upd_case.php\" method=\"post\">
<input type=\"hidden\" name=\"id_author\" value=\"" . $_SESSION['case_data']['id_author'] . "\" />
<table class=\"tbl_usr_dtl\">\n";

if ($attach_client)
	echo '<input type="hidden" name="attach_client" value="' . $attach_client . '" />' . "\n";

if ($attach_org)
	echo '<input type="hidden" name="attach_org" value="' . $attach_org . '" />' . "\n";

if ($_SESSION['case_data']['id_case']) {
	echo "\t<tr><td>" . _T('case_input_id') . "</td><td>" . $_SESSION['case_data']['id_case']
		. "<input type=\"hidden\" name=\"id_case\" value=\"" . $_SESSION['case_data']['id_case'] . "\" /></td></tr>\n";
}

	echo '<tr><td><label for="input_title">'
		. f_err_star('title', $_SESSION['errors']) . _T('case_input_title')
		. "</label></td>\n";
	echo '<td><input size="35" name="title" id="input_title" value="'
		. clean_output($_SESSION['case_data']['title'])
		. '" class="search_form_txt" />';
	echo "</td></tr>\n";
	
	// Court archive ID
	if ($case_court_archive == 'yes') {
		echo '<tr><td><label for="input_id_court_archive">' . _T('case_input_court_archive') . "</label></td>\n";
		echo '<td><input size="35" name="id_court_archive" id="input_id_court_archive" value="'
			. clean_output($_SESSION['case_data']['id_court_archive']) 
			. '" class="search_form_txt" /></td></tr>' . "\n";
	}

	// Legal reason
	echo '<tr><td><label for="input_legal_reason">' . _T('case_input_legal_reason') . "</label></td>\n";
	echo '<td>';
	echo '<textarea name="legal_reason" id="input_legal_reason" class="frm_tarea" rows="2" cols="60">';
	echo clean_output($_SESSION['case_data']['legal_reason']);
	echo "</textarea>";
	echo "</td>\n";
	echo "</tr>\n";

	// Alledged crime
	if ($case_alledged_crime == 'yes') {
		echo '<tr><td><label for="input_alledged_crime">' . _T('case_input_alledged_crime') . "</label></td>\n";
		echo '<td>';
		echo '<textarea name="alledged_crime" id="input_alledged_crime" class="frm_tarea" rows="2" cols="60">';
		echo clean_output($_SESSION['case_data']['alledged_crime']);
		echo '</textarea>';
		echo "</td>\n";
		echo "</tr>\n";
	}

	// Keywords
	include_lcm('inc_keywords');
	$kwg_for_case = get_kwg_all('case', true);
	$cpt_kw = 0;

	foreach ($kwg_for_case as $kwg) {
		echo "<tr>\n";
		echo '<td>' . f_err_star('keyword_' . $cpt_kw) . _Ti($kwg['title']) 
			. "<br />(" . _T('keywords_input_policy_' . $kwg['policy']) . ")</td>\n";

		$kw_for_kwg = get_keywords_in_group_id($kwg['id_group']);
		if (count($kw_for_kwg)) {
			echo "<td>";
			echo '<input type="hidden" name="kwg_name[]" value="' . $kwg['name'] . '" />' . "\n";
			echo '<select name="kwg_value[]">';

			foreach ($kw_for_kwg as $kw)
				echo '<option value="' . $kw['name'] . '">' . _T($kw['title']) . "</option>\n";

			echo "</select>\n";
			echo "</td>\n";
		} else {
			// This should not happen, we should get only non-empty groups
		}
		
		echo "</tr>\n";
		$cpt_kw++;
	}

	// Case status
	echo '<tr><td><label for="input_status">' . _T('case_input_status') . "</label></td>\n";
	echo '<td>';
	echo '<select name="status" id="input_status" class="sel_frm">' . "\n";
	$statuses = ($existing ? array('draft','open','suspended','closed','merged') : array('draft','open') );

	foreach ($statuses as $s) {
		$sel = ($s == $_SESSION['case_data']['status'] ? ' selected="selected"' : '');
		echo '<option value="' . $s . '"' . $sel . ">" 
			. _T('case_status_option_' . $s)
			. "</option>\n";
	}

	echo "</select></td>\n";
	echo "</tr>\n";

	// Case stage
	global $system_kwg;
	if (! $_SESSION['case_data']['stage'])
		$_SESSION['case_data']['stage'] = $system_kwg['stage']['suggest'];

	echo '<tr><td><label for="input_stage">' . _T('case_input_stage') . "</label></td>\n";
	echo '<td><select name="stage" id="input_stage" class="sel_frm">' . "\n";
	foreach($system_kwg['stage']['keywords'] as $kw) {
		$sel = ($kw['name'] == $_SESSION['case_data']['stage'] ? ' selected="selected"' : '');
		echo "\t\t\t\t<option value='" . $kw['name'] . "'" . "$sel>" . _T($kw['title']) . "</option>\n";
	}
	echo "</select></td>\n";
	echo "</tr>\n";

	// Public access rights
	if ($_SESSION['case_data']['admin'] || !read_meta('case_read_always') || !read_meta('case_write_always')) {
		echo '<tr><td colspan="2">' . _T('case_input_collaboration') .  ' <br />
				<ul>';

		if (read_meta('case_read_always') == 'no' || $author_session['status'] == 'admin') {
			echo '<li style="list-style-type: none;">';
			echo '<input type="checkbox" name="public" id="case_public_read" value="yes"';

			if ($_SESSION['case_data']['public'])
				echo ' checked="checked"';

			echo " />";
			echo '<label for="case_public_read">' . _T('case_input_collaboration_read') . "</label></li>\n";
		}

		if (!read_meta('case_write_always') || $_SESSION['case_data']['admin']) {
			echo '<li style="list-style-type: none;">';
			echo '<input type="checkbox" name="pub_write" id="case_public_write" value="yes"';

			if ($_SESSION['case_data']['pub_write'])
				echo ' checked="checked"';

			echo " />";
			echo '<label for="case_public_write">' . _T('case_input_collaboration_write') . "</label></li>\n";
		}

		echo "</ul>\n";
?>

			</td>
		</tr>

<?php
	}

	echo "</table>\n";

	// Different buttons for edit existing and for new case
	if ($existing) {
		echo '<p><button name="submit" type="submit" value="submit" class="simple_form_btn">' . _T('button_validate') . "</button></p>\n";
	} else {
		// More buttons for 'extended' mode
		if ($prefs['mode'] == 'extended') {
			echo '<p><button name="submit" type="submit" value="add" class="simple_form_btn">' . _T('button_validate') . "</button>\n";
			echo '<button name="submit" type="submit" value="addnew" class="simple_form_btn">' . _T('add_and_open_new') . "</button>\n";
			echo '<button name="submit" type="submit" value="adddet" class="simple_form_btn">' . _T('add_and_go_to_details') .  "</button></p>\n";
		} else {
			// Less buttons in simple mode
			echo '<p><button name="submit" type="submit" value="adddet" class="simple_form_btn">' . _T('button_validate') . "</button></p>\n";
		}
	}

	echo '<input type="hidden" name="admin" value="' . $_SESSION['case_data']['admin'] . "\" />\n";
	echo '<input type="hidden" name="ref_edit_case" value="' . $_SESSION['case_data']['ref_edit_case'] . "\" />\n";
	echo "</form>\n\n";

	// Reset error messages and form data
	$_SESSION['errors'] = array();
	$_SESSION['case_data'] = array();

	lcm_page_end();
?>
