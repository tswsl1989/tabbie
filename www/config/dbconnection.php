<?
/******************************************************************************
File    :   dbconnection.php

Author  :   Deepak Jois

Purpose :   Handle database connection


CHANGELOG
June 30th 2003
- Started work on the module

******************************************************************************/

//Make connection to database
mysql_connect("localhost", "root", "");
mysql_select_db("worlds0");

?>
