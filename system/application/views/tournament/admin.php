<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Admin" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Tournament Admin - <?= $tournament->name ?></h1>

<a href="<?= site_url("building/overview/{$tournament->short_name}") ?>"> Building overview</a><br/>
<a href="<?= site_url("room/overview/{$tournament->short_name}") ?>"> Room overview</a><br/>

<?php require APPPATH . "views/footer.php"; ?>