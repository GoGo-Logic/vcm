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
//lcm_page_start("Select client(s)");

$case = intval($_GET['case']);

if (! $case)
	die("There's no such case!");

//
// Show only clients who are not already in the case
// Extract the clients on the case, then put them in a "not in" list
//
$q = "SELECT *
		FROM lcm_case_client_org
		WHERE (id_case=$case AND id_client>0)";

$result = lcm_query($q);

$q2 = "SELECT id_client,name_first,name_middle,name_last
		FROM lcm_client
		WHERE (id_client NOT IN (0";

// Build "not in" list
while ($row = lcm_fetch_array($result)) {
	$q2 .= ',' . $row['id_client'];
}

$q2 .= ')';

// Add search criteria if any
if (strlen($find_client_string)>1) {
	$q2 .= " AND ((name_first LIKE '%$find_client_string%')"
		. " OR (name_middle LIKE '%$find_client_string%')"
		. " OR (name_last LIKE '%$find_client_string%'))";
	lcm_page_start("Select client(s) from these, containing '$find_client_string':");
} else {
	lcm_page_start("Select client(s)");
}

$q2 .= ")";


$result = lcm_query($q2);

// Get the number of rows in the result
$number_of_rows = lcm_num_rows($result);

// Check for correct start position of the list
if ($list_pos>=$number_of_rows) $list_pos = 0;

// Position to the page info start
if ($list_pos>0)
	if (!lcm_data_seek($result,$list_pos))
		die("Error seeking position $list_pos in the result");

// Search form
?>
<form name="frm_find_client" class="search_form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
	Find client:&nbsp;<input type="text" name="find_client_string" size="10" class="search_form_txt"<?php

//	if (isset($find_client_string)) echo " value='$find_client_string'";
	echo " value='$find_client_string'";

?> />&nbsp;<input type="submit" name="submit" value="Search" class="search_form_btn" />
</form>

<ul><li>Todo: Search for client + if list too long, show only search.</li>
<li>Todo: Show case overview.</li></ul>

<form action="add_client.php" method="post">
	<table border="0" class="tbl_usr_dtl">

		<tr>
			<th class="heading">&nbsp;</th>
			<th class="heading" width="350">Client name</th>
			<th class="heading">&nbsp;</th>
		</tr>
<?php

// Process the output of the query
for ($i = 0 ; (($i<$prefs['page_rows']) && ($row = lcm_fetch_array($result))) ; $i++) {
	// Show checkbox
	echo "\t<tr><td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	echo "<input type='checkbox' name='clients[]' value='" . $row['id_client'] "></td>\n";
	// Show client name
	echo "\t\t<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	echo highlight_matches(clean_output($row['name_first'] . ' ' . $row['name_middle'] . ' '
		. $row['name_last']),$find_author_string);
	echo "</td>\n";
	// Show client edit link
	echo "\t\t<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	echo '<a href="edit_client.php?client=' . $row['id_client'] . '" class="content_link">Edit</a>';
	echo "</td>\n";

//	if (($GLOBALS['author_session']['status'] == 'admin') ||
//		($row['id_author'] == $GLOBALS['author_session']['id_author']))
//			echo '<a href="edit_author.php?author=' . $row['id_author'] . '" class="content_link">Edit</a>';

	echo "\t</tr>\n";
}

?>
		<tr>
			<td></td>
<?php
	echo '<td><a href="edit_client.php?attach_case=' . $case . '" class="content_link">' . 'Create a new client and attach to case' . '</a></td>' . "\n";
?>
		<td></td>
		</tr>
	</table>

	<table border='0' align='center' width='99%' class='page_numbers'>
		<tr><td align="left" width="15%"><?php

// Show link to previous page
if ($list_pos>0) {
	echo '<a href="' . $_SERVER['REQUEST_URI'];
	if ($list_pos>$prefs['page_rows']) echo '?list_pos=' . ($list_pos - $prefs['page_rows']);
	if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
	echo '" class="content_link">< Prev</a> ';
}

echo "</td>\n\t\t\t<td align='center' width='70%'>";

// Show page numbers with direct links
$list_pages = ceil($number_of_rows / $prefs['page_rows']);
if ($list_pages>1) {
	echo 'Go to page: ';
	for ($i=0 ; $i<$list_pages ; $i++) {
		if ($i==floor($list_pos / $prefs['page_rows'])) echo '['. ($i+1) . '] ';
		else {
			echo '<a href="' . $_SERVER['REQUEST_URI'] . '?list_pos=' . ($i*$prefs['page_rows']);
			if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
			echo '" class="content_link">' . ($i+1) . '</a> ';
		}
	}
}

echo "</td>\n\t\t\t<td align='right' width='15%'>";

// Show link to next page
$next_pos = $list_pos + $prefs['page_rows'];
if ($next_pos<$number_of_rows) {
	echo '<a href="' . $_SERVER['REQUEST_URI'] . "?list_pos=$next_pos";
	if (strlen($find_client_string)>1) echo "&amp;find_client_string=" . rawurlencode($find_client_string);
	echo '" class="content_link">Next ></a>';
}

echo "</td>\n\t\t</tr>\n\t</table>\n";

?>
	<input type="hidden" name="case" value="<?php echo $case; ?>">
	<input type="hidden" name="ref_sel_client" value="<?php echo $GLOBALS['HTTP_REFERER']; ?>">
	<button name="submit" type="submit" value="submit" class="simple_form_btn"><?php echo _T('button_validate'); ?></button>
</form>

<?php

lcm_page_end();

?>
