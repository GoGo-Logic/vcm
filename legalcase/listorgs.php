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

$find_org_string = '';
if (isset($_REQUEST['find_org_string']))
	$find_org_string = $_REQUEST['find_org_string'];

lcm_page_start(_T('title_org_list'));
show_find_box('org', $find_org_string);

// List all organisations in the system + search criterion if any
$q = "SELECT id_org,name
		FROM lcm_org";

if (strlen($find_org_string) > 1)
	$q .= " WHERE (name LIKE '%$find_org_string%')";

// Sort organisations by name
$order_name = 'ASC';
if (isset($_REQUEST['order_name']))
	if ($_REQUEST['order_name'] == 'ASC' || $_REQUEST['order_name'] == 'DESC')
		$order_name = $_REQUEST['order_name'];

$q .= " ORDER BY name " . $order_name;

$result = lcm_query($q);
$number_of_rows = lcm_num_rows($result);

// Check for correct start position of the list
$list_pos = get_list_pos($result);

// Output table tags
// Not worth creating show_listorgs_*() for now
$cpt = 0;
$headers = array();

$headers[0]['title'] = _Th('org_input_name');
$headers[0]['order'] = 'order_name';
$headers[0]['default'] = 'ASC';

show_list_start($headers);

for ($i = 0 ; (($i < $prefs['page_rows']) && ($row = lcm_fetch_array($result))) ; $i++) {
	echo "<tr>\n";
	echo "<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	echo '<a href="org_det.php?org=' . $row['id_org'] . '" class="content_link">';
	echo highlight_matches(clean_output($row['name']), $find_org_string);
	echo "</td>\n";
	echo "</tr>\n";
}

show_list_end($list_pos, $number_of_rows);

echo '<p><a href="edit_org.php" class="create_new_lnk">' . "Register new organisation" . "</a></p>\n"; // TRAD
echo "<br />\n";

lcm_page_end();

?>
