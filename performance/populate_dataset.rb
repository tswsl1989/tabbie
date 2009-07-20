#!/usr/bin/ruby -w

require 'rubygems'
require "mysql"

begin
  # connect to the MySQL server
  dbh = Mysql.real_connect("localhost", "root", "", "tabbie")
  # get server version string and display it
  puts "Server version: " + dbh.get_server_info
  
  # Create database (Drop if already exists)
  dbh.query("DROP DATABASE IF EXISTS tabbie")
  dbh.query("CREATE DATABASE tabbie")
  dbh.query("USE tabbie")
  
  # Load initial schema
  sql_init = File.read("../install/create_db.sql")
  sql_init.split(";").each do |q|
      dbh.query(q.strip) unless q.strip.length == 0
  end
  
  # Populate Venues
  puts "Generating Venues"
  100.times do |i|
      dbh.query(<<-SQL
        INSERT INTO venue(venue_name,venue_location,active) VALUES('LT#{i}', 'LT#{i}', 'Y')
      SQL
      )
  end
  
  # Populate Universities
  alphabets = ('A'..'Z').to_a
  f = File.open("university_code.txt")
  codes = f.readlines.collect { |c| c.strip }
  univs = []
  puts "Generating Universties"
  
  60.times do |i|
      # Generate Code
      # code = (1..3).to_a.inject("") { |sum,d| sum+= alphabets[rand(26)] } until code and !univs.member?(code)
      
      # Read code
      code = codes[i]
      univs << code
      dbh.query(<<-SQL
        INSERT INTO university(univ_name, univ_code) VALUES('#{code.capitalize}', '#{code}')
      SQL
      )
  end
  
  # Open names file
  f = File.open("names_random.txt")
  names = f.readlines.collect { |c| c.strip }
  # Populate Teams and Speakers  
  puts "Generating Teams and Speakers"
  240.times do |i|
     # Create Team
     univid=(i/4)+1
     eslchance=(1+rand(100))
     esl = 'EFL'
     sp1rls = 'EFL'
     if eslchance < 93
       esl = 'ESL'
       sp1rls = 'ESL'
     end
     if eslchance < 70
       esl = 'N'
       sp1rls = 'N'
     end
     dbh.query(<<-SQL
        INSERT INTO team(univ_id, team_code, active, composite, esl) VALUES(#{(i/4) + 1}, '#{alphabets[i % 4]}', 'Y', 'N', '#{esl}')
     SQL
     )
     3.times do |k|
       dbh.query(<<-SQL
           INSERT INTO strikes (adjud_id, univ_id, team_id) VALUES(#{1+rand(200)}, #{(i/4)+1}, #{i})
       SQL
       )
     end
     # Create 2 Speakers 
     2.times do |j|
         #first_name = (1..10).to_a.inject("") { |sum,d| sum+= alphabets[rand(26)] } 
         #last_name =  (1..10).to_a.inject("") { |sum,d| sum+= alphabets[rand(26)] } 
         #name = '#{first_name} #{last_name}'
         name = names[j*400 + i]
         dbh.query(<<-SQL
            INSERT INTO speaker(team_id, speaker_name, speaker_esl) VALUES(#{i+1}, '#{name}', '#{sp1rls}')
         SQL
         )
     end
  end
  
  # Populate Adjudicators
  puts "Generating Adjudicators"
  200.times do |i|
      #first_name = (1..10).to_a.inject("") { |sum,d| sum+= alphabets[rand(26)] } 
      #last_name =  (1..10).to_a.inject("") { |sum,d| sum+= alphabets[rand(26)] } 
      #name = '#{first_name} #{last_name}'
      name = names[800 + i]
      dbh.query(<<-SQL
            INSERT INTO adjudicator(univ_id, adjud_name, ranking, active) VALUES(#{(i % 60) + 1}, '#{name}', #{(1+rand(25))*4}, 'Y')
      SQL
      )
      5.times do |j|
        dbh.query(<<-SQL
            INSERT INTO strikes (adjud_id, univ_id) VALUES(#{i}, #{(1+rand(60))})
        SQL
        )
      end
  end
  
  
  
  
rescue Mysql::Error => e
  puts "Error code: #{e.errno}"
  puts "Error message: #{e.error}"
  puts "Error SQLSTATE: #{e.sqlstate}" if e.respond_to?("sqlstate")
ensure
  # disconnect from server
  dbh.close if dbh
end

