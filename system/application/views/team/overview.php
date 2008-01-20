<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Building Overview" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Building Overview</h1>

<? foreach($buildings as $building): ?>
<?= $building->name ?> <br/>
<? endforeach; ?>

<div><a href="<?= site_url("building/add/{$tournament->short_name}") ?>">Add Building</div>

<?php require APPPATH . "views/footer.php"; ?>