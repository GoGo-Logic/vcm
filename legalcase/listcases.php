<?php

include('inc/inc.php');
include_lcm('inc_acc');

// Prepare query
$q = "SELECT lcm_case.id_case,title,public,pub_write
		FROM lcm_case,lcm_case_author
		WHERE (lcm_case.id_case=lcm_case_author.id_case
			AND lcm_case_author.id_author=" . $GLOBALS['author_session']['id_author'];

// Add search criteria if any
if (strlen($find_case_string)>1) {
	$q .= " AND (lcm_case.title LIKE '%$find_case_string%')";
	lcm_page_start("Cases, containing '$find_case_string':");
} else {
	lcm_page_start("List of cases");
}

$q .= ")";

// TODO - add case filter based on user/case status to query

// Do the query
$result = lcm_query($q);

?>

<table border='1' align='center'>
<tr><th colspan="3">Case description</th></tr>
<?php
// Process the output of the query
while ($row = lcm_fetch_array($result)) {
	// Show case title
	echo '<tr><td>';
	if (allowed($row['id_case'],'r')) echo '<a href="case_det.php?case=' . $row['id_case'] . '">';
	if (strlen($find_case_string)>1) {
		echo implode("<b>$find_case_string</b>",explode($find_case_string,$row['title']));
	} else {
		echo $row['title'];
	}
	if (allowed($row['id_case'],'r')) echo '</a>';
	echo "</td>\n<td>";
	if (allowed($row['id_case'],'e'))
		echo '<a href="edit_case.php?case=' . $row['id_case'] . '">Edit case</a>';
	echo "</td>\n<td>";
	if (allowed($row['id_case'],'w'))
		echo '<a href="edit_fu.php?case=' . $row['id_case'] . '">Add followup</a>';
	echo "</td></tr>\n";
}

?>
<tr><td colspan="3"><a href="edit_case.php?case=0">Open new case</a></td></tr>
</table>

<?php
	lcm_page_end();
?>
