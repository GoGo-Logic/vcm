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

define('DIR_BACKUPS_PREFIX', 'inc/data/db-');

$tabs = array(	array('name' => _T('archives_tab_all_cases'), 'url' => 'archive.php'),
		array('name' => _T('archives_tab_export'), 'url' => 'export_db.php'),
		array('name' => _T('archives_tab_import'), 'url' => 'import_db.php')
	);

function show_export_form_partial() {
	//
	// New backup
	//
	echo "<fieldset class='info_box'>\n";
	show_page_subtitle(_T('archives_subtitle_new')); // HELP
	
	echo "<form action='export_db.php' method='post'>\n";
	echo '<p class="normal_text">' . _T('archives_info_what_is_backup') . "</p>\n";
	echo '<p><a class="exp_lnk" href="export_db.php?action=export">' . _T('archives_button_new') . "</a></p>\n";
	echo "</form>\n";
	echo "</fieldset>\n";

	//
	// Existing backups
	//
	echo "<fieldset class='info_box'>\n";
	echo "<a name='listbk'></a>\n";
	show_page_subtitle(_T('archives_subtitle_previously_made')); // HELP
	
	$storage = opendir('inc/data');
	$html = "";

	while (($file = readdir($storage)))
		if (is_dir("inc/data/$file") && (strpos($file, 'db-') === 0)) {
			$file = substr($file, 3);
			$css = 'tbl_cont_' . ($cpt++ % 2 ? "dark" : "light");

			$html .= "<tr>\n";
			$html .= '<td class="' . $css . '">' . $file . "</td>\n";
			$html .= '<td nowrap="nowrap" width="1%" class="' . $css . '">' . get_delete_box($file, "rem_file", "test") . "</td>\n";
			$html .= "</tr>\n";
		} elseif (is_file("inc/data/$file") && (strpos($file, 'db-') === 0)) {
			$file = substr($file, 3);
			$css = 'tbl_cont_' . ($cpt++ % 2 ? "dark" : "light");

			$html .= "<tr>\n";
			$html .= '<td class="' . $css . '">';
			$html .= '<a class="content_link" href="export_db.php?action=download&file=' . $file . '">' . $file . '</a>';
			$html .= ' (' . filesize_in_bytes("inc/data/db-" . $file) . ')';
			$html .= "</td>\n";
			$html .= '<td nowrap="nowrap" width="1%" class="' . $css . '">' . get_delete_box($file, "rem_file", "test") . "</td>\n";
			$html .= "</tr>\n";
		}
	
	if ($html) {
		echo '<p class="normal_text">' . _T('archives_info_how_to_download') . "</p>\n";

		echo '<form action="export_db.php" method="post">' . "\n";
		echo '<input type="hidden" name="action" value="rem_file" />' . "\n";

		echo '<div style="height: 250px; overflow: auto;">';
		echo '<table border="0" align="center" class="tbl_usr_dtl" width="99%">' . "\n";
		echo $html;
		echo "</table>\n";
		echo "</div>\n";

		echo '<div align="right" style="visibility: hidden;">';
		echo '<input type="submit" name="submit" id="btn_delete" value="' . _T('button_delete') . '" class="search_form_btn" />';
		echo "</div>\n";
		echo "</form>\n";
	} else {
		echo '<p class="normal_text">' . _T('archives_info_no_previous') . "</p>\n";
	}

	echo "</fieldset>\n";

}

function show_export_form() {
	global $tabs;

	lcm_page_start(_T('title_archives')); // HELP?
	show_tabs_links($tabs,1);
	lcm_bubble('archive_create');
	show_export_form_partial();
	lcm_page_end();
}

function deldir($dir) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh))) {
			$fullpath = $dir . '/' . $file;
			if (is_dir($fullpath)) {
				if ($file!='.' && $file!='..') deldir($fullpath);
			} else unlink($fullpath);
		}
		closedir($dh);
		return (rmdir($dir));
	} else return false;
}

