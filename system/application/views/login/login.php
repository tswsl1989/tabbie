<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<? $page_title = "Login" ?>
<?php require APPPATH . "views/header.php"; ?>

<h1>Login to Tabbie 2.0</h1>

<form action="<?= site_url("login/go") ?>" method="post">

<input type="text" name="username"/> Username<br/>
<input type="password" name="password"/> Password<br/>
<input type="submit"/><br/>
</form>

<?php require APPPATH . "views/footer.php"; ?>