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

session_start();

include('inc/inc.php');
include_lcm('inc_acc');
include_lcm('inc_filters');

if (empty($errors)) {

    // Clear form data
    $case_data = array();

	// Set the returning page
	if (isset($ref)) $case_data['ref_edit_case'] = $ref;
	else $case_data['ref_edit_case'] = $GLOBALS['HTTP_REFERER'];

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
	$case_data['id_author'] = $GLOBALS['author_session']['id_author'];

	if ($existing) {
		// Check access rights
		if (!allowed($case,'e')) die(_T('error_no_edit_permission'));

		$q = "SELECT *
			FROM lcm_case
			WHERE id_case = $case";

		$result = lcm_query($q);

		if ($row = lcm_fetch_array($result)) {
			foreach ($row as $key => $value) {
				$case_data[$key] = $value;
			}
		}

		$admin = allowed($case,'a');

	} else {
		// Set default values for the new case
		$case_data['date_creation'] = date(_T('date_format')); // was: date('Y-m-d H:i:s');
		$case_data['public'] = read_meta('case_default_read');
		$case_data['pub_write'] = read_meta('case_default_write');
		$case_data['status'] = 'open';

		$admin = true;

	}
}

// Start page and title
if ($existing) lcm_page_start(_T('title_case_edit'));
else lcm_page_start(_T('title_case_new'));

// Start edit case form
echo "\n<form action=\"upd_case.php\" method=\"post\">
<table class=\"tbl_usr_dtl\">
<input type=\"hidden\" name=\"id_author\" value=\"" . $case_data['id_author'] . "\">\n";

if ($case_data['id_case']) {
	echo "\t<tr><td>" . _T('case_input_id') . "</td><td>" . $case_data['id_case']
		. "<input type=\"hidden\" name=\"id_case\" value=\"" .  $case_data['id_case'] . "\"></td></tr>\n";
}

//	echo "
//		<tr><td>" . _T('author_id') . "</td><td>" . $case_data['id_author'] . "
//			<input type=\"hidden\" name=\"id_author\" value=\"" . $case_data['id_author'] . "\"></td></tr>";
	echo "
		<tr><td>" . _T('case_input_title') . "</td>
			<td><input name=\"title\" value=\"" . clean_output($case_data['title']) . "\" class=\"search_form_txt\">";
	echo f_err('title',$errors) . "</td></tr>
		<tr><td>" . _T('case_input_court_archive') . "</td>
			<td><input name=\"id_court_archive\" value=\"" . clean_output($case_data['id_court_archive']) . "\" class=\"search_form_txt\"></td></tr>";
// [AG] Creation date not shown upon ML request
//		<tr><td>" . _T('case_input_date_creation') . "</td>
//			<td>" . $case_data['date_creation'] . "</td></tr>
// [AG] Assignment date is set only when adding user to the case
//		<tr><td>" . _T('case_input_date_assignment') . "</td>
//			<td><input name=\"date_assignment\" value=\"" . clean_output($case_data['date_assignment']) . "\" class=\"search_form_txt\"></td></tr>
	echo "
		<tr><td>" . _T('case_input_legal_reason') . "</td>
			<td><input name=\"legal_reason\" value=\"" . clean_output($case_data['legal_reason']) . "\" class=\"search_form_txt\"></td></tr>
		<tr><td>" . _T('case_input_alledged_crime') . "</td>
			<td><input name=\"alledged_crime\" value=\"" .  clean_output($case_data['alledged_crime']) . "\" class=\"search_form_txt\"></td></tr>
		<tr><td>" . _T('case_input_status') . "</td>
			<td><input name=\"status\" value=\"" . clean_output($case_data['status']) . "\" class=\"search_form_txt\"></td></tr>
	";

	if ($admin || !read_meta('case_read_always') || !read_meta('case_write_always')) {
		echo "\t<tr><td>" . _T('public') . "</td>
			<td>
				<table>
				<tr>\n";

		if (!read_meta('case_read_always') || $admin) echo "			<td>" . _T('read') . "</td>\n";
		if (!read_meta('case_write_always') || $admin) echo "			<td>" . _T('write') . "</td>\n";

		echo "</tr><tr>\n";

		if (!read_meta('case_read_always') || $admin) {
			echo '			<td><input type="checkbox" name="public" value="yes"';
			if ($case_data['public']) echo ' checked';
			echo "></td>\n";
		}

		if (!read_meta('case_write_always') || $admin) {
			echo '			<td><input type="checkbox" name="pub_write" value="yes"';
			if ($case_data['pub_write']) echo ' checked';
			echo "></td>\n";
		}
?>				</tr>
				</table>
			</td>
		</tr>

<?php
	}

	echo "</table>\n";

	// Different buttons for edit existing and for new case
	// TODO: add expert mode
	if ($existing) {
		echo '<button name="submit" type="submit" value="submit" class="simple_form_btn">' . _T('button_validate') . "</button>\n";
	} else {
		echo '<button name="submit" type="submit" value="add" class="simple_form_btn">' . _T('button_validate') . '</button>
	<button name="submit" type="submit" value="addnew" class="simple_form_btn">' . _T('add_and_open_new') . '</button>
	<button name="submit" type="submit" value="adddet" class="simple_form_btn">' . _T('add_and_go_to_details') . "</button>\n";
	}

	// [ML] if ($existing)
	//	echo '<button name="reset" type="reset" class="simple_form_btn">' . _T('button_reset') . "</button>\n";

	echo '<input type="hidden" name="ref_edit_case" value="' . $case_data['ref_edit_case'];
	echo '">
</form>

';

	lcm_page_end();
	session_destroy();
?>
