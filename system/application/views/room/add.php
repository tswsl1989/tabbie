<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Add Room" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Add Room</h1>

<?= $this->validation->error_string; ?>

<?= form_open("room/add/{$tournament->short_name}") ?>

<input type="text" name="name" value="<?= $this->validation->name ?>" size="16" /> Name <br/>

<div><input type="submit" value="Add" /></div>

</form>

<?php require APPPATH . "views/footer.php"; ?>