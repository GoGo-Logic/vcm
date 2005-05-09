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

// Execute this file only once
if (defined('_INC_CONDITIONS')) return;
define('_INC_CONDITIONS', '1');

//global $condition_types;
$GLOBALS['condition_types'] = array(1 => 'IS EQUAL TO',
				2 => 'IS LESS THAN',
				3 => 'IS GREATER THAN',
				4 => 'CONTAINS',
				5 => 'STARTS WITH',
				6 => 'ENDS WITH');

// Displays select condition form field
// $name - field name, $sel - selected option
function select_condition($name,$sel=0) {
	global $condition_types;

	$html = "<select name='$name' class='sel_frm'>\n";

	foreach($condition_types as $key => $val) {
		$html .= "<option " . (($key == $sel) ? 'selected ' : '') . "value=$key>$val</option>\n";
	}
	$html .= "</select>\n";

	return $html;
}

// Used by rep_det.php/run_rep.php to print filters for report
// * is_runtime determines whether it's time to enter the values for run_rep.php
function show_report_filters($id_report, $is_runtime = false) {
	// Get general report info
	$q = "SELECT * FROM lcm_report WHERE id_report = " . intval($id_report);
	$res = lcm_query($q);
	$rep_info = lcm_fetch_array($res);

	if (! $rep_info)
		lcm_panic("Report does not exist: $id_report");

	// List filters attached to this report
	$query = "SELECT *
		FROM lcm_rep_filter as v, lcm_fields as f
		WHERE id_report = " . $id_report . "
		AND f.id_field = v.id_field";

	$result = lcm_query($query);

	if (lcm_num_rows($result)) {
		if ($is_runtime) {
			// submit all at once (else submit on a per-filter basis)
			echo '<form action="run_rep.php" name="frm_filters" method="get">' . "\n";
			echo '<input name="rep" value="' . $id_report . '" type="hidden" />' . "\n";

			if (isset($_REQUEST['export']))
				echo '<input name="export" value="' . $_REQUEST['export'] . '" type="hidden" />' . "\n";
		}
	
		echo "<table border='0' class='tbl_usr_dtl' width='99%'>\n";

		while ($filter = lcm_fetch_array($result)) {
			if (! $is_runtime) {
				echo "<form action='upd_rep_field.php' name='frm_line_additem' method='get'>\n";
				echo "<input name='update' value='filter' type='hidden' />\n";
				echo "<input name='rep' value='$id_report' type='hidden' />\n";
				echo "<input name='id_filter' value='" . $filter['id_filter'] . "' type='hidden' />\n";
			}

			echo "<tr>\n";
			echo "<td>" . $filter['field_name'] . "</td>\n";

			// Type of filter
			echo "<td>";

			$all_filters = array(
					'number' => array('none', 'num_eq', 'num_lt', 'num_le', 'num_gt', 'num_ge'),
					'date' => array('none', 'date_eq', 'date_in', 'date_lt', 'date_le', 'date_gt', 'date_ge'),
					'text' => array('none', 'text_eq')
					);

			if ($all_filters[$filter['filter']]) {
				echo "<select name='filter_type'>\n";

				foreach ($all_filters[$filter['filter']] as $f) {
					$sel = ($filter['type'] == $f ? ' selected="selected"' : '');
					echo "<option value='" . $f . "'" . $sel . ">" . _T('filter_' . $f) . "</option>\n";
				}

				echo "</select>\n";
			} else {
				// XXX Should happen only if a filter was removed in a future version, e.g. rarely
				// or between development releases.
				echo "Unknown filter";
			}
			echo "</td>\n";

			// Value for filter
			echo "<td>";

			switch ($filter['type']) {
				case 'num_eq':
					if ($filter['field_name'] == 'id_author') {
						$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'filter_value');
						
						// XXX make this a function
						$q = "SELECT * FROM lcm_author WHERE status IN ('admin', 'normal', 'external')";
						$result_author = lcm_query($q);

						echo "<select name='$name'>\n";
						echo "<option value=''>-- select from list--</option>\n"; // TRAD

						while ($author = lcm_fetch_array($result_author)) {
							$sel = ($filter['value'] == $author['id_author'] ? ' selected="selected"' : '');
							echo "<option value='" . $author['id_author'] . "'" . $sel . ">" . $author['id_author'] . " : " . get_person_name($author) . "</option>\n";
						}

						echo "</select>\n";
						break;
					}
				case 'num_lt':
				case 'num_gt':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'filter_value');
					echo '<input style="width: 99%;" type="text" name="' . $name . '" value="' . $filter['value'] . '" />';
					break;

				case 'date_eq':
				case 'date_lt':
				case 'date_lt':
				case 'date_gt':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'date');
					echo get_date_inputs($name, $filter['value']); // FIXME
					break;
				case 'date_in':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'date');
					echo get_date_inputs($name . '_start', $filter['value']); // FIXME
					echo "<br />\n";
					echo get_date_inputs($name . '_end', $filter['value']); // FIXME
					break;
				case 'text_eq':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'date');
					echo '<input style="width: 99%;" type="text" name="' . $name . '" value="' . $filter['value'] . '" />';
					break;
				default:
					echo "<!-- no type -->\n";
			}

			echo "</td>\n";

			if (! $is_runtime) {
				// Button to validate
				echo "<td>";
				echo "<button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button>\n";
				echo "</td>\n";

				// Link for "Remove"
				echo "<td><a class='content_link' href='upd_rep_field.php?rep=" . $id_report . "&amp;"
					. "remove=filter" . "&amp;" . "id_filter=" . $filter['id_filter'] . "'>" . "X" . "</a></td>\n";
			}

			echo "</tr>\n";

			if (! $is_runtime)
				echo "</form>\n";
		}

		echo "</table>\n";
	}

	if ($is_runtime) {
		echo "<p><button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button></p>\n";
		echo "</form>\n";
		return;
	}

	// List all available fields in selected tables for report
	$query = "SELECT *
		FROM lcm_fields
		WHERE ";

	$sources = array();

	if ($rep_info['line_src_name'])
		array_push($sources, "'lcm_" . $rep_info['line_src_name'] .  "'");

	if ($rep_info['col_src_name'])
		array_push($sources, "'" /* lcm_" . */ . $rep_info['col_src_name'] . "'");

	// List only filters if table were selected as sources (line/col)
	if (count($sources)) {
		$query .= " table_name IN ( " . implode(" , ", $sources) . " ) AND ";
		$query .= " filter != 'none'";

		echo "<!-- QUERY: $query -->\n";

		$result = lcm_query($query);

		if (lcm_num_rows($result)) {
			echo "<form action='upd_rep_field.php' name='frm_line_additem' method='get'>\n";
			echo "<input name='rep' value='" . $rep_info['id_report'] . "' type='hidden' />\n";
			echo "<input name='add' value='filter' type='hidden' />\n";

			echo "<p class='normal_text'>" . "Add a filter based on this field:" . " "; // TRAD
			echo "<select name='id_field'>\n";

			while ($row = lcm_fetch_array($result)) {
				echo "<option value='" . $row['id_field'] . "'>" . $row['description'] . "</option>\n";
			}

			echo "</select>\n";
			echo "<button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button>\n";
			echo "</p>\n";
			echo "</form>\n";
		}
	} else {
		echo "<p>To apply filters, first select the source tables for report line and columns.</p>"; // TRAD
	}
}

?>
