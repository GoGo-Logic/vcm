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

$app = intval($_GET['app']);

$q = "SELECT lcm_app.*,lcm_author.name_first,lcm_author.name_middle,lcm_author.name_last,lcm_case.title AS case_title
	FROM lcm_app, lcm_author_app, lcm_author, lcm_case
	WHERE (lcm_app.id_app=$app
		AND lcm_author_app.id_app=$app
		AND lcm_author_app.id_author=" . $GLOBALS['author_session']['id_author'] . "
		AND lcm_app.id_author=lcm_author.id_author
		AND lcm_app.id_case=lcm_case.id_case)";
$result = lcm_query($q);

if ($row = lcm_fetch_array($result)) {
	lcm_page_start('Appointment details:' . $row['title']);

	echo "Start time: " . $row['start_time'] . "<br />\n";
	echo "End time: " . $row['end_time'] . "<br />\n";
	echo "Reminder: " . $row['reminder'] . "<br />\n";
	echo "Type: " . $row['type'] . "<br />\n";
	echo "Title: " . $row['title'] . "<br />\n";
	echo "Description: " . $row['description'] . "<br />\n";
	echo "Created by: " . join(' ',array($row['name_first'],$row['name_middle'],$row['name_last'])) . "<br />\n";
	echo "In connection with: " . $row['case_title'] , "<br />\n";

	// Show appointment participants
	$q = "SELECT lcm_author_app.*,lcm_author.name_first,lcm_author.name_middle,lcm_author.name_last
		FROM lcm_author_app, lcm_author
		WHERE (id_app=" . $row['id_app'] . "
			AND lcm_author_app.id_author=lcm_author.id_author)";
	$res_author = lcm_query($q);
	if (lcm_num_rows($res_author)>0) {
		echo "Participants: ";
		while ($author = lcm_fetch_array($res_author)) {
			echo  (strlen($author['name_first'])>0 ? $author['name_first'] . ' ' : '')
			. (strlen($author['name_middle'])>0 ? $author['name_middle'] . ' ' : '')
			. $author['name_last'];
		}
		echo "<br />\n";
	}
	
	// Show appointment clients
	$q = "SELECT lcm_app_client_org.*,lcm_client.name_first,lcm_client.name_middle,lcm_client.name_last,lcm_org.name
		FROM lcm_app_client_org, lcm_client
		LEFT JOIN  lcm_org ON lcm_app_client_org.id_org=lcm_org.id_org
		WHERE (id_app=" . $row['id_app'] . "
			AND lcm_app_client_org.id_client=lcm_client.id_client)";
	$res_client = lcm_query($q);

	if (lcm_num_rows($res_client)>0) {
		echo "Clients: ";
		$clients = array();
		while ($client = lcm_fetch_array($res_client))
			$clients[] = join(' ',array($client['name_first'],$client['name_middle'],$client['name_last']))
				. ( ($client['id_org'] > 0) ? " of " . $client['name'] : '');
		echo join(',',$clients);
		echo "<br />\n";
	}

	lcm_page_end();
}

?>