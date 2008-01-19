<? $page_title = "Home" ?>

<?php require APPPATH . "views/header.php"; ?>

<h1>Welcome to Tabbie 2.0</h1>

<p><a href="<?= site_url("register") ?>">Register</a> or <a href="<?= site_url("login") ?>">Log in</a></p>

<p>Logged in as [<?= $this->session->userdata('username') ?>] <a href="<?= site_url("logout") ?>">Log Out</a></p>

<?php if ($this->session->userdata('username')): ?>

<p><a href="<?= site_url("tournament/create") ?>">Create Tournament</a></p>

<?php endif; ?> 

<?php require APPPATH . "views/footer.php"; ?>