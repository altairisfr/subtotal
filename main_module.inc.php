<?php
/*
 * Licensed under the GNU GPL v3 or higher (See file gpl-3.0.html)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 *
 *
 * * * * * * INSTRUCTIONS TO USE (for module developers)
 *
 * 	1. include this PHP file in the root of your module
 *
 * 	2. at the top of any PHP file of your module needing to load main.inc.php put his:
 *
 * 		include_once 'main_module.inc.php';
 *
 */

 // ACTIVATE the ERROR reporting (use only to debug)
// ini_set('display_errors',1);ini_set('display_startup_errors',1);ini_set('error_reporting', E_ALL);

$version   = '2.0';
$path      = '';
$error_msg = '';
$sep       = DIRECTORY_SEPARATOR;

$a_debug   = array();
$a_debug[] = 'OS directory separator: '.$sep;

// 1. try to get the location of main.inc.php from CACHED TEXT FILE

// 1.1 try /documents/main_module_inc_php.txt

$cache_filepath = __DIR__.$sep.s('../../../documents/main_module_inc_php.txt');
if (file_exists($cache_filepath)) {
	$cached_path = @file_get_contents($cache_filepath);
	$a_debug[] = 'The cache file exists: '.$cache_filepath;
	$a_debug[] = 'Content of the cache file: '.$cached_path;
	if (!empty($cached_path) && file_exists($cached_path) && @include $cached_path) {
		return;
	} else {
		$a_debug[] = 'Unable to load the main.inc.php at: '.$cached_path;
		$__DIR__parent = get_parent_absolute_path();
	}
} else {
	$a_debug[] = 'Does not exist the cache file: '.$cache_filepath;

	// 1.2 try /custom/main_module_inc_php

	$__DIR__parent = get_parent_absolute_path();
	$a_debug[] = 'Absolute path of the PARENT directory: '.$__DIR__parent;
	$cache_filepath = $__DIR__parent.$sep."main_module_inc_php";
	if (file_exists($cache_filepath)) {
		$cached_path = @file_get_contents($cache_filepath);
		$a_debug[] = 'The cache file exists: '.$cache_filepath;
		$a_debug[] = 'Content of the cache file: '.$cached_path;
		if (file_exists($cached_path) && @include $cached_path) {
			return;
		} else {
			$a_debug[] = 'Unable to load the main.inc.php at: '.$cached_path;
		}
	} else {
		$a_debug[] = 'Does not exist the cache file: '.$cache_filepath;
	}
}

// 2. Try into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)

if (!empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$CONTEXT_DOCUMENT_ROOT_filepath = s($_SERVER["CONTEXT_DOCUMENT_ROOT"]) .$sep. "main.inc.php";
	if (file_exists($CONTEXT_DOCUMENT_ROOT_filepath)) {
		$a_debug[] = 'Candidate file exists at: '.$CONTEXT_DOCUMENT_ROOT_filepath;
		if (@include $CONTEXT_DOCUMENT_ROOT_filepath) {
			$a_debug[] = 'Successfully loaded !';
			$path = $CONTEXT_DOCUMENT_ROOT_filepath;
		} else {
			$a_debug[] = 'Unable to load it !';
		}
	}
}

// 3. try to find main.inc.php in all parent folders calculated from SCRIPT_FILENAME

if ($path == '' && !empty($_SERVER['SCRIPT_FILENAME'])) {
	$a_debug[] = 'Trying the SCRIPT_FILENAME: '.$_SERVER['SCRIPT_FILENAME'];
	$dolipath = s(dirname($_SERVER['SCRIPT_FILENAME']));
	$a_debug[] = 'Container directory of SCRIPT_FILENAME: '.$dolipath;
	while (!file_exists($dolipath.$sep."main.inc.php")) {
		$a_debug[] = 'No file exists: '.$dolipath.$sep."main.inc.php";
		$abspath = $dolipath;
		$dolipath = dirname($dolipath); // /var/www/htdocs/custom -> /var/www/htdocs
		$dolipath = rtrim($dolipath, $sep); // only useful in windows for the root part of the path C:\ -> C:
		if ($abspath == $dolipath) { // cope with no main.inc.php all the way to filesystem root
			break;
		}
	}
	if (!file_exists($dolipath.$sep."main.inc.php")) {
		$a_debug[] = 'No found main.inc.php on parent folders of SCRIPT_FILENAME.';
	} elseif (@include $dolipath.$sep."main.inc.php") {
		$a_debug[] = 'Found & loaded main.inc.php at: '.$dolipath.$sep."main.inc.php";
		$path = $dolipath.$sep."main.inc.php";
	} else {
		$a_debug[] = 'Exists but cannot be loaded main.inc.php at: '.$dolipath.$sep."main.inc.php";
	}
}

// 4. try to find main.inc.php in the parent directories, using RELATIVE PATHS (we must use unix separator also for windows: /)

if ($path == '') {
	$dolipath = "../";
	$a_debug[] = 'Trying parent relative paths from: ../';
	$ii=0;
	while (is_dir($dolipath) && !file_exists($dolipath."main.inc.php")) { $ii++;
		$a_debug[] = 'No found: '.$dolipath."main.inc.php";
		$dolipath  = "../".$dolipath;
		// we need to fix a reasonable limit of 10 nested levels
		// and because is_dir() can return TRUE although we try 1000 levels, due to open_basedir restrictions
		if ($ii>10) break;
	}
	if (!file_exists($dolipath."main.inc.php")) {
		$a_debug[] = 'No found main.inc.php on parent folders of ../ using RELATIVE PATHS.';
	} elseif (@include $dolipath."main.inc.php") {
		$a_debug[] = 'Found & loaded main.inc.php at: '.$dolipath."main.inc.php";
		$path = $dolipath."main.inc.php";
	} else {
		$a_debug[] = 'Exists but cannot be loaded main.inc.php at: '.$dolipath."main.inc.php";
	}
}

// 5. try to find main.inc.php in the LOCATIONS used by some providers

// Bitnami over AWS
if ($path == '') {
	if (file_exists("/opt/bitnami/dolibarr/htdocs/main.inc.php")) {
		if (@include "/opt/bitnami/dolibarr/htdocs/main.inc.php") {
			$a_debug[] = 'Found & loadded from typical Bitnami location: /opt/bitnami/dolibarr/htdocs/main.inc.php';
			$path = "/opt/bitnami/dolibarr/htdocs/main.inc.php";
		} else {
			$a_debug[] = 'Found on typical Bitnami location but UNABLE TO LAD IT: /opt/bitnami/dolibarr/htdocs/main.inc.php';
		}
	} else {
		$a_debug[] = 'Also does not exist on typical Bitnami location: /opt/bitnami/dolibarr/htdocs/main.inc.php';
	}
}

// 6. last try, with the PATH passed from the <FORM> in this script to let the user indicate the path

if ($path == '' && !empty($_POST['main_inc_php_path'])) {
	$a_debug[] = 'Passed filepath by user: '.$_POST['main_inc_php_path'];
	// check that the proposed file is really main.inc.php and not other malicious file
	if (substr($_POST['main_inc_php_path'], -12) != 'main.inc.php') {
		$a_debug[] = 'Error: this filepath not finish with main.inc.php';
	} elseif (!file_exists($_POST['main_inc_php_path'])) {
		$a_debug[] = 'Error: this file does not exist.';
	} else {
		// prevent a hacker from trying to upload a file submitted by him
		// we check existence of usual Dolibarr directories of core modules (a few it's enough)
		$dir = s(dirname($_POST['main_inc_php_path'])); // ex: ../..
		$a_debug[] = 'Parent directory: '.$dir;
		if (is_dir($dir.$sep.'fourn') && is_dir($dir.$sep.'fourn'.$sep.'facture') && is_dir($dir.$sep.'fourn'.$sep.'facture'.$sep.'tpl')) {
			define('NOCSRFCHECK', 1); // this disable for this unique call the check of the CSRF security token!
			if (@include $_POST['main_inc_php_path']) {
				$path = $_POST['main_inc_php_path'];
				$a_debug[] = 'Fine, the parent directory contain usual Dolibarr core modules directories.';
			}
		} else {
			$a_debug[] = 'Error: the parent directory does not contain usual Dolibarr core modules directories.';
		}
	}
}

// if we accomplished to load main.inc.php file then save it and return
if ($path != '') {
	// check if we can save the filepath on /documents/main_module_inc_php.txt
	// we supose the typical location for this directory: alongside /htdocs
	$__DIR__documents = __DIR__.$sep.s('../../../documents');
	$cache_file__documents = $__DIR__documents.$sep.'main_module_inc_php.txt';

	$error_msg  = "";
	$cache_file = "";
	if (!is_dir($__DIR__documents)) {
		// then we will try to save data on /custom/main_module_inc_php
		// so this in this case we don't show an error to user regarding /documents directory
		$error_msg = "";
		$a_debug[] = "Not found directory 'documents' at: ".$__DIR__documents;
	} elseif (file_exists($cache_file__documents)) {
		if (!is_writable($cache_file__documents)) {
			$error_msg = "ERROR: File { ".$cache_file__documents." } must be writeable. Check permissions.";
			$a_debug[] = $error_msg;
		} else {
			$cache_file = $cache_file__documents;
		}
	} else {
		if (!is_writable($__DIR__documents)) {
			$error_msg = "ERROR: Directory { ".$__DIR__documents." } must be writeable. Check permissions. Or create on it an empty and writeable text file named 'main_module_inc_php.txt'.";
			$a_debug[] = $error_msg;
		} else {
			$cache_file = $cache_file__documents;
		}
	}

	// if not found /documents directory
	if ($cache_file=="" && $error_msg=="") {
		$cache_file = $__DIR__parent.$sep.'main_module_inc_php';
		if (file_exists($cache_file) && !is_writable($cache_file)) {
			$error_msg  = "ERROR: File { ".$cache_file." } must be writeable. Check permissions.";
			$a_debug[]  = $error_msg;
			$cache_file = "";
		} elseif (!file_exists($cache_file) && !is_writable($__DIR__parent)) {
			$error_msg  = "ERROR: Directory { ".$__DIR__parent." } must be writeable. Check permissions. Or create on it an empty and writeable text file named 'main_module_inc_php' (without filename extension!).";
			$a_debug[]  = $error_msg;
			$cache_file = "";
		}
	}

	// if the load was not successful then we empty the path from this file
	if ($cache_file!="") {
		$a_debug[] = "--- Success: { ".$path." } found and path stored at { ".$cache_file." }";
		file_put_contents($cache_file, $path);
		return;
	} else {
		$a_debug[] = "Unable to store the path of 'main.inc.php' on the CACHE FILE.";
		$a_debug[] = $error_msg;
	}
}

// we did not accomplished to load the main.inc.php or to save the path on CACHE FILE
// so we request help to user rendering a form to let she to indicate the PATH

?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="robots" content="noindex,nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body style='font-family:sans-serif;font-size:1.2em;'>
		<form method='post' style='display:block;text-align:center;width:90%;max-width:500px;margin:2em auto;'>
			<h3 style='color:red;'>Dolibarr <u>main.inc.php</u><br />not found in the usual locations</h3>
			<p><br />Please, specify absolute path of that file:</p>
			<p><input type='text' name='main_inc_php_path' style='width:100%;font-size:1.2em;padding:0.5em 0.7em;' placeholder='/var/www/htdocs/main.inc.php'
					  value='<?php echo !empty($_POST['main_inc_php_path']) ? $_POST['main_inc_php_path'] : '' ?>' /></p>
			<?php if ($error_msg!="") { ?>
				<p style='color:red;'><?php echo $error_msg ?></p>
			<?php } elseif (!empty($_POST['main_inc_php_path'])) { ?>
				<p style='color:red;'>This path also failed to load the file <u>main.inc.php</u>.</p>
			<?php } ?>
			<p><br /><button type='submit' style='font-size:1.2em;padding:0.5em 1.5em;cursor:pointer;'>✅ &nbsp; Check this location</button></p>
			<p style='opacity:0.5;font-size:0.9em;'><br />It's normal for the Dolibarr core files location to vary depending on the type of installation.
			Some Dolibarr hosting providers use custom installations, which may place the core in a different path
			than the one you'd encounter if you installed Dolibarr manually.</p>
			<hr style='opacity:0.5;margin:3rem;' />
		</form>
		<!-- SEARCH LOG -->
		<div style='margin:2em auto;'>
			<h3 style='text-align:center;'>Search log</h3>
			<table style='width:auto;margin:2rem auto;'>
				<tr>
					<td style='color:#888;font-size:0.8em;'>
						<ol><li><?php echo implode('</li><li>', $a_debug) ?></li></ol>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>

<?php

die();

/**
 * Get absolute path of the parent directory of the current script
 *
 * @return	string
 */
function get_parent_absolute_path()
{
	$dir_path = s(__DIR__);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $dir_path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}
	array_pop($absolutes);
	if (isset($parts[0]) && substr($parts[0], -1) == ':') {
		// windows C:\xampp\htdocs\...
		return implode(DIRECTORY_SEPARATOR, $absolutes);
	} else {
		// UNIX /var/www/htdocs/...
		return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $absolutes);
	}
}

/**
 * Sanitize file path, because con Windows/XAMPP sometimes are used / and sometimes \ as directory separator
 *
 * @param	String	$path	File/dir path
 * @return	String			Sanitized path, using DIRECTORY_SEPARATOR (OS constant)
 */
function s($path)
{
	return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
}

/**
 * Print on screen the content of a variable, string or array (only for debugging purposes)
 *
 * @param	String	$content	String or Array
 * @param	String	$title		Optional title
 * @return	String				Well formed HTML
 */
function p($content, $title = '')
{
	if (!empty($title)) print "<h3>$title</h3>";
	if (is_array($content)) {
		print "<pre>";var_dump($content); print "</pre>";
	} else {
		print "<p>$content</p>";
	}
}
