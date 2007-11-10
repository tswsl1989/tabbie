# Monkey patching the core ruby classes for some convenience functionality

module Enumerable
    def group_by
        inject({}) do |groups, element|
            (groups[yield(element)] ||= []) << element
            groups
        end
    end
end

class Array
    def in_groups_of(number, fill_with = nil, &block)
      require 'enumerator'
      collection = dup
      collection << fill_with until collection.size.modulo(number).zero? unless fill_with == false
      grouped_collection = [] unless block_given?
      collection.each_slice(number) do |group|
        block_given? ? yield(group) : grouped_collection << group
      end
      grouped_collection unless block_given?
    end
end

class String
    def camelize
        self.gsub(/\/(.?)/) { "::" + $1.upcase }.gsub(/(^|_)(.)/) { $2.upcase }
    end
    
    def constantize
      unless /\A(?:::)?([A-Z]\w*(?:::[A-Z]\w*)*)\z/ =~ self
        raise NameError, "#{camel_cased_word.inspect} is not a valid constant name!"
      end
      Object.module_eval("::#{$1}", __FILE__, __LINE__)
    end
    
    def humanize
        self.gsub(/_id$/, "").gsub(/_/, " ").capitalize
    end
end
