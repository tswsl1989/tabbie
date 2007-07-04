<?php require("templates/header.inc");?>
<h3>Tabbie Installation Guide</h3>

<h3>Getting Started</h3>
<div>
First of all - don't worry. Even though this page may seem a bit overwhelming, it really shouldn't take you more than 10 minutes to get Tabbie started. It's really been designed to run straight out of the box.
</div>

<div>
If you use some kind of Unix/Linux we'll expect you to know what to do on your particular system. You can skip straight to <a href="#requirements">the requirements</a>.
</div>

<div>
If you use windows and already have a webserver, you can <a href="#step2">skip step 1</a>. If you use Windows, and have no idea what a web server is, read the whole guide. If you run into problems that you cannot solve by yourself and the nerds you have befriended, go to the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project Page</a> and ask for support there.
</div>

<h3>Step 1: Install a Web Server</h3>
<div>
Tabbie is Web Based Software, which is a really fancy way of saying that it's a bunch of web pages. These pages are written in a language called PHP and a mysql database is used to store data. Unlike regular HTML pages, PHP pages may execute commands and can therefor be used as an interactive interface.
</div>
 
<div>
To make the PHP pages actually do show up as something else than a bunch of boring text pages, we need a Web Server to run them on. If you already have a webserver running you can skip this step.
</div>
 
<div>
If not - no worries - webservers come nicely packaged these days. One example of such a server which runs under Windows is EasyPHP, which can be download at <a href="http://www.easyphp.org/">www.easyphp.org</a>. If you don't feel like browsing their site you can also get <a href="http://www.easyphp.org/telechargements/dn.php?F=easyphp1-8">version 1.8 (7.7 MB)</a> directly.
</div>

<div>
Now - run the installer and install it in some location of your liking. Basically, just press OK many times. In the following, we will assume you have chosen C:\Program&nbsp;Files\EasyPHP1-8 as the root location of EasyPHP. It's fine to install elsewhere, just make sure you replace C:\Program&nbsp;Files\EasyPHP1-8 by the correct location.
</div>
 
<div>
If you've finished this step correctly you should be able to go to <a href="http://localhost/" target="_blank">http://localhost/</a> and see EasyPHP's opening page.
</div>
 
<h3><a name="step2"></a>Step 2: Download Tabbie</h3>
<div>Download the latest version of Tabbie on <a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Sourceforge</a>. The highest stable release in a file format that seems familiar should be just fine.</div>
 
<h3>Step 3: Unpack Tabbie</h3>
Use your favourite tool to extract the contents of the downloaded Tabbie file to C:\Program&nbsp;Files\EasyPHP1-8\www. If you did this correctly you should see a directory "tabbie" in C:\Program&nbsp;Files\EasyPHP1-8\www. Also, opening <a href="http://localhost/tabbie/" target="_blank">http://localhost/tabbie/</a> should bring up a site remarkably similar to this one - this is your live Installation. (Go there to start using Tabbie)

<h3>Step 4: Install Tabbie</h3>
<div>
On your installed version of Tabbie click on the "Run" option, (if you closed the window: <a href="http://localhost/tabbie/run/" target="_blank">http://localhost/tabbie/run/</a>. You will get a bunch of options - if you don't know what these mean, just click on the install button. By this time, your screen should be Filled with succesful database statements, followed by a big "Installation Succesful" remark.
</div>

<h3>Step 5: Security</h3>
<div>
Tabbie itself does no attempt to restrict certain people from seeing what's going on. This means you'll have to configure your computer and network so that no-one else can edit. If you have no idea how to do this - this is really the moment to get help from an expert/nerd in your personal environment. She will also be able to tell you how you can allow others to access Tabbie on your computer.
</div>

<h3>Step 6: Start inputting data</h3>
<div>
This is really where the install ends....and using Tabbie begins. Should you have any problems, please visit the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project Page</a> and ask for support there.
</div>

<h3><a name="requirements"></a>System Requirements</h3>
<div>
Tabbie requires PHP 4 and MySql 4.1. Unix/Linux system administrators may want to run the script run/setpermissions.sh or inspect it to see which file permissions to set.
</div>
<?php require("templates/footer.inc"); ?>