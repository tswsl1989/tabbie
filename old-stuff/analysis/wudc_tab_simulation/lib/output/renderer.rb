require 'fileutils'
require 'erb'
class Renderer
    def initialize(root)
        @round_template = ERB.new(File.read(File.join(root,"templates","display.erb")))
        @index_template = ERB.new(File.read(File.join(root,"templates","index.erb")))        
        @base_folder = File.join(root, "output")
        Dir.mkdir(@base_folder) unless File.exists?(@base_folder)
        @base_folder = File.join(@base_folder, "#{Time.now.strftime("%d_%b_%Y-%H%M%S")}") # Timestamp
        Dir.mkdir(@base_folder)
        %w(jquery.js styles.css).each do |file|
            FileUtils.copy_file "#{root}/templates/#{file}", "#{@base_folder}/#{file}" 
        end
    end
    
    def render_round_template(round,debates, results, teams_hash)
        @round = round
        @debates = debates
        @results = results
        @teams = teams_hash.keys.sort_by { |k| teams_hash[k].points }.reverse.collect { |t| teams_hash[t] }
        b = binding
        html = @round_template.result(b)
        File.open(File.join(@base_folder,"round-#{@round}.html"), 'w') do |f|
           f.write(html)
           f.close
        end
    end
    
    def render_index_template(num_rounds, draw_algorithm, simulation_algorithm)
        @num_rounds = num_rounds
        @draw_algorithm = draw_algorithm.gsub(/_id$/, "").humanize
        @simulation_algorithm = simulation_algorithm.humanize
        b = binding
        html = @index_template.result(b)
        File.open(File.join(@base_folder,"index.html"), 'w') do |f|
           f.write(html)
           f.close
        end        
    end
end