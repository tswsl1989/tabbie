Copied from http://tabbie.wikidot.com/dummy-tournament

++ Running a Dummy Tournament

+++ Introduction

It may be required to run a dummy tournament using Tabbie. This could be for reasons of performance testing, checking for reliability/errors or just getting of how things would work in Tabbie, without needing to enter a large set of inputs and results manually.

I have developed a couple of quick scripts to enable population of data, and random generation of results to ease the manual task of entering data manually, especially for large datasets of upto 100 teams.

+++ Scripts
The scripts are in the {{performance}} folder of the [http://tabbie.svn.sourceforge.net/viewvc/tabbie/trunk/performance Tabbie SVN Repository].

You need the following prerequisites to using the script
* A working installation of Tabbie.
* A working installation of Ruby, along with [http://rubyforge.org/projects/rubygems/ RubyGems]. This can be on any computer which can access the MySQL database that Tabbie is using. So, you could have Tabbie running on a Windows machine, and use a Linux machine to run the scripts and connect to the Tabbie database.
* Ruby-MySQL bindings, installed by using the command {{gem install mysql}}

I have tested the scripts on an Ubuntu Gustsy Gibbon VM. All instructions SHOULD work on windows since there are no other dependencies apart from Ruby and RubyGems

+++ Preparation
The only thing you need to do before running the scripts is to configure the database settings. Both the scripts have a line on top which looks like this

[[code type="ruby"]]
  # connect to the MySQL server
  dbh = Mysql.real_connect("localhost", "root", "", "tabbie")
[[/code]]

Modify this line to specify the host, username, password and the name of the db (in that order) to connect to. As mentioned before, the DB could be located on a different machine, as long as it can be remotely accessed.

+++ Using the scripts
There are two scripts that can be used:

* {{populate_dataset.rb}} : This script will populate the initial dataset of 100 venues, 30 universities, 400 teams and 300 adjudicators
* {{generate_results.rb}} : Use this script immediately after you have generated a draw. This script will automatically look for the latest draw and populate the results randomly so that you can save the manual effort of doing so.

+++ Future Enchancements
Here are some planned enhancements:
* Configurable parameteres for number of venues, universities, teams and adjudicators 
* Better Error Checking
* Taking into account situations where some results have already been entered for some debated. The script should leave those results untouched and randomly generate results for only the debates for which results have not been entered yet.

+++ Feedback and Queries
Mail me (Deepak Jois) at deepak.jois@gmail.com, or join the [http://groups.google.com/group/tabbie-devel/ Tabbie Development Google Group] and post your queries there.