#!/usr/bin/ruby -w

require 'rubygems'
require "mysql"

begin
  # connect to the MySQL server
  dbh = Mysql.real_connect("localhost", "root", "", "tabbie")
    
  # Check for any temp tables
  res = dbh.query("SHOW TABLES LIKE 'temp_%'")
  puts "No. of temporary tables : #{res.num_rows}"
  res.free
  
  # Check for tables starting with draw_round and find the next round
  res = dbh.query("SHOW TABLES LIKE 'draw_round_%'")
  current_draw_round = res.num_rows
  res.free 
  
  res = dbh.query("SHOW TABLES LIKE 'result_round_%'")
  current_result_round = res.num_rows
  res.free
  

  unless current_result_round == current_draw_round
      # Fixed points tally
      points = [90,
                74,
                65,
                56]
      # Select debates
      res = dbh.query("SELECT * from draw_round_#{current_draw_round}")
      team_results = []
      speaker_points = []
      while row = res.fetch_hash do
          # Determine result
          team_ids = [row['og'],
                      row['oo'],
                      row['cg'],
                      row['co']].sort_by { rand }
          # Insert team res
          team_results << [row['debate_id'], team_ids].flatten
          
          # Find speakers for each team
          team_ids.each_with_index do |team_id, idx|
              res1 = dbh.query("SELECT * from speaker WHERE team_id=#{team_id}")
              while row1 = res1.fetch_hash do
                  # Insert speaker points
                  speaker_points << [row1['speaker_id'], row['debate_id'], points[idx]]
              end
          end
      end
      
      puts "No. of teams #{team_results.size}"
      puts "No. of speakers #{speaker_points.size}"
      
      # Create tables (DROP if any)
      dbh.query("DROP TABLE IF EXISTS temp_result_round_#{current_draw_round}")
      dbh.query("DROP TABLE IF EXISTS temp_speaker_round_#{current_draw_round}")
      # Team results table
      dbh.query(<<-SQL
      CREATE TABLE `result_round_#{current_draw_round}` (
        `debate_id` mediumint(9) NOT NULL default '0',
        `first` mediumint(9) NOT NULL default '0',
        `second` mediumint(9) NOT NULL default '0',
        `third` mediumint(9) NOT NULL default '0',
        `fourth` mediumint(9) NOT NULL default '0',
        PRIMARY KEY  (`debate_id`))
      SQL
     )
     
     # Speaker points table
     dbh.query(<<-SQL
     CREATE TABLE `speaker_round_#{current_draw_round}` (
       `speaker_id` mediumint(9) NOT NULL default '0',
       `debate_id` mediumint(9) NOT NULL default '0',
       `points` smallint(9) NOT NULL default '0',
       PRIMARY KEY  (`speaker_id`))
     SQL
     )
      
      # Write data into tables
      team_results.each do |result_entry|
          dbh.query(<<-SQL
          INSERT INTO result_round_#{current_draw_round} 
          VALUES (
          #{result_entry.inject('') { |sum,i| sum+= "#{i},"}.chomp(',')}
          )
          SQL
          )
      end
      
      speaker_points.each do |speaker_entry|
           dbh.query(<<-SQL
           INSERT INTO speaker_round_#{current_draw_round}
           VALUES (
           #{speaker_entry.inject('') { |sum,i| sum+= "#{i},"}.chomp(',')}
           )
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