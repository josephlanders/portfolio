<?php

require_once("doc_class.php");
require_once "lexicon_file_manager.php";
require_once("invlists_block_memory_cache_simple_array.php");
//require_once("invlists_block_memory_cache2.php");
require_once "configuration.php";
require_once("invlists_file_manager.php");

class invlists_manager {

    private $invlists_handle = null;
    public $invlists_filename = "invlists";
    public $lexicon_filename = "lexicon";
    public $integer_length = 4; // 2 4 8
    public $integer_keyword = "L"; // S = 2, L = 4, Q = 8
    public $maximum_fixed_block_size = null;
    //public $padded_block_size = 3000;
    public $use_buffering = false;
    public $lexicon_file_manager = null;
    public $use_variable_length_disk_blocks = false;
    public $invlists_block_memory_cache = null;
    private $configuration = null;
    public $invlists_file_manager = null;
    public $next_block_location = 0;

    public function __construct($arr) {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }

        $this->invlists_block_memory_cache = new invlists_memory_block_cache_simple_array(array("configuration" => $this->configuration));

        $this -> invlists_file_manager = new invlists_file_manager(array("configuration" => $this->configuration, 
            "invlists_filename" => $this->invlists_filename, 
            "lexicon_filename" => $this->lexicon_filename,
            "lexicon_file_manager" => $this->lexicon_file_manager,
            "configuration" => $this->configuration));
        
