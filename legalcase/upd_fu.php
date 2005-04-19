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


// Clear all previous errors
$_SESSION['errors'] = array();

$id_followup = 0;
if (isset($_REQUEST['id_followup']) && $_REQUEST['id_followup'] > 0)
	$id_followup = $_REQUEST['id_followup'];

// Get form data from POST fields
foreach($_POST as $key => $value)
    $_SESSION['fu_data'][$key]=$value;

///////////////////////////////////////////////////////////////////////
//	Followup information error checking
///////////////////////////////////////////////////////////////////////
// Convert day, month, year to date
// Check submitted information

// date_start
$_SESSION['fu_data']['date_start'] = get_datetime_from_array($_SESSION['fu_data'], 'start', 'start');

$unix_date_start = strtotime($_SESSION['fu_data']['date_start']);

if ( ($unix_date_start<0) || !checkdate($_SESSION['fu_data']['start_month'],$_SESSION['fu_data']['start_day'],$_SESSION['fu_data']['start_year']) )
	$_SESSION['errors']['date_start'] = 'Invalid start date!';	// TRAD
//else
//	$_SESSION['fu_data']['date_start'] = date('Y-m-d H:i:s', $unix_date_start);

// date_end
if ($prefs['time_intervals']=='absolute') {
	// Set to default empty date if all fields empty
	if (!($_SESSION['fu_data']['end_year'] || $_SESSION['fu_data']['end_month'] || $_SESSION['fu_data']['end_day']))
		$_SESSION['fu_data']['date_end'] = '0000-00-00 00:00:00';
		// Report error if some of the fields empty
	elseif (!$_SESSION['fu_data']['end_year'] || !$_SESSION['fu_data']['end_month'] || !$_SESSION['fu_data']['end_day']) {
		$_SESSION['errors']['date_end'] = 'Partial end date!';	// TRAD
		$_SESSION['fu_data']['date_end'] = get_datetime_from_array($_SESSION['fu_data'], 'end', 'start');
	} else {
		$_SESSION['fu_data']['date_end'] = get_datetime_from_array($_SESSION['fu_data'], 'end', 'start');
		
		$unix_date_end = strtotime($_SESSION['fu_data']['date_end']);
		if ( ($unix_date_end<0) || !checkdate($_SESSION['fu_data']['end_month'],$_SESSION['fu_data']['end_day'],$_SESSION['fu_data']['end_year']) )
			$_SESSION['errors']['date_end'] = 'Invalid end date!';	// TRAD
		//else
		//	$_SESSION['fu_data']['date_end'] = date('Y-m-d H:i:s',$unix_date_end);
	}
} else {
	if ( ! (isset($_SESSION['fu_data']['delta_days']) && (!is_numeric($_SESSION['fu_data']['delta_days']) || $_SESSION['fu_data']['delta_days'] < 0) ||
		isset($_SESSION['fu_data']['delta_hours']) && (!is_numeric($_SESSION['fu_data']['delta_hours']) || $_SESSION['fu_data']['delta_hours'] < 0) ||
		isset($_SESSION['fu_data']['delta_minutes']) && (!is_numeric($_SESSION['fu_data']['delta_minutes']) || $_SESSION['fu_data']['delta_minutes'] < 0) ) ) {
		$unix_date_end = $unix_date_start
				+ $_SESSION['fu_data']['delta_days'] * 86400
				+ $_SESSION['fu_data']['delta_hours'] * 3600
				+ $_SESSION['fu_data']['delta_minutes'] * 60;
		$_SESSION['fu_data']['date_end'] = date('Y-m-d H:i:s', $unix_date_end);
	} else {
		$_SESSION['errors']['date_end'] = 'Invalid time interval!';	// TRAD
		$_SESSION['fu_data']['date_end'] = $_SESSION['fu_data']['date_start'];
	}
}

// Description
/* [ML] This was requested to be optional (MG, PDO)
if ( !(strlen($_SESSION['fu_data']['description']) > 0) )
	$_SESSION['errors']['description'] = _Ti('fu_input_description') . _T('warning_field_mandatory');
*/

///////////////////////////////////////////////////////////////////////
//	Consequent appointment information error checking
///////////////////////////////////////////////////////////////////////
if (isset($_SESSION['fu_data']['add_appointment'])) {
	// Convert day, month, year, hour, minute to date/time
	// Check submitted information
	// start_time
	$_SESSION['fu_data']['app_start_time'] = $_SESSION['fu_data']['app_start_year'] . '-'
						. $_SESSION['fu_data']['app_start_month'] . '-'
						. $_SESSION['fu_data']['app_start_day'] . ' '
						. (isset($_SESSION['fu_data']['app_start_hour']) ? $_SESSION['fu_data']['app_start_hour'] : '00') . ':'
						. (isset($_SESSION['fu_data']['app_start_minutes']) ? $_SESSION['fu_data']['app_start_minutes'] : '00') . ':'
						. (isset($_SESSION['fu_data']['app_start_seconds']) ? $_SESSION['fu_data']['app_start_seconds'] : '00');
	
	$unix_app_start_time = strtotime($_SESSION['fu_data']['app_start_time']);
	
	if ( ($unix_app_start_time<0) || !checkdate($_SESSION['fu_data']['app_start_month'],$_SESSION['fu_data']['app_start_day'],$_SESSION['fu_data']['app_start_year']) )
		$_SESSION['errors']['app_start_time'] = 'Invalid appointment start time!'; // TRAD
	//else 
	//	$_SESSION['fu_data']['app_start_time'] = date('Y-m-d H:i:s', $unix_app_start_time);
	
	//
	// End time
	//
	if ($prefs['time_intervals'] == 'absolute') {
		// Set to default empty date if all fields empty
		if (!($_SESSION['fu_data']['app_end_year'] || $_SESSION['fu_data']['app_end_month'] || $_SESSION['fu_data']['app_end_day']))
			$_SESSION['fu_data']['app_end_time'] = '0000-00-00 00:00:00';
			// Report error if some of the fields empty TODO
		elseif (!$_SESSION['fu_data']['app_end_year'] || !$_SESSION['fu_data']['app_end_month'] || !$_SESSION['fu_data']['app_end_day']) {
			$_SESSION['errors']['app_end_time'] = 'Partial appointment end time!';
			$_SESSION['fu_data']['app_end_time'] = ($_SESSION['fu_data']['app_end_year'] ? $_SESSION['fu_data']['app_end_year'] : '0000') . '-'
								. ($_SESSION['fu_data']['app_end_month'] ? $_SESSION['fu_data']['app_end_month'] : '00') . '-'
								. ($_SESSION['fu_data']['app_end_day'] ? $_SESSION['fu_data']['app_end_day'] : '00') . ' '
								. ($_SESSION['fu_data']['app_end_hour'] ? $_SESSION['fu_data']['app_end_hour'] : '00') . ':'
								. ($_SESSION['fu_data']['app_end_minutes'] ? $_SESSION['fu_data']['app_end_minutes'] : '00') . ':'
								. ($_SESSION['fu_data']['app_end_seconds'] ? $_SESSION['fu_data']['app_end_seconds'] : '00');
		} else {
			// Join fields and check resulting date
			$_SESSION['fu_data']['app_end_time'] = $_SESSION['fu_data']['app_end_year'] . '-'
								. $_SESSION['fu_data']['app_end_month'] . '-'
								. $_SESSION['fu_data']['app_end_day'] . ' '
								. $_SESSION['fu_data']['app_end_hour'] . ':'
								. $_SESSION['fu_data']['app_end_minutes'] . ':'
						. (isset($_SESSION['fu_data']['app_end_seconds']) ? $_SESSION['fu_data']['app_end_seconds'] : '00');
			$unix_app_end_time = strtotime($_SESSION['fu_data']['app_end_time']);
	
			if ( ($unix_app_end_time<0) || !checkdate($_SESSION['fu_data']['app_end_month'],$_SESSION['fu_data']['app_end_day'],$_SESSION['fu_data']['app_end_year']) )
				$_SESSION['errors']['app_end_time'] = 'Invalid appointment end time!';
	//		else 
	//			$_SESSION['fu_data']['app_end_time'] = date('Y-m-d H:i:s',$unix_app_end_time);
		}
	} else {
		if ( ! (isset($_SESSION['fu_data']['app_delta_days']) && (!is_numeric($_SESSION['fu_data']['app_delta_days']) || $_SESSION['fu_data']['app_delta_days'] < 0) ||
			isset($_SESSION['fu_data']['app_delta_hours']) && (!is_numeric($_SESSION['fu_data']['app_delta_hours']) || $_SESSION['fu_data']['app_delta_hours'] < 0) ||
			isset($_SESSION['fu_data']['app_delta_minutes']) && (!is_numeric($_SESSION['fu_data']['app_delta_minutes']) || $_SESSION['fu_data']['app_delta_minutes'] < 0) ) ) {
			$unix_app_end_time = $unix_app_start_time
					+ $_SESSION['fu_data']['app_delta_days'] * 86400
					+ $_SESSION['fu_data']['app_delta_hours'] * 3600
					+ $_SESSION['fu_data']['app_delta_minutes'] * 60;
			$_SESSION['fu_data']['app_end_time'] = date('Y-m-d H:i:s', $unix_app_end_time);
		} else {
			$_SESSION['errors']['app_end_time'] = _Ti('app_input_time_length') . _T('time_warning_invalid_format') . ' (' . $_SESSION['fu_data']['app_delta_hours'] . ')'; // XXX
			$_SESSION['fu_data']['app_end_time'] = $_SESSION['fu_data']['app_start_time'];
		}
	}
	
	// reminder
	if ($prefs['time_intervals']=='absolute') {
		// Set to default empty date if all fields empty
		if (!($_SESSION['fu_data']['app_reminder_year'] || $_SESSION['fu_data']['app_reminder_month'] || $_SESSION['fu_data']['app_reminder_day']))
			$_SESSION['fu_data']['app_reminder'] = '0000-00-00 00:00:00';
			// Report error if some of the fields empty
		elseif (!$_SESSION['fu_data']['app_reminder_year'] || !$_SESSION['fu_data']['app_reminder_month'] || !$_SESSION['fu_data']['app_reminder_day']) {
			$_SESSION['errors']['app_reminder'] = 'Partial appointment reminder time!'; // TRAD
			$_SESSION['fu_data']['app_reminder'] = ($_SESSION['fu_data']['app_reminder_year'] ? $_SESSION['fu_data']['app_reminder_year'] : '0000') . '-'
								. ($_SESSION['fu_data']['app_reminder_month'] ? $_SESSION['fu_data']['app_reminder_month'] : '00') . '-'
								. ($_SESSION['fu_data']['app_reminder_day'] ? $_SESSION['fu_data']['app_reminder_day'] : '00') . ' '
								. ($_SESSION['fu_data']['app_reminder_hour'] ? $_SESSION['fu_data']['app_reminder_hour'] : '00') . ':'
								. ($_SESSION['fu_data']['app_reminder_minutes'] ? $_SESSION['fu_data']['app_reminder_minutes'] : '00') . ':'
								. ($_SESSION['fu_data']['app_reminder_seconds'] ? $_SESSION['fu_data']['app_reminder_seconds'] : '00');
		} else {
			// Join fields and check resulting time
			$_SESSION['fu_data']['app_reminder'] = $_SESSION['fu_data']['app_reminder_year'] . '-'
							. $_SESSION['fu_data']['app_reminder_month'] . '-'
							. $_SESSION['fu_data']['app_reminder_day'] . ' '
							. $_SESSION['fu_data']['app_reminder_hour'] . ':'
							. $_SESSION['fu_data']['app_reminder_minutes'] . ':'
						. (isset($_SESSION['fu_data']['app_reminder_seconds']) ? $_SESSION['fu_data']['app_reminder_seconds'] : '00');
			$unix_app_reminder_time = strtotime($_SESSION['fu_data']['app_reminder']);
	
			if ( ($unix_app_reminder_time<0) || !checkdate($_SESSION['fu_data']['app_reminder_month'],$_SESSION['fu_data']['app_reminder_day'],$_SESSION['fu_data']['app_reminder_year']) )
				$_SESSION['errors']['app_reminder'] = 'Invalid appointment reminder time!'; // TRAD
	//		else 
	//			$_SESSION['fu_data']['app_reminder'] = date('Y-m-d H:i:s',$unix_app_reminder_time);
		}
	} else {
		if ( ! (isset($_SESSION['fu_data']['app_rem_offset_days']) && (!is_numeric($_SESSION['fu_data']['app_rem_offset_days']) || $_SESSION['fu_data']['app_rem_offset_days'] < 0) ||
			isset($_SESSION['fu_data']['app_rem_offset_hours']) && (!is_numeric($_SESSION['fu_data']['app_rem_offset_hours']) || $_SESSION['fu_data']['app_rem_offset_hours'] < 0) ||
			isset($_SESSION['fu_data']['app_rem_offset_minutes']) && (!is_numeric($_SESSION['fu_data']['app_rem_offset_minutes']) || $_SESSION['fu_data']['app_rem_offset_minutes'] < 0) ) ) {
			$unix_app_reminder_time = $unix_app_start_time
					- $_SESSION['fu_data']['app_rem_offset_days'] * 86400
					- $_SESSION['fu_data']['app_rem_offset_hours'] * 3600
					- $_SESSION['fu_data']['app_rem_offset_minutes'] * 60;
			$_SESSION['fu_data']['app_reminder'] = date('Y-m-d H:i:s', $unix_app_reminder_time);
		} else {
			$_SESSION['errors']['app_reminder'] = _Ti('app_input_reminder') . _T('time_warning_invalid_format') . ' (' . $_SESSION['fu_data']['app_rem_offset_hours'] . ')'; // XXX
			$_SESSION['fu_data']['app_reminder'] = $_SESSION['fu_data']['app_start_time'];
		}
	}
	
	// title
	if (! $_SESSION['fu_data']['app_title'])
		$_SESSION['errors']['app_title'] = _Ti('app_input_title') . _T('warning_field_mandatory');
}

//
// Check if any errors found
//
if (count($_SESSION['errors'])) {
    header("Location: " . $GLOBALS['HTTP_REFERER']);
    exit;
}

///////////////////////////////////////////////////////////////////////
//	Followup information update
///////////////////////////////////////////////////////////////////////

	$fl=" date_start = '" . clean_input($_SESSION['fu_data']['date_start']) . "',
		date_end     = '" . clean_input($_SESSION['fu_data']['date_end']) . "',
		type         = '" . clean_input($_SESSION['fu_data']['type']) . "',
		sumbilled    = '" . clean_input($_SESSION['fu_data']['sumbilled']) . "'";

	if ($_SESSION['fu_data']['type'] == 'stage_change' || is_status_change($_SESSION['fu_data']['type'])) {
		$desc = array(
					'description'  => clean_input($_SESSION['fu_data']['description']),
					'conclusion'   => clean_input($_SESSION['fu_data']['conclusion']),
					'sentence'     => clean_input($_SESSION['fu_data']['sentence']),
					'sentence_val' => clean_input($_SESSION['fu_data']['sentence_val']));

		$fl .= ", description = '" . serialize($desc) . "'";
	} else {
		$fl .= ", description  = '" . clean_input($_SESSION['fu_data']['description']) . "'";
	}

	if ($id_followup>0) {
		// Check access rights
		if (!allowed($_SESSION['fu_data']['id_case'],'e')) 
			lcm_panic("You don't have permission to modify this case's information. (" . $_SESSION['fu_data']['id_case'] . ")");

		$q="UPDATE lcm_followup SET $fl WHERE id_followup = $id_followup";
		$result = lcm_query($q);
	} else {
		// Check access rights
		if (!allowed($_SESSION['fu_data']['id_case'],'w'))
			lcm_panic("You don't have permission to add information to this case. (" . $_SESSION['fu_data']['id_case'] . ")");

		// Get the current case stage
		$q = "SELECT stage FROM lcm_case WHERE id_case=" . $_SESSION['fu_data']['id_case'];
		$result = lcm_query($q);
		if ($row = lcm_fetch_array($result)) {
			$case_stage = $row['stage'];
		} else {
			lcm_panic("There is no such case (" . $_SESSION['fu_data']['id_case'] . ")");
		}

		// Update case status
		$status = '';
		$stage = '';
		switch ($_SESSION['fu_data']['type']) {
			case 'conclusion' :
				$status = 'closed';
				break;
			case 'suspension' :
				$status = 'suspended';
				break;
			case 'opening' :
			case 'resumption' :
			case 'reopening' :
				$status = 'open';
				break;
			case 'merge' :
				$status = 'merged';
				break;
			case 'stage_change' :
				$stage = $_POST['new_stage'];
		}
		
		if ($status || $stage) {
			$q = "UPDATE lcm_case
					SET " . ($status ? "status='$status'" : '') . ($status && $stage ? ',' : '') . ($stage ? "stage='$stage'" : '') . "
					WHERE id_case=" . $_SESSION['fu_data']['id_case'];
			$result = lcm_query($q);
		}
		
		// Add the new follow-up
		$q = "INSERT INTO lcm_followup
			SET	id_followup=0,
				id_case=" . $_SESSION['fu_data']['id_case'] . ",
				id_author=" . $GLOBALS['author_session']['id_author'] . ",
				$fl,
				case_stage='$case_stage'";

		if (!($result = lcm_query($q))) 
			lcm_panic("$q<br>\nError ".lcm_errno().": ".lcm_error());

		$id_followup = lcm_insert_id();

		// Set relation to the parent appointment, if any
		if (! empty($_SESSION['fu_data']['id_app'])) {
			$q = "INSERT INTO lcm_app_fu 
					SET id_app=" . $_SESSION['fu_data']['id_app'] . ",
						id_followup=$id_followup, relation='child'";
			$result = lcm_query($q);
		}
	}

//
// Update stage keywords
//
if ($_REQUEST['new_stage']) {
	include_lcm('inc_keywords');
	$stage_info = get_kw_from_name('stage', $_REQUEST['new_stage']);
	$id_stage = $stage_info['id_keyword'];
	update_keywords_request('stage', $id_case, $id_stage);
}

///////////////////////////////////////////////////////////////////////
//	Consequent appointment information update
///////////////////////////////////////////////////////////////////////
if (isset($_SESSION['fu_data']['add_appointment'])) {
	// No errors, proceed with database update
	$fl="	type		= '" . clean_input($_SESSION['fu_data']['app_type']) . "',
		title		= '" . clean_input($_SESSION['fu_data']['app_title']) . "',
		description	= '" . clean_input($_SESSION['fu_data']['app_description']) . "',
		start_time	= '" . $_SESSION['fu_data']['app_start_time'] . "',
		end_time	= '" . $_SESSION['fu_data']['app_end_time'] . "',
		reminder	= '" . $_SESSION['fu_data']['app_reminder'] . "'
		";

	// Add the new appointment
	$q = "INSERT INTO lcm_app SET id_app=0";
	// Add case ID
	$q .= ',id_case=' . $_SESSION['fu_data']['id_case'];
	// Add ID of the creator
	$q .= ',id_author=' . $GLOBALS['author_session']['id_author'];
	// Add the rest of the fields
	$q .= ",$fl,date_creation=NOW()";

	$result = lcm_query($q);

	// Get new appointment's ID
	$id_app = lcm_insert_id();
	$_SESSION['fu_data']['id_app'] = $id_app;

	// Add relationship with the creator
	lcm_query("INSERT INTO lcm_author_app SET id_app=$id_app,id_author=" . $GLOBALS['author_session']['id_author']);

	// Add followup relation
	lcm_query("INSERT INTO lcm_app_fu SET id_app=$id_app,id_followup=$id_followup,relation='parent'");
}

// Send user back to add/edit page's referer or (default) to followup detail page
header('Location: ' . ($_SESSION['fu_data']['ref_edit_fu'] ? $_SESSION['fu_data']['ref_edit_fu'] : "fu_det.php?followup=$id_followup"));

exit;

?>
