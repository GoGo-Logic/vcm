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
include_lcm('inc_filters');

// Clear all previous errors
$_SESSION['errors'] = array();

// Get form data from POST fields
foreach($_POST as $key => $value)
    $_SESSION['org_data'][$key] = $value;
$id_org = intval($_POST['id_org']);

// Check submitted information
if (!(strlen($_SESSION['org_data']['name'])>0)) { $_SESSION['errors']['name'] = 'Organization should have a name!'; }
//if (strtotime($_SESSION['org_data']['date_creation'])<0) { $_SESSION['errors']['date_creation']='Invalid creation date!'; }
//if (strtotime($_SESSION['org_data']['date_update'])<0) { $_SESSION['errors']['date_update']='Invalid update date!'; }

// Add timestamp
//$_SESSION['org_data']['date_update'] = date('Y-m-d H:i:s'); // now

if (count($_SESSION['errors'])) {
	// Return to edit page
	header("Location: $HTTP_REFERER");
	exit;
} else {
	// Record data in database
	$ol="name='" . clean_input($_SESSION['org_data']['name']) . "'," .
//		date_creation='" . clean_input($_SESSION['org_data']['date_creation']) . "',
//		date_update='" . clean_input($_SESSION['org_data']['date_update']) . "',
		"address='" . clean_input($_SESSION['org_data']['address']) . "'";

	if ($id_org>0) {
		// Prepare query
		$q="UPDATE lcm_org SET date_update=NOW(),$ol WHERE id_org=$id_org";
	} else {
		$q="INSERT INTO lcm_org SET id_org=0,date_creation=NOW(),$ol";
	}

	// Do the query
	if (!($result = lcm_query($q))) die("$q<br>\nError ".lcm_errno().": ".lcm_error());
	//echo $q;

	// Send user back to add/edit page's referer
	header('Location: ' . $_SESSION['org_data']['ref_edit_org']);
}

?>
