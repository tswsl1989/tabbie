<? $page_title = "Create Tournament" ?>

<?php require APPPATH . "views/header.php"; ?>

<h1>Create Tournament</h1>

<?= $this->validation->error_string; ?>

<?= form_open('tournament/create') ?>

<input type="text" name="short_name" value="" size="16" /> Identifier (used in addresses)<br/>
<input type="text" name="name" value="" size="16" /> Name (for display purposes)<br/>

<div><input type="submit" value="Create" /></div>

</form>

<?php require APPPATH . "views/footer.php"; ?>