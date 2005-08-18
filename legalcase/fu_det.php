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
include_lcm('inc_keywords');

if (isset($_GET['followup'])) {
	$followup=intval($_GET['followup']);

	// Fetch the details on the specified follow-up
	$q="SELECT fu.*, a.name_first, a.name_middle, a.name_last,
			IF(UNIX_TIMESTAMP(fu.date_end) > 0, UNIX_TIMESTAMP(fu.date_end) - UNIX_TIMESTAMP(fu.date_start), 0) as length
		FROM lcm_followup as fu, lcm_author as a
		WHERE id_followup = $followup
			AND fu.id_author = a.id_author";

	$result = lcm_query($q);

	if ($row = lcm_fetch_array($result)) {
		foreach($row as $key=>$value) {
			$fu_data[$key] = $value;
		}
	} else die("There's no such follow-up!");
} else {
	die("Which follow-up?");
}

// For 'edit case' button + 'undelete' message
$case_allow_modif = read_meta('case_allow_modif');
$edit = allowed($case,'e');
$admin = allowed($case, 'a');

lcm_page_start(_T('title_fu_view'), '', '', 'cases_followups');

echo '<fieldset class="info_box">';

// Show a bit of background on the case
$case = $fu_data['id_case'];
show_context_start();
show_context_case_title($case, 'followups');
show_context_case_stage($case, $fu_data['id_followup']);
show_context_case_involving($case);

// Show parent appointment, if any
// [ML] todo put in inc_presentation
$q = "SELECT lcm_app.* FROM lcm_app_fu,lcm_app
		WHERE lcm_app_fu.id_followup=$followup 
		  AND lcm_app_fu.id_app=lcm_app.id_app 
		  AND lcm_app_fu.relation='child'";
$res_app = lcm_query($q);

if ($app = lcm_fetch_array($res_app)) {
	echo '<li style="list-style-type: none;">' . _T('fu_input_parent_appointment') . ' ';
	echo '<a class="content_link" href="app_det.php?app=' . $app['id_app'] . '">' . _Tkw('appointments', $app['type'])
		. ' (' . $app['title'] . ') from ' . format_date($app['start_time']) . "</a></li>\n"; // TRAD
}

// Show child appointment, if any
$q = "SELECT lcm_app.* 
		FROM lcm_app_fu,lcm_app 
		WHERE lcm_app_fu.id_followup = $followup 
		  AND lcm_app_fu.id_app = lcm_app.id_app 
		  AND lcm_app_fu.relation = 'parent'";

$res_app = lcm_query($q);

if ($app = lcm_fetch_array($res_app)) {
	echo '<li style="list-style-type: none;">' . _T('fu_input_child_appointment') . ' ';
	echo '<a class="content_link" href="app_det.php?app=' . $app['id_app'] . '">' . _Tkw('appointments', $app['type'])
		. ' (' . $app['title'] . ') from ' . format_date($app['start_time']) . "</a></li>\n"; // TRAD
}

// Show stage information
if ($fu_data['case_stage']) {
	// if editing an existing followup..
	if ($_SESSION['fu_data']['case_stage'])
		$stage_info = get_kw_from_name('stage', $_SESSION['fu_data']['case_stage']);
	$id_stage = $stage_info['id_keyword'];
	show_context_stage($case, $id_stage);
}

show_context_end();

if ($fu_data['hidden'] == 'Y') {
	echo '<p class="normal_text"><strong>' . _T('fu_info_is_deleted') . "</strong>";

	if ($admin)
		echo " " . _T('fu_info_is_deleted2');
	
	echo "</p>\n";
}

echo '<table class="tbl_usr_dtl" width="99%">' . "\n";

// Author
echo "<tr>\n";
echo '<td>' . _Ti('case_input_author') . "</td>\n";
echo '<td>' . get_author_link($fu_data) . "</td>\n";
echo "</tr>\n";

// Date start
echo "<tr>\n";
echo '<td>' . _Ti('time_input_date_start') . "</td>\n";
echo '<td>' . format_date($fu_data['date_start']) . "</td>\n";
echo "</tr>\n";

// Date end
echo "<tr>\n";
echo '<td>' . _Ti('time_input_date_end') . "</td>\n";
echo '<td>' . format_date($fu_data['date_end']) . "</td>\n";
echo "</tr>\n";

// Date length
echo "<tr>\n";
echo '<td>' . _Ti('time_input_length') . "</td>\n";
echo '<td>' . format_time_interval_prefs($fu_data['length']) . "</td>\n";
echo "</tr>\n";

// FU type
echo "<tr>\n";
echo '<td>' . _Ti('fu_input_type') . "</td>\n";
echo '<td>' . _Tkw('followups', $fu_data['type']) . "</td>\n";
echo "</tr>\n";

// Conclusion for case/status change
if ($fu_data['type'] == 'status_change' || $fu_data['type'] == 'stage_change') {
	$tmp = lcm_unserialize($fu_data['description']);

	echo "<tr>\n";
	echo '<td>' . _Ti('fu_input_conclusion') . "</td>\n";

	echo '<td>';

	if (read_meta('case_result') == 'yes' && $tmp['result'])
		echo _Tkw('_crimresults', $tmp['result']) . "<br />\n";
	
	echo _Tkw('conclusion', $tmp['conclusion']) . "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo '<td>' . _Ti('fu_input_sentence') . "</td>\n";
	echo '<td>' . _Tkw('sentence', $tmp['sentence']) . "</td>\n";
	echo "</tr>\n";
}

// Description
$desc = get_fu_description($fu_data, false);

echo "<tr>\n";
echo '<td valign="top">' . _T('fu_input_description') . "</td>\n";
echo '<td>' . $desc . "</td>\n";
echo "</tr>\n";

// Sum billed (if activated from policy)
$fu_sum_billed = read_meta('fu_sum_billed');

if ($fu_sum_billed == 'yes') {
	echo "<tr><td>" . _T('fu_input_sum_billed') . "</td>\n";
	echo "<td>";
	echo format_money(clean_output($fu_data['sumbilled']));
	$currency = read_meta('currency');
	echo htmlspecialchars($currency);
	echo "</td></tr>\n";
}
				
echo "</table>\n";
echo "<br />";

// Edit button
if ($case_allow_modif == 'yes' && $edit) {
	echo '<a href="edit_fu.php?followup=' . $fu_data['id_followup'] . '" class="edit_lnk">'
		. _T('fu_button_edit')
		. '</a>';
}

if ($GLOBALS['author_session']['status'] == 'admin')
	echo '<a href="export.php?item=followup&amp;id=' . $fu_data['id_followup'] . '" class="exp_lnk">' . _T('export_button_followup') . "</a>\n";

echo "<br /><br /></fieldset>";

if (! $app) {
	// Show create appointment from followup
	echo '<p><a href="edit_app.php?case=' . $case . '&amp;followup=' . $followup . '" class="create_new_lnk">Create new appointment related to this followup' . "</a></p>\n";  // TRAD
}

lcm_page_end();

?>
