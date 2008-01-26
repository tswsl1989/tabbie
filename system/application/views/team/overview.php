<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Team Overview" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1><?= $page_title ?></h1>

<? foreach($teams as $team): ?>
<?= $team->club_short_name . " " . $team->team_short_name ?>
    <? foreach($team->speakers as $speaker): ?>
        <?= $speaker->name ?>
    <? endforeach; ?>
    <br/>
<? endforeach; ?>

<div><a href="<?= site_url("team/add/{$tournament->short_name}") ?>">Add Team</div>

<?php require APPPATH . "views/footer.php"; ?>