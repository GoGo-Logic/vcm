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
include('inc/inc_acc.php');

$client = intval($_REQUEST['client']);

if ($client > 0) {
	$q="SELECT *
		FROM lcm_client
		WHERE lcm_client.id_client = $client";

	$result = lcm_query($q);

	if ($row = lcm_fetch_array($result)) {
		/* Saved for future use
			// Check for access rights
			if (!($row['public'] || allowed($client,'r'))) {
				die("You don't have permission to view this client details!");
			}
			$edit = allowed($client,'w');
		*/

		$edit = true;

		if ($row['gender'] == 'male' || $row['gender'] == 'female')
			$gender = _T('person_input_gender_' . $row['gender']);
		else
			$gender = _T('info_not_available');


		// [ML] TODO: Show as a list with UL + LI without bullets (accessibility)

		// Show client details
		lcm_page_start(_T('title_client_view') . ' ' . $row['name_first'] . ' ' .  $row['name_middle'] . ' ' . $row['name_last']);

		if (isset($_SESSION['client']['attach_case'])) {
			$q = "SELECT title
					FROM lcm_case
					WHERE id_case = " . intval($_SESSION['client']['attach_case']);
			$result = lcm_query($q);

			while ($row = lcm_fetch_array($result)) {
				echo '<p>' . 'The client was created and attached to the case: ' 
					. '<a href="case_det.php?case=' . $_SESSION['client']['attach_case'] . '">' 
					. $row['title'] 
					. "</a></p>\n";
			}
		}
		
		echo '<fieldset class="info_box">';
		echo '<div class="prefs_column_menu_head">' . _T('client_subtitle_view_general') . "</div>\n";

		echo '<p class="normal_text">';
		echo _T('client_input_id') . ' ' . $row['id_client'] . "<br/>\n";
		echo _T('person_input_gender') . ' ' . $gender . "<br/>\n";
		echo _T('person_input_citizen_number') . ' ' . $row['citizen_number'] . "<br/>\n";
		echo _T('person_input_address') . ' ' . $row['address'] . "<br/>\n";
		echo _T('person_input_address') . ' ' . $row['civil_status'] . "<br/>\n";
		echo _T('person_input_income') . ' ' . $row['income'] . "<br/>\n";
		echo 'Creation date: ' . format_date($row['date_creation']) . "<br/>\n";
		// [ML] echo 'Last update date: ' . $row['date_update'] . "<br/>\n";
		echo "</p>\n";

		if ($edit)
			echo '<a href="edit_client.php?client=' . $row['id_client'] .  '" class="edit_lnk">Edit client information</a><br />' . "\n";
		
		echo "<br /></fieldset>\n";
			
		echo '<fieldset class="info_box">';
		echo '<div class="prefs_column_menu_head">' . _T('client_subtitle_associated_org') . "</div>\n";

		echo '
		<br /><table border="0" class="tbl_usr_dtl">
		    <tr>
			<th class="heading">Organisation name</th>
			<th class="heading">&nbsp;</th>
		    </tr>';

		//
		// Show organisation(s)
		//
		$q = "SELECT lcm_org.id_org,name
				FROM lcm_client_org,lcm_org
				WHERE id_client=$client
					AND lcm_client_org.id_org=lcm_org.id_org";

		$result = lcm_query($q);

		while ($row = lcm_fetch_array($result)) {
			echo '<tr><td><a href="org_det.php?org=' . $row['id_org'] . '" class="content_link">' . $row['name'] . "</a></td>\n<td>";
			if ($edit)
				echo '<a href="edit_org.php?org=' . $row['id_org'] . '" class="content_link">Edit</a>';
			echo "</td></tr>\n";
		}
		
		echo "</table>";

		if ($edit)
			echo "<br /><a href=\"sel_org_cli.php?client=$client\" class=\"add_lnk\">Add organisation(s)</a><br />";

		echo "<br /></fieldset>";

		//
		// Show 5 recent cases - why 5? - [ML] because I'm lazy
		//
		$q = "SELECT clo.id_case, c.title, c.date_creation
				FROM lcm_case_client_org as clo, lcm_case as c
				WHERE id_client = " . $client . "
				AND clo.id_case = c.id_case
				LIMIT 5";

		$result = lcm_query($q);
		$html = "";
		$cpt = 0;

		while ($row = lcm_fetch_array($result)) {
			$html .= '<tr><td class="tbl_cont_' . ($i % 2 ? "dark" : "light") . '">' 
				. '<a href="case_det.php?case=' . $row['id_case'] . '" class="content_link">' 
				.  $row['title'] 
				. '</a></td></tr>' . "\n";
		}

		if ($html) {
			echo '<fieldset class="info_box">' . "\n";
			echo '<div class="prefs_column_menu_head">' . _T('client_subtitle_recent_cases') . "</div>\n";
			echo "<table>\n";
			echo $html;
			echo "</table>\n";
			echo "</fieldset>\n";
		}

	} else die("There's no such client!");
} else die("Which client?");

lcm_page_end();
?>
