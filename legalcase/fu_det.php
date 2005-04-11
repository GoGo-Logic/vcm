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
	$q="SELECT lcm_followup.*,lcm_author.name_first,lcm_author.name_middle,lcm_author.name_last
		FROM lcm_followup, lcm_author
		WHERE id_followup=$followup
			AND lcm_followup.id_author=lcm_author.id_author";

	$result = lcm_query($q);

	if ($row = lcm_fetch_array($result)) {
		foreach($row as $key=>$value) {
			$fu_data[$key] = $value;
		}
	} else die("There's no such follow-up!");
} else {
	die("Which follow-up?");
}

lcm_page_start("Follow-up details"); // TRAD

// Show a bit of background on the case
$case = $fu_data['id_case'];
show_context_start();
show_context_case_title($case);
show_context_case_involving($case);

// Show parent appointment, if any
// [ML] todo put in inc_presentation
$q = "SELECT lcm_app.* FROM lcm_app_fu,lcm_app WHERE lcm_app_fu.id_followup=$followup AND lcm_app_fu.id_app=lcm_app.id_app AND lcm_app_fu.relation='child'";
$res_app = lcm_query($q);
if ($app = lcm_fetch_array($res_app)) {
	echo '<li style="list-style-type: none;">' . _T('fu_input_parent_appointment') . ' ';
	echo '<a href="app_det.php?app=' . $app['id_app'] . '">' . _T(get_kw_title($app['type']))
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
	echo '<a href="app_det.php?app=' . $app['id_app'] . '">' . _T(get_kw_title($app['type']))
		. ' (' . $app['title'] . ') from ' . format_date($app['start_time']) . "</a></li>\n"; // TRAD
}

show_context_end();

?>

	<table class="tbl_usr_dtl" width="99%">
		<tr><td><?php echo _T('case_input_author'); ?></td>
			<td><?php echo get_person_name($fu_data); ?></td></tr>
		<tr><td><?php echo _T('fu_input_date_start'); ?></td>
			<td><?php echo format_date($fu_data['date_start']); ?></td></tr>
		<tr><td><?php echo _T('fu_input_date_end'); ?></td>
			<td><?php echo format_date($fu_data['date_end']); ?></td></tr>
		<tr><td><?php echo _T('fu_input_type'); ?></td>
			<td><?php echo _T('kw_followups_' . $fu_data['type'] . '_title'); ?></td></tr>
		<tr><td valign="top"><?php echo _T('fu_input_description'); ?></td>
			<td><?php
			
	echo nl2br(clean_output($fu_data['description']));
	echo "</td></tr>\n";

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

	// Show 'edit case' button if allowed
	$case_allow_modif = read_meta('case_allow_modif');
	$edit = ($GLOBALS['author_session']['status'] == 'admin') || allowed($case,'e');

	if ($case_allow_modif == 'yes' && $edit) {
		echo '<p><a href="edit_fu.php?followup=' . $fu_data['id_followup'] . '" class="edit_lnk">'
				. "Edit follow-up" // TRAD
				. '</a></p>';
	}

	if (! $app) {
		// Show create appointment from followup
		echo '<p><a href="edit_app.php?case=' . $case . '&amp;followup=' . $followup . '" class="create_new_lnk">Create new appointment related to this followup' . "</a></p>\n";  // TRAD
	}
	
	lcm_page_end();
?>