        $config = $this->configuration;
        $config_array = $config->get_config_array();
        foreach ($config_array as $conf_key => $conf_val) {
            $this->$conf_key = $conf_val;
        }
        

    }

    public function initialise_invlists_file() {
        $data = pack($this->integer_keyword, $this->integer_length);
        file_put_contents($this->invlists_filename, $data);        
    }

    public function create_blank_data_block_struct($pad_block, $padded_block_size) {
        $string = "";

        $num_occurances_in_all_docs = pack($this->integer_keyword, 0); // 32 bit pack?
        $length_of_inverted_list = pack($this->integer_keyword, 0); // 32 bit pack?

        $padding = "";
        if ($pad_block == true) {
            $len = $padded_block_size - (strlen($num_occurances_in_all_docs) + strlen($length_of_inverted_list));
            $padding = str_pad($string, $len, chr(0));
        }

        // Assume Binary Safe
        $blank_data_block = $num_occurances_in_all_docs . $length_of_inverted_list . $padding;

        $size_of_block = strlen($blank_data_block);

        return array($size_of_block, $blank_data_block);
    }

    public function update_next_block_location_in_memory($next_block_location, $size_of_block) {
        $next_block_location += $size_of_block;

        return $next_block_location;
    }

    public function update_unique_occurances_for_block($num_occurances_in_invlist, $incrementer) {

        $num_occurances_in_invlist += $incrementer;

        return $num_occurances_in_invlist;
    }

    public function update_inverted_index_occurances_per_document_using_string_as_append(&$data_block_class, $length_of_inv_list, $occurances_per_document_string) {
        $inverted_list = $data_block_class->inverted_list;

        $write_length = strlen($occurances_per_document_string);
        //$new_length_of_inv_list = $length_of_inv_list + $write_length;

        $inverted_list .= $occurances_per_document_string;
        $new_length_of_inv_list = strlen($inverted_list);

        $data_block_class->inverted_list = $inverted_list;

        return array($write_length, $new_length_of_inv_list);
    }
    
    // Unused / untested code
    public function update_inverted_index_occurances_per_document_array_as_append(&$data_block_class, $length_of_inv_list, $occurances_per_document_array) {
        $inverted_list = $data_block_class->inverted_list;
        // 8 bytes 
        // Jump to the end of the inverted list where we can safely append
        $end_of_inv_list = $length_of_inv_list;

        
        $write_text = "";
        
        foreach($occurances_per_document_array as $key => $value)
        {
            $write_text .= pack($integer_keyword, $key);
            $write_text .= pack($integer_keyword, $value);
        }
        $debug_text = "";

        $write_length = strlen($write_text);
        $new_length_of_inv_list = $length_of_inv_list + $write_length;

        $data .= $write_text;

        $data_block_class->inverted_list = $inverted_list;

        return array($write_length, $new_length_of_inv_list);
    }

    public function write_data_block_from_cache_to_file($invlists_handle, $lexicon_item) {
        $this->invlists_block_memory_cache->write_data_block_from_cache_to_file($invlists_handle, $lexicon_item);
    }

    public function create_invlist_block_in_memory_cache_simple_array($term, $padding, $padded_fixed_block_size) {

        list($size_of_block, $blank_data_block) = $this->create_blank_data_block_struct($padding, $padded_fixed_block_size);
        
        
        $data_block_class = $this->invlists_block_memory_cache->allocate_new_cache_entry($term, $size_of_block, $size_of_block);

        $next_block_location = $this->get_next_block_location();

        $data_block_class->file_offset = $next_block_location;

        $disk_block_size = $size_of_block;

        // Block is already packed
        $data_block_class->header = $blank_data_block;
        $data_block_class->disk_block_size = $disk_block_size;
        $data_block_class->content_size = $disk_block_size;

        $this->invlists_block_memory_cache->update_cache($term, $data_block_class);
               
        $content_size = $size_of_block;
        if ($this->use_variable_length_disk_blocks == true) {
            $data_block_class->content_size = $content_size;
            $data_block_class->disk_block_size = $content_size;
            $data_block_class->memory_block_size = $content_size;
        } else {
            $data_block_class->content_size = $content_size;
        }
        
        // Data size is 0
        return array($size_of_block, $content_size);
    }

    public function extract_header_efficient($data_block_class) {
        $header_bytes = $data_block_class->header;
        //$length_of_header = $this->integer_keyword * 2;

        $header_string_unpacked_array = unpack($this->integer_keyword . "2", $header_bytes);
        $header = array();
        $header["num_unique_occurances"] = $header_string_unpacked_array[1];
        $header["inverted_list_length"] = $header_string_unpacked_array[2];
        
        return $header;
    }

    public function write_header_efficient(&$data_block_class, $header) {
        $num_unique_occurances = pack($this->integer_keyword, $header["num_unique_occurances"]);
        $inverted_list_length = pack($this->integer_keyword, $header["inverted_list_length"]);
        $header_packed = $num_unique_occurances . $inverted_list_length;
        
        $data_block_class->header = $header_packed;
        return true;
    }

    public function update_invlist_block_in_memory_cache_simple_array($lexicon_item, $term_statistics) {

        $data_block_class = $this->invlists_block_memory_cache->get_data_block_from_cache($lexicon_item->term);

        $incrementer = $term_statistics->number_of_unique_documents_occurs_in;

        $header = $this->extract_header_efficient($data_block_class);

        $num_unique_occurances = $header["num_unique_occurances"];

        $num_unique_occurances_updated = $num_unique_occurances + $incrementer;

        $header["num_unique_occurances"] = $num_unique_occurances_updated;

        $length_of_inv_list = $header["inverted_list_length"];

        $occurances_per_document_string = $term_statistics->occurances_per_document_string;

        list($write_length_occurances_per_document_appended_array, $new_length_of_inv_list) = $this->update_inverted_index_occurances_per_document_using_string_as_append($data_block_class, $length_of_inv_list, $occurances_per_document_string);
                
        $header["inverted_list_length"] = $new_length_of_inv_list;
        
        $this ->write_header_efficient($data_block_class, $header);                

        $content_size = $this->integer_length + $this->integer_length + $new_length_of_inv_list;

        if ($this->use_variable_length_disk_blocks == true) {
            $data_block_class->content_size = $content_size;
            $data_block_class->disk_block_size = $content_size;
            $data_block_class->memory_block_size = $content_size;
        } else {
            $data_block_class->content_size = $content_size;
            $data_block_class->memory_block_size = $content_size;
        }

        if ($data_block_class->term == "headline") {
            //$header = $data_block_class -> header;
            //$inverted_list = $data_block_class -> inverted_list;
            //$debug_var = $this ->explode_block_data($header, $inverted_list);
        }

        $this->invlists_block_memory_cache->update_cache($lexicon_item->term, $data_block_class);


        return $content_size;
    }

    public function create_invlist_block($term, $padding, $padded_fixed_block_size) {
        // Create first block after the first 
        // Read next block pointer from file

        $next_block_location = $this->invlists_file_manager->get_next_block_location_from_disk();

        list($size_of_block, $blank_data_block) = $this->create_blank_data_block_struct($padding, $padded_fixed_block_size);

        $disk_block_size = $size_of_block;
        
        $this->invlists_file_manager->write_out_blank_block_to_disk($next_block_location, $blank_data_block, $disk_block_size);

        $size_of_content = $size_of_block;

        return array($next_block_location, $size_of_block, $size_of_content);
    }

    
    
    public function explode_block_data($header, $inverted_list)
    {
        $term_statistics = new term_statistics("debug variable");
        
        $header_unpacked = unpack($this->integer_keyword . "*", $header);
        $num_unique_occurances = $header_unpacked[1];
        $length_of_inv_list = $header_unpacked[2];

        $inverted_list_unpacked = unpack($this->integer_keyword . "*", $inverted_list);
        $occurances_per_document_array = array();

        $expected_array_size = $num_unique_occurances * 2;

        for ($i = 1; $i < 1 + $expected_array_size; $i = $i + 2) {

            $key = $inverted_list_unpacked[$i];

            $value = $inverted_list_unpacked[$i + 1];
            $occurances_per_document_array["$key"] = $value;
        }
       
        $term_statistics->number_of_unique_documents_occurs_in = $num_unique_occurances;

        $term_statistics->occurances_per_document_array = $occurances_per_document_array;
        
        $term_statistics -> length_of_inv_list = $length_of_inv_list;

 

        return $term_statistics;
    }
    
    // TODO: Use memory 
    public function get_next_block_location()
    {
        return $this -> next_block_location;
    }

    public function get_inverted_index_from_disk($lexicon_item)
    {
        return $this -> invlists_file_manager -> get_inverted_index_from_disk($lexicon_item);
    }

    public function write_buffer_simple_array($lexicon_array) {       
        $write_buffer_time = 0;
        $write_buffer_start_time = microtime(true);

        $current_offset = 4;
        if ($this -> verbose == true)
        {
           echo "re-indexing buffer\n";
        }
        
        $lexicons_processed = 0;
        $lexicon_size = count($lexicon_array);
        // Correct lexicon/invlists offsets
        foreach ($lexicon_array as $key => $lexicon_item) {
            $lexicon_item->file_offset = $current_offset;
            // Increment offset
            $disk_block_size = $lexicon_item->disk_block_size;
            $current_offset = $current_offset + $disk_block_size;
            $this->lexicon_file_manager->update_lexicon_item($lexicon_item);
            
            $lexicons_processed++;
            if ($lexicons_processed % 50 == 0)
            {
                if ($this -> verbose == true)
                {
                    echo "lexicons processed " . $lexicons_processed . " of " . $lexicon_size . "\n";
                }
            }
        }


        //var_dump($lexicon_array);
        //echo "writing buffer\n";
        $data2 = "";
        $lex_total = 0;
        
        if ($this -> verbose == true)
        {
            echo "collating text from blocks\n";
        }

        $blocks_processed = 0;
        $lexicon_size = count($lexicon_array);
        foreach ($lexicon_array as $key => $lexicon_item) {
            $data_blocks_array = $this->invlists_block_memory_cache->get_data_blocks_array();

            $term = $lexicon_item->term;
            if (!array_key_exists($term, $data_blocks_array)) {
                die(" No data block exists for term: " . $term);
            } else {
                $data_block_class = $data_blocks_array["$term"];
                $dd = $data_block_class->header . $data_block_class->inverted_list;
                //$md5 = md5($dd);
                // WRITE TO DISK

                $data2 = $data2 . $dd;

                $lex_total += strlen($dd);
            }
            
            $blocks_processed++;
            if ($blocks_processed % 50 == 0)
            {
                if ($this -> verbose == true)
                {
                    echo "blocks processed " . $blocks_processed . " of " . $lexicon_size . "\n";
                }
            }
        }

        if ($this -> verbose == true)
        {
            echo "writing out blocks\n";
        }
        
        $invlists_handle = $this->invlists_file_manager->open_invlists_file();
        fseek($invlists_handle, $this->integer_length, SEEK_SET);
        fwrite($invlists_handle, $data2);
        $this->invlists_file_manager->close_invlists_file();
        $this->invlists_file_manager->close_invlists_file_real();

        $write_buffer_end_time = microtime(true);
        $write_buffer_time = $write_buffer_end_time - $write_buffer_start_time;
        if ($this->measure_times == true) {
            echo "\n\nWrite buffer time (flush cache to disk): " . $write_buffer_time . "\n";
        }
    }
    
    public function close_invlists_file_real()
    {
        return $this -> invlists_file_manager -> close_invlists_file_real();
    }

    public function open_invlists_file()
    {
        return $this -> invlists_file_manager -> open_invlists_file();
    }
    
    public function close_invlists_file()
    {
        return $this -> invlists_file_manager -> close_invlists_file();
    }

    public function update_invlist_block_merge_on_disk($lexicon_item, $term_statistics)
    {
        return $this -> invlists_file_manager -> update_invlist_block_merge_on_disk($lexicon_item, $term_statistics);
    }
    
    public function write_next_block_location_to_disk($next_block_location)
    {
        return $this -> invlists_file_manager -> write_next_block_location_to_disk($next_block_location);
    }
    
    public function get_next_block_location_from_disk()
    {
        return $this -> invlists_file_manager ->get_next_block_location_from_disk();
    }

    public function __destruct() {
    }   
    
    
}
