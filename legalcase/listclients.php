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

lcm_page_start("List of clients");

// List all clients in the system + search criterion if any
$q = "SELECT id_client,name_first,name_middle,name_last
		FROM lcm_client";

$find_client_string = '';
if (isset($_REQUEST['find_client_string']))
	$find_client_string = $_REQUEST['find_client_string'];

if (strlen($find_client_string)>1) {
	// Add search criteria
	$q .= " WHERE ((name_first LIKE '%$find_client_string%')
			OR (name_middle LIKE '%$find_client_string%')
			OR (name_last LIKE '%$find_client_string%'))";
}

$result = lcm_query($q);

// Get the number of rows in the result
$number_of_rows = lcm_num_rows($result);

// Check for correct start position of the list
$list_pos = 0;

if (isset($_REQUEST['list_pos']))
	$list_pos = $_REQUEST['list_pos'];

if ($list_pos >= $number_of_rows)
	$list_pos = 0;

// Position to the page info start
if ($list_pos > 0)
	if (!lcm_data_seek($result,$list_pos))
		lcm_panic("Error seeking position $list_pos in the result");

echo '<form name="frm_find_client" class="search_form" action="listclients.php" method="get">' . "\n";
echo _T('input_search_client') . "&nbsp;";
echo '<input type="text" name="find_client_string" size="10" class="search_form_txt" value="' .  $find_client_string . '" />';
echo '&nbsp;<input type="submit" name="submit" value="' . _T('button_search') . '" class="search_form_btn" />' . "\n";
echo "</form>\n";

// Output table tags
?>
<table border='0' width='99%' class='tbl_usr_dtl'>
	<tr>
		<th class='heading'>Client name</th>
	</tr>

<?php

for ($i = 0 ; (($i < $prefs['page_rows']) && ($row = lcm_fetch_array($result))) ; $i++) {
	echo "<tr>\n";
	echo '<td class="tbl_cont_' . ($i % 2 ? "dark" : "light") . '">';
	echo '<a href="client_det.php?client=' . $row['id_client'] . '" class="content_link">';
	$fullname = clean_output($row['name_first'] . ' ' . $row['name_middle'] . ' ' . $row['name_last']);
	echo highlight_matches($fullname, $find_client_string);
	echo "</td>\n";

	// [ML] Better not to allow to edit a client before the user can know exactly who it is
	// echo "<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>"; <a href="edit_client.php?client=<?php echo $row['id_client']; " class="content_link">Edit</a></td>
	echo "</tr>\n";
}

?>

</table>

<table border='0' align='center' width='99%' class='page_numbers'>
	<tr><td align="left" width="15%">
<?php

// Show link to previous page
if ($list_pos>0) {
	echo '<a href="listclients.php';
	if ($list_pos>$prefs['page_rows']) echo '?list_pos=' . ($list_pos - $prefs['page_rows']);
	if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
	echo '" class="content_link">< Prev</a> ';
}

echo "</td>\n\t\t<td align='center' width='70%'>";

// Show page numbers with direct links
$list_pages = ceil($number_of_rows / $prefs['page_rows']);
if ($list_pages>1) {
	echo 'Go to page: ';
	for ($i=0 ; $i<$list_pages ; $i++) {
		if ($i==floor($list_pos / $prefs['page_rows'])) echo '['. ($i+1) .'] ';
		else {
			echo '<a href="listclients.php?list_pos=' . ($i*$prefs['page_rows']);
			if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
			echo '" class="content_link">' . ($i+1) . '</a> ';
		}
	}
}

echo "</td>\n\t\t<td align='right' width='15%'>";

// Show link to next page
$next_pos = $list_pos + $prefs['page_rows'];
if ($next_pos<$number_of_rows) {
	echo "<a href=\"listclients.php?list_pos=$next_pos";
	if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
	echo '" class="content_link">Next ></a>';
}
echo "</td>\n\t</tr>\n</table>\n";
?>
<a href="edit_client.php" class="create_new_lnk">Add new client</a>
<br /><br />
<?php
lcm_page_end();
?>
