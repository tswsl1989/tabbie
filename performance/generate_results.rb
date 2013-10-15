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
  res = dbh.query("SELECT param_value FROM settings WHERE param_name='round'")
  current_draw_round = res.fetch_hash['param_value']
  res.free 
  
  res = dbh.query("SELECT MAX(round_no) as param_value FROM results")
  current_result_round = (res.fetch_hash['param_value'].to_i) 
  res.free
  
  puts "Draw: #{current_draw_round}    Round: #{current_result_round}"

  unless current_result_round == current_draw_round
      # Fixed points tally
      points = [90,
                74,
                65,
                56]
      # Select debates
      res = dbh.query("SELECT * from draws WHERE round_no = #{current_draw_round}")
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
      
      # Write data into tables
      team_results.each do |result_entry|
          dbh.query(<<-SQL
          INSERT INTO results 
          VALUES (
          #{current_draw_round}, #{result_entry.inject('') { |sum,i| sum+= "#{i},"}.chomp(',')}
          )
          SQL
          )
      end
      
      speaker_points.each do |speaker_entry|
           dbh.query(<<-SQL
           INSERT INTO speaker_results 
           VALUES (
           #{current_draw_round}, #{speaker_entry.inject('') { |sum,i| sum+= "#{i},"}.chomp(',')}
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
