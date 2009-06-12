<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><?= $title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="<?= @$dir_hack ?>view/index.css">
<?php
//avoid loading expensive script files if we don't need them
$ajaxpath = 'ajax/'.$ntu_controller.'/'.$moduletype.'.js';
if(file_exists($ajaxpath)){ ?>
	<script type="text/JavaScript">
	var ntu_controller = "<?= $ntu_controller ?>";
	var moduletype = "<?= $moduletype ?>";
	</script>
	<script type="text/JavaScript" src="<?= @$dir_hack ?>js/jquery-1.3.2.min.js"></script>
    <script type="text/JavaScript" src="<?= @$dir_hack ?>js/jquery-ui-1.7.1.custom.min.js"></script>
	<script type="text/JavaScript" src="<?= $ajaxpath ?>"></script>
<?php } ?>
<body>
<div id='content'>

