<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "{$tournament->name} - Add Building" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Add Building</h1>

<?= $this->validation->error_string; ?>

<?= form_open("building/add/{$tournament->short_name}") ?>

<input type="text" name="name" value="<?= $this->validation->name ?>" size="16" /> Name <br/>

<div><input type="submit" value="Add" /></div>

</form>

<?php require APPPATH . "views/footer.php"; ?>