function export_database($output_filename = '', $ignore_old = false) {
	global $tabs;
	$output_filename = clean_input($output_filename);

	if (! $output_filename)
		$output_filename = "lcm-" . date('Ymd');

	//
	// Check if file exists. If exists, add a revision number to name (ex: foo-2)
	//
	$root = addslashes(getcwd());
	$cpt = 0;

	while (file_exists("$root/inc/data/db-$output_filename" . ($cpt ? "-" . $cpt : '')))
		$cpt++;

	if ($cpt)
		$output_filename .= "-" . $cpt;

	//
	// Export database
	//
	if (! mkdir("$root/inc/data/db-$output_filename",0777))
		lcm_panic("Could not create $root/inc/data/db-$output_filename");

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

	// By default, in most installations, directory will have 0777 mode
	// and will be owned by the Apache process' user.
	chmod("$root/inc/data/db-$output_filename", 0700);

	@include("Archive/Tar.php");
	$tar_worked = false;

	if (class_exists("Archive_Tar")) {
		$tar_worked = true;
		$tar_object = new Archive_Tar("inc/data/db-$output_filename.tar");

		$files = array();
		$file_dir = opendir("inc/data/db-$output_filename");

		while (($file = readdir($file_dir)))
			if (is_file("inc/data/db-$output_filename/" . $file))
				$files[] = "inc/data/db-$output_filename/" . $file;

		if (count($files)) {
			$tar_object->setErrorHandling(PEAR_ERROR_PRINT);
			$tar_object->create($files)
				or lcm_panic("Could not add files " . get_var_dump($files));
		}
	}

	//
	// Finished
	//
	lcm_page_start(_T('title_archives')); // HELP?
	show_tabs_links($tabs, 1);
	echo '<div class="sys_msg_box">' . "\n";

	if ($tar_worked) {
		$name = '<a class="content_link" href="export_db.php?action=download&file=' . $output_filename . '.tar">'
			. $output_filename . '.tar'
			. '</a> ('
			. filesize_in_bytes("inc/data/db-" . $output_filename . ".tar")
			. ')';

		echo _T('archives_info_new_success', array('name' => $name));
	} else {
		echo _T('archives_info_new_success', array('name' => $output_filename));
	}

	echo "</div>\n";
	show_export_form_partial();
	lcm_page_end();
}

function download_backup($file) {
	
	// file name can only be with alpha-numeric characters, _, - and .
	// ex: db-lcm-20050101.tar.gz
	if (! preg_match("/^([-_\.a-zA-Z0-9]+)$/", $file))
		lcm_panic("Access denied: file name format not accepted.");

	if (! is_file(DIR_BACKUPS_PREFIX . $file))
		lcm_panic("Access denied: file does not exist (" . DIR_BACKUPS_PREFIX . $file . ").");

	if (($fh = fopen(DIR_BACKUPS_PREFIX . $file, "r"))) {
		header("Content-Type: application/x-gtar");
		header('Content-Disposition: filename="db-' . $file . '"');
		header("Content-Description: $file");
		header("Content-Transfer-Encoding: binary");
	
		while (($data = fread($fh, filesize(DIR_BACKUPS_PREFIX . $file))))
			echo $data;

		fclose($fh);
	}
}

function delete_backup($file) {
	// file name can only be with alpha-numeric characters, _, - and .
	// ex: db-lcm-20050101.tar.gz
	if (! preg_match("/^([-_\.a-zA-Z0-9]+)$/", $file))
		lcm_panic("Access denied: file name format not accepted.");

	if (is_dir(DIR_BACKUPS_PREFIX . $file))
		deldir(DIR_BACKUPS_PREFIX . $file);
	elseif (is_file(DIR_BACKUPS_PREFIX . $file))
		unlink(DIR_BACKUPS_PREFIX . $file);
}

//
// Main
//

global $author_session;

// Restrict page to administrators
if ($author_session['status'] != 'admin') {
	lcm_page_start(_T('title_archives'), '', '', ''); // HELP?
	echo '<p class="normal_text">' . _T('warning_forbidden_not_admin') . "</p>\n";
	lcm_page_end();
	exit;
}

switch($_REQUEST['action']) {
	case 'export':
		// Automatic name (lcm-YYYYMMDD)
		export_database();
		break;

	case 'download':
		download_backup($_REQUEST['file']);
		break;
	
	case 'rem_file':
		foreach($_REQUEST['rem_file'] as $key => $val)
			delete_backup($val);

		header('Location: export_db.php#listbk');
		break;

	default:
		show_export_form();
}

?>
