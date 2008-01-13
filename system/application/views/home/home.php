<html>
<head>
<title>Welcome to Tabbie 2.0</title>
</head>
<body>

<h1>Welcome to Tabbie 2.0</h1>

<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

<p><a href="<?= site_url("register") ?>">Register</a> or <a href="<?= site_url("login") ?>">Log in</a></p>

<p>Logged in as [<?= $this->session->userdata('username') ?>] <a href="<?= site_url("logout") ?>">Log Out</a></p>

</body>
</html>