<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "Club Overview" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Club Overview</h1>

<? foreach($clubs as $club): ?>
<?= $club->short_name ?> <?= $club->name ?><br/>
<? endforeach; ?>

<div><a href="<?= site_url("club/add/") ?>">Add Club</div>

<?php require APPPATH . "views/footer.php"; ?>