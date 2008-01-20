<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Room Overview" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Room Overview</h1>

<? foreach($rooms as $room): ?>
<?= $room->building_name ?> <?= $room->name ?><br/>
<? endforeach; ?>

<div><a href="<?= site_url("room/add/{$tournament->short_name}") ?>">Add Room</div>

<?php require APPPATH . "views/footer.php"; ?>