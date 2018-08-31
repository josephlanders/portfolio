<?php

require_once("doc_class.php");
require_once "lexicon_file_manager.php";
require_once("invlists_block_memory_cache_simple_array.php");
require_once "configuration.php";

class invlists_file_manager {

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
    //public $invlists_block_memory_cache2 = null;
    private $configuration = null;
    private $next_block_location;

    public function __construct($arr) {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
        $this->invlists_block_memory_cache = new invlists_memory_block_cache_simple_array(array("configuration" => $this->configuration));

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

    public function write_out_blank_block_to_disk($write_location, $blank_data_block, $disk_block_size) {
        $handle = $this ->open_invlists_file();

        fseek($handle, $write_location, SEEK_SET);

        fwrite($handle, $blank_data_block, $disk_block_size);
        
        $this ->close_invlists_file();
    }
    
    public function write_next_block_location_to_disk($next_block_location) {
        $handle = $this ->open_invlists_file();

        fseek($handle, 0, SEEK_SET);
        fwrite($handle, pack($this->integer_keyword, $next_block_location));
        
        $this ->close_invlists_file();
    }

    public function update_invlist_block_merge_on_disk($lexicon_item, $term_statistics) {

        $file_offset_of_block = $lexicon_item->file_offset;       

        $incrementer = $term_statistics->number_of_unique_documents_occurs_in;
        
        $header = $this ->get_block_header_from_disk($file_offset_of_block);
        
        $num_unique_occurances = $header["num_unique_occurances"];

        $num_unique_occurances_updated = $this->update_unique_occurances_for_block($num_unique_occurances, $incrementer);                

        $header["num_unique_occurances"] = $num_unique_occurances_updated;
        
        $occurances_per_document_string = $term_statistics->occurances_per_document_string;   

        $length_of_inv_list = $header["inverted_list_length"];
        
        $additional_length_of_inv_list = strlen($occurances_per_document_string);
        
        $new_length_of_inv_list = $length_of_inv_list + $additional_length_of_inv_list;
        
        $header["inverted_list_length"] = $new_length_of_inv_list;
        
        $this ->write_block_header_to_disk($file_offset_of_block, $header);
        
        $end_of_inv_list = $file_offset_of_block + $this->integer_length + $this->integer_length + $length_of_inv_list;
        
        $file_offset_of_end_of_inverted_list = $end_of_inv_list;

        $write_length = $this->write_inverted_index_occurances_per_document_string_to_disk_as_append($file_offset_of_end_of_inverted_list, $occurances_per_document_string);
        
        // Don't use the integer sizes they are unreliable as PHP stores them as 1 byte sometimes
        $content_size = $this->integer_length + $this->integer_length + $new_length_of_inv_list;

        return $content_size;
    }

    public function write_data_block_from_cache_to_file($invlists_handle, $lexicon_item) {
        $this->invlists_block_memory_cache->write_data_block_from_cache_to_file($invlists_handle, $lexicon_item);
    }

    public function write_block_header_to_disk($file_offset_of_block, $header)
    {
        $handle = $this ->open_invlists_file();
        
        $num_occurances_in_invlist = $header["num_unique_occurances"];
        $new_length_of_inv_list = $header["inverted_list_length"];
        
        fseek($handle, $file_offset_of_block);

        $data = pack($this->integer_keyword, $num_occurances_in_invlist);
        $data .= pack($this->integer_keyword, $new_length_of_inv_list);
        // Can return false
        $write_length = fwrite($handle, $data);
        
        $this ->close_invlists_file();
        
        return $write_length;
    }

    public function get_block_header_from_disk($file_offset_of_block)
    {
        $handle = $this ->open_invlists_file();
        
        fseek($handle, $file_offset_of_block);
        #var_dump($file_offset_of_block);
        $num_occurances_in_invlist_raw = fread($handle, $this->integer_length * 2);
        $char_array = unpack($this->integer_keyword . "*", $num_occurances_in_invlist_raw);
        $num_occurances_in_invlist = $char_array[1];
        $length_of_inv_list = $char_array[2];
        
        $header = array();
        $header["num_unique_occurances"] = $num_occurances_in_invlist;
                $header["inverted_list_length"] = $length_of_inv_list;
                
                $this ->close_invlists_file();
        
        return $header;
    }
    
    public function open_invlists_file() {
        $invlists_filename = $this->invlists_filename;

        $invlists_handle = $this->invlists_handle;
        if ($invlists_handle === null) {
            $invlists_handle = fopen($invlists_filename, "c+b");
            $this->invlists_handle = $invlists_handle;
        }
        return $invlists_handle;
    }

    public function close_invlists_file() {
        $invlists_handle = $this->invlists_handle;
        if ($invlists_handle != null) {
        fflush($invlists_handle);
        }
    }

    public function close_invlists_file_real() {
        $invlists_handle = $this->invlists_handle;
        if ($invlists_handle !== null)
        {
           fflush($invlists_handle);
           fclose($invlists_handle);
        }
        $this -> invlists_handle = null;
    }

    public function get_next_block_location_from_disk() {

        $invlists_handle = $this->open_invlists_file();

        fseek($invlists_handle, 0, SEEK_SET);
        // Read the reference to next block location we can write to
        $next_block_location_raw = fread($invlists_handle, $this->integer_length);
        $char_array = unpack($this->integer_keyword, $next_block_location_raw);
        $next_block_location = $char_array[1];

        $this->close_invlists_file();

        return $next_block_location;
    }
    
    public function get_block_from_disk($file_offset_of_block, $length_of_block) {
        
        $invlists_handle = $this ->open_invlists_file();
               
        fseek($invlists_handle, $file_offset_of_block, SEEK_SET);
        $data_packed = fread($invlists_handle, $length_of_block);
        
        $header_packed = substr($data_packed, 0, 8);
        $inverted_list_packed = "";
        if (strlen($data_packed) > 8)
        {
           $inverted_list_packed = substr($data_packed, 8);
        }
        
        $data_block_class = new data_block(array("header" => $header_packed, "inverted_list" => $inverted_list_packed));
        
        $this ->close_invlists_file();
        
        return $data_block_class;
    }
    
    public function get_block_inverted_list_from_data($data_block_class, $num_unique_occurances, $length_of_inv_list) {
        
        $inverted_list = $data_block_class -> inverted_list;
        
        $inv_list_unpacked = unpack($this->integer_keyword . "*", $inverted_list);
        
        $occurances_per_document_array = array();
        $expected_array_size = $num_unique_occurances * 2;
        
        $expected_array_end_index = $expected_array_size;
        for ($i = 1; $i < 1 + $expected_array_end_index; $i = $i + 2) {
            $key = $inv_list_unpacked[$i];
            $value = $inv_list_unpacked[$i + 1];
            $occurances_per_document_array["$key"] = $value;            
        }
        
        return $occurances_per_document_array;
    }
   
    public function get_block_inverted_list_from_disk($file_offset_of_block, $num_unique_occurances, $length_of_inv_list) {
        
        $invlists_handle = $this ->open_invlists_file();
        
        $start_of_inv_list = $file_offset_of_block + $this->integer_length + $this->integer_length;

        fseek($invlists_handle, $start_of_inv_list, SEEK_SET);
        $inv_list = fread($invlists_handle, $length_of_inv_list);
        $inv_list_unpacked = unpack($this->integer_keyword . "*", $inv_list);

        $occurances_per_document_array = array();
        $expected_array_size = $num_unique_occurances * 2;
        for ($i = 1; $i < $expected_array_size; $i = $i + 2) {
            $key = $inv_list_unpacked[$i];
            $value = $inv_list_unpacked[$i + 1];
            $occurances_per_document_array["$key"] = $value;
        }
        
        $this ->close_invlists_file();
        
        return $occurances_per_document_array;
    }
    
    public function __destruct() {
    }
    
    public function write_inverted_index_occurances_per_document_string_to_disk_as_append($file_offset_of_end_of_inverted_list, $occurances_per_document_string) {
        $handle = $this ->open_invlists_file();
        
        fseek($handle, $file_offset_of_end_of_inverted_list);
        $write_length = fwrite($handle, $occurances_per_document_string);

        $this ->close_invlists_file();
        return array($write_length);
    }



    public function write_inverted_index_occurances_per_document_array_to_disk_as_append($file_offset_of_block, $length_of_inv_list, $occurances_per_document_array) {
        $handle = $this ->open_invlists_file();
        $end_of_inv_list = $file_offset_of_block + $this->integer_length + $this->integer_length + $length_of_inv_list;
        fseek($handle, $end_of_inv_list);

        $write_text = "";
        $debug_text = "";
        foreach ($occurances_per_document_array as $key => $value) {

            $write_text .= pack($this->integer_keyword, $key);
            $write_text .= pack($this->integer_keyword, $value);
            $length_of_inv_list += $this->integer_length;
            $length_of_inv_list += $this->integer_length;

            $debug_text .= $key . "," . $value . "|";
        }

        $write_length = fwrite($handle, $write_text);
        
        $this ->close_invlists_file();
        
        return array($write_length, $length_of_inv_list);
    }

    public function update_unique_occurances_for_block($num_occurances_in_invlist, $incrementer) {

        $num_occurances_in_invlist += $incrementer;

        return $num_occurances_in_invlist;
    }   
    
    public function get_inverted_index_from_disk($lexicon_item) {

        $term = $lexicon_item->term;

        $term_statistics = new term_statistics($term);

        $file_offset_of_block = $lexicon_item->file_offset;

        $content_size = $lexicon_item -> content_size;
        
        $data_block_class = $this ->get_block_from_disk($file_offset_of_block, $content_size);               
        
        $header = $this -> extract_header_efficient($data_block_class);
        
        $num_unique_occurances = $header["num_unique_occurances"];

        $term_statistics->number_of_unique_documents_occurs_in = $num_unique_occurances;
        
        $length_of_inv_list = $header["inverted_list_length"];
        
        $occurances_per_document_array = $this -> get_block_inverted_list_from_data($data_block_class, $num_unique_occurances, $length_of_inv_list);
        
        $term_statistics->occurances_per_document_array = $occurances_per_document_array;

        return $term_statistics;
    }
    
    public function extract_header_efficient($data_block_class) {
        $header= $data_block_class->header;
        $length_of_header = $this->integer_keyword * 2;

        $header_string_unpacked_array = unpack($this->integer_keyword . "2", $header);
        $header = array();
        $header["num_unique_occurances"] = $header_string_unpacked_array[1];
        $header["inverted_list_length"] = $header_string_unpacked_array[2];
        
        return $header;
    }
           
    // inefficient
    public function write_inverted_list_length_to_disk($file_offset_of_block, $new_length_of_inv_list) {
        $handle = $this ->open_invlists_file();
        $length_of_inv_list_location = $file_offset_of_block + $this->integer_length;
        fseek($handle, $length_of_inv_list_location);
        fwrite($handle, pack($this->integer_keyword, $new_length_of_inv_list));
        
        $this ->close_invlists_file();
    }
    
    // inefficient, unused
    public function get_block_inverted_list_length_from_disk($file_offset_of_block) {
        // Get the length of the inverted list
        
        $handle = $this ->open_invlists_file();

        $data_location = $file_offset_of_block + $this->integer_length;

        fseek($handle, $data_location, SEEK_SET);
        $length_of_inv_list_raw = fread($handle, $this->integer_length);
        $char_array = unpack($this->integer_keyword, $length_of_inv_list_raw);
        $length_of_inv_list = $char_array[1];
        
        $this ->close_invlists_file();

        return $length_of_inv_list;
    }
    // Inefficient, Unused
    public function write_unique_occurances_for_block_to_disk($file_offset_of_block, $num_occurances_in_invlist) {
$handle = $this ->open_invlists_file();
        fseek($handle, $file_offset_of_block);

        // Can return false
        $write_length = fwrite($handle, pack($this->integer_keyword, $num_occurances_in_invlist));

        $this ->close_invlists_file();
        return $write_length;
    }
    
    // Inefficient, Unused
    public function get_unique_occurances_for_block_from_disk($file_offset_of_block) {
        
        $handle = $this ->open_invlists_file();
        
        // Read in existing value
        fseek($handle, $file_offset_of_block);

        $num_occurances_in_invlist_raw = fread($handle, $this->integer_length);
        $char_array = unpack($this->integer_keyword, $num_occurances_in_invlist_raw);
        $num_occurances_in_invlist = $char_array[1];

        $this ->close_invlists_file();

        return $num_occurances_in_invlist;
    }
 
    // unused
    public function write_inverted_index_occurances_per_document_string_to_disk_as_append_old($file_offset_of_block, $length_of_inv_list, $occurances_per_document_string) {
        $handle = $this ->open_invlists_file();
        // 8 bytes 
        // Jump to the end of the inverted list where we can safely append
        $end_of_inv_list = $file_offset_of_block + $this->integer_length + $this->integer_length + $length_of_inv_list;
        fseek($handle, $end_of_inv_list);

        $write_text = $occurances_per_document_string;
        $debug_text = "";

        $length_of_inv_list += strlen($occurances_per_document_string);

        $write_length = fwrite($handle, $write_text);
        
        $this ->close_invlists_file();

        return array($write_length, $length_of_inv_list);
    }

    // unused
    public function update_next_block_location_by_size_of_current_block($term) {

        $invlists_handle = $this->open_invlists_file();
        // Block size is 8 for empty structs, 3000 for padded structs
        $size_of_block = $this->invlists_block_memory_cache->get_data_block_length_in_memory_cache($term); // subtract empty struct size

        $next_block_location = $this->get_next_block_location_from_disk();

        $next_block_location = $this->update_next_block_location_in_memory($next_block_location, $size_of_block);

       
        $this->write_next_block_location_to_disk($next_block_location);

        $this->close_invlists_file();
    }


}

    /*
    public function create_invlist_block_in_memory_cache_simple_array($term, $padding, $padded_fixed_block_size) {

        list($size_of_block, $blank_data_block) = $this->create_blank_data_block_struct($padding, $padded_fixed_block_size);

        $data_block_class = $this->invlists_block_memory_cache->allocate_new_cache_entry($term, $size_of_block, $size_of_block);

        $next_block_location = $this->get_next_block_location_from_disk();

        $data_block_class->file_offset = $next_block_location;

        $disk_block_size = $size_of_block;

        // Block is already packed
        $data_block_class->data = $blank_data_block;
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
    } */

