Tabbie
======

Tabbie is a web based tabbing system for British Parliamentary style debating tournaments of varying sizes. This version of tabbie is one of a number based on the last publicly available code on [Sourceforge](http://sourceforge.net/projects/tabbie/) and is maintained by Tom Lake, based at Swansea University.

Requirements
------------

 * PHP 5+ - currently tested on 5.4 and 5.5
 * MySQL 5+ (or a compatible fork) - currently tested on MariaDB and MySQL 5.5
 * ADODB - a PHP database library
 * A web server compatible with PHP
 
This version of tabbie is generally tested against PHP 5.5 and 5.4, MySQL and MariaDB 5.5 on web servers running Apache 2.2 and Apache 2.4

Installation
------------

 * Copy tabbie to a location available to your web server
 * Ensure that the web server is able to write to the config/ directory
 * Create a blank database and a new database user
 * Open tabbie in your web browser and provide the information requested by the install script

**Note:** This version of Tabbie does not currently perform any authentication. If you are placing tabbie on a publically accessible server, ensure that access is restricted. Otherwise any debater, judge or member of the public who learns the address can modify the tab at will.

Versions
--------

The master branch of the [GitHub repository](https://github.com/tswsl1989/tabbie/) contains the latest code. Older, tested versions are available by checking out the appropriate tag or from the [Releases](https://github.com/tswsl1989/tabbie/releases "Releases") page on GitHub. These versions are dated, and are typically tagged based on the competition they were tested at and will include any patches applied during that tournament.

The last public release and latest code taken from SourceForge are also available, tagged "v1.5" and "trunk".

License
-------
Tabbie is released under Version 2 of the GNU General Public License. See the LICENSE file for further details.

This version also includes code from other projects:

 - PHPPowerPoint (LGPL v2.1)
 - FPDF (Freeware - "You may use, modify and redistribute this softare as you wish")
 - jQuery, jQuery UI and various jQuery plugins/libraries (See files for details)

Contributors
------------

The contributors listed on SourceForge or in the version history for this project are:

 * Klaas van Schelven
 * Deepak Jois
 * Ben Walker
 * Giles Robertson
 * Meir Maor
 * Joshua Lo
 
Apologies if anyone has been missed!
