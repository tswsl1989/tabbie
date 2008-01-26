<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "Tournament Overview" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1><?= $page_title?></h1>

<? foreach($tournaments as $tournament): ?>
<?= $tournament->short_name ?> <?= $tournament->name ?><br/>
<? endforeach; ?>

<div><a href="<?= site_url("tournament/add/") ?>">Add Tournament</div>

<?php require APPPATH . "views/footer.php"; ?>