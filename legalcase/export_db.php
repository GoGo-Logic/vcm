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
include_lcm('inc_conditions');

function get_parameters() {
	lcm_page_start('Export database');

	// Create form
	echo "\n<form action='export_db.php' method='POST'>\n";
	echo "\t<select name='old_name'>\n";
	echo "\t\t<option selected>-- Create new file --</option>\n";
	// Read existing backups
	$storage = opendir('inc/data');
	while (false !== ($file = readdir($storage))) {
//		var_dump($file);
		if (is_dir("inc/data/$file") && (strpos($file,'db-')===0)) {
			echo "\t\t<option value='" . substr($file,3) . "'>" . substr($file,3) . "</option>\n";
		}
	}
	echo "\t</select>\n";
	echo "\tName: <input name='new_name' />\n";
	echo "\t<button type='submit' class='simple_form_btn'>Export</button>\n";
	echo "</form>\n";

	lcm_page_end();
}

function deldir($dir) {
//	echo "Deleting $dir...";
	if ($dh = opendir($dir)) {
		while (false !== ($file = readdir($dh))) {
			$fullpath = $dir . '/' . $file;
			if (is_dir($fullpath)) {
				if ($file!='.' && $file!='..') deldir($fullpath);
			} else unlink($fullpath);
		}
		closedir($dh);
		return (rmdir($dir));
	} else return false;
}

function export_database($output_filename) {
	// Clean input data
	$output_filename = clean_input($output_filename);
	// Check if file exists
	$root = addslashes(getcwd());
	if (file_exists("$root/inc/data/db-$output_filename")) {
		if ($_POST['conf']!=='yes') {
			// Print confirmation form
			lcm_page_start("Warning!");
			echo "<form action='export_db.php' method='POST'>\n";
			echo "\tBackup named '$output_filename' already exists. Do you want to overwrite it?<br />\n";
			echo "\t<button type='submit' class='simple_form_btn' name='conf' value='yes'>Yes</button>\n";
			echo "\t<button type='submit' class='simple_form_btn' name='conf' value='no'>No</button>\n";
			echo "\t<input type='hidden' name='new_name' value='$output_filename' />\n";
			echo "</form>";
			lcm_page_end();
			return;
		} else {
			// Delete old backup dir
			if (!deldir("$root/inc/data/db-$output_filename"))
				die("System error: Could not erase $root/inc/data/db-$output_filename!");
		}
	}

	// Export database
	if (!mkdir("$root/inc/data/db-$output_filename",0700))
		die("System error: Could not create $root/inc/data/db-$output_filename!");
//	if (!chdir("$root/inc/data/db-$output_filename"))
//		die("System error: Could not change dir to '$root/inc/data/db-$output_filename'");
	// Record database version
	$file = fopen("$root/inc/data/db-$output_filename/db-version",'w');
	fwrite($file,read_meta('lcm_db_version'));
	fclose($file);

	// Get the list of tables in the database
	$q = "SHOW TABLES";
	$result = lcm_query($q);
	while ($row = lcm_fetch_array($result)) {
		// Backup table structure
		$q = "SHOW CREATE TABLE " . $row[0];
		$res = lcm_query($q);
		$sql = lcm_fetch_row($res);
		$file = fopen("$root/inc/data/db-$output_filename/" . $row[0] . ".structure",'w');
		fwrite($file,$sql[1]);
		fclose($file);

		// Backup data
		$q = "SELECT * FROM " . $row[0] . "
				INTO OUTFILE '$root/inc/data/db-$output_filename/" . $row[0] . ".data'
				FIELDS TERMINATED BY ','
					OPTIONALLY ENCLOSED BY '\"'
					ESCAPED BY '\\\\'
				LINES TERMINATED BY '\r\n'";
		$res = lcm_query($q);
	}

}

//
// Main
//
if ($_POST['new_name'] && ($_POST['conf']!=='no')) {
	// Proceed with export in new file
	$log = export_database($_POST['new_name']);
} else if ($_POST['old_name'] && ($_POST['conf']!=='no')) {
	// Proceed with export overwriting old backup
	$log = export_database($_POST['old_name']);
} else {
	// Get export parameters
	get_parameters();
}


?>
