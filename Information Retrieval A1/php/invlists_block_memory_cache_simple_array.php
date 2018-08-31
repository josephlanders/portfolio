<?php

class invlists_memory_block_cache_simple_array {

    private $data_blocks_array = array();
    private $cache_size_in_use = 0;
    public $block_cache_memory_buffer_size = 0; // 9 MB memory buffer
    public $parent = null;
    private $configuration = null;
    private $use_memory_buffer_cache_eviction = false;
    private $block_cache = array();
    public $inverted_list_string_cache = array();

    public function __construct($arr) {

        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }

        $config = $this->configuration;
        $config_array = $config->get_config_array();
        foreach ($config_array as $conf_key => $conf_val) {
            $this->$conf_key = $conf_val;
        }
    }

    public function allocate_new_cache_entry($term, $disk_block_size, $memory_block_size) {

        /*
         * If using cache eviction - evict stuff from cache - Unused now
         * 
         *         if ($this->use_memory_buffer_cache_eviction == true) {

            $this->make_space_in_cache($term_not_to_evict, $memory_block_size);
        } 
         */
        if (!array_key_exists($term, $this->data_blocks_array)) {
            $this->data_blocks_array[$term] = "";

            $data_block_class = new data_block(array("data_block_handle" => null,
                "term" => $term,
                "file_offset" => null,
                "disk_block_size" => $disk_block_size,
                "memory_block_size" => $memory_block_size,
                "content_size" => 0));

            $this->data_blocks_array["$term"] = $data_block_class;
        } else {
            //die("term already exists in cache:" . $term);
        }

        return $data_block_class;
    }

    public function update_cache($term, $data_block_class) {
        $this->data_blocks_array["$term"] = $data_block_class;
    }

    public function get_data_block_from_cache($term) {
        $data_block_class = null;

        if (isset($this->data_blocks_array["$term"])) {
            $data_block_class = $this->data_blocks_array["$term"];
        }
        return $data_block_class;
    }
    
    public function get_data_blocks_array()
    {
        return $this -> data_blocks_array;
    }
    
    // Unused
    function write_data_block_from_cache_to_file($invlists_handle, $lexicon_item) {
        $dd = "";
        $md5 = 0;
        $term = $lexicon_item->term;
        if (!array_key_exists($term, $this->data_blocks_array)) {
            die(" No data block exists for term: " . $term);
        } else {
            $data_block_class = $this->data_blocks_array["$term"];

            // READ FROM VIRTUAL BUFFER
            $size_to_read = $lexicon_item->disk_block_size;
//            $size_to_read = $data_block_class -> disk_block_size;
            if ($size_to_read != 0) {
                $dd = $data_block_class -> data;      
                $md5 = md5($dd);
                // WRITE TO DISK
                $size_to_write = $lexicon_item->disk_block_size;
                //$size_to_write = $data_block_class -> disk_block_size;
                $file_offset_of_block = $lexicon_item->file_offset;
                fseek($invlists_handle, $file_offset_of_block, SEEK_SET);
                fwrite($invlists_handle, $dd, $size_to_write);
            } else {
                echo " Can't write cache blocks with 0 data size?\n";
            }
            //fclose($invlists_handle);
        }
        
        return $md5;
    }

    // Unused
    private function get_cache_size_in_use() {
        // For fixed block sizes
        //$cache_size_in_use = count($this->data_blocks_array) * $this->maximum_fixed_block_size;                
        $cache_size_in_use = $this->cache_size_in_use;

        // Not efficient
        /*
        foreach($this -> data_blocks_array as $key => $data_block_class)
        {
          $data_block_term = $data_block_class -> term;
        $length = $this -> get_block_length_in_memory($data_block_term);
            $cache_size_in_use += $length;
        }
        var_dump($cache_size_in_use);
         * 
         */


        return $cache_size_in_use;
    }

    // Unused
    private function estimate_cache_size_with_new_item($size_to_allocate) {
        $cache_size_in_use = $this->get_cache_size_in_use();
        //Fixed length blocks
        //$cache_size_in_use_new = $cache_size_in_use + $this->maximum_fixed_block_size;
        $cache_size_in_use_new = $cache_size_in_use + $size_to_allocate;

        return $cache_size_in_use_new;
    }

    // Unused
    private function evict($term) {
        if (isset($this->data_blocks_array["$term"])) {
            //$invlists_handle = $this -> parent -> open_invlists_handle();
            //$this->write_data_block_from_cache_to_file($invlists_handle, $lexicon_item);
            //$this -> close_invlists_handle();
            unset($this->data_blocks_array["$term"]);
        }
    }

    // Unused
    private function make_space_in_cache($term_not_to_evict, $space_required) {
        //$cache_size_in_use = $this->get_cache_size_in_use();
        $cache_size_in_use_new = $this->estimate_cache_size_with_new_item($space_required);


        while ($cache_size_in_use_new > $this->block_cache_memory_buffer_size) {
            //echo "Memory block evicted " . $term;
            //die();
            // evict the first block 
            // TODO: evict less frequently accessed block
            //$this->data_blocks_array[0];
            // Evict block by writing to disk and clearing array
            //$this -> 
//var_dump($this -> data_blocks_array);
//die();
            // echo count($this -> data_blocks_array) . "\n";
            //echo count($this -> data_blocks_array);

            $data = $this->data_blocks_array;
            //shuffle($data);
            $arr = array_values($data);
            $evicted = false;
            // Find one block to evict
            for ($i = 0; $i < count($data); $i++) {
                $some_data_block_class = $arr[0];

                $evict_candidate_term = $some_data_block_class->term;
                if ($evict_candidate_term != $term_not_to_evict) {
                    $this->evict($evict_candidate_term);

                    $evicted = true;
                    break;
                }
            };
        }
    }
    // Unused
    public function get_data_block_length_in_memory_cache($term) {
        $block_size = null;
        //$lexicon_item = $this -> lexicon_file_manager ->get_lexicon_item_from_lexicon($term);
        $data_block_class = $this->get_data_block_from_cache($term);
        $data_block_handle = $data_block_class->data_block_handle;

        $block_size = $data_block_class->block_size;

        return $block_size;
    }

    // Unused
    public function get_data_content_length_in_memory_cache($term) {
        $data_block_class = $this->get_data_block_from_cache($term);

        $data_content_size = $data_block_class->data_content_size;

        return $data_content_size;
    }
    
    // Unused
    public function get_data_block_from_disk($lexicon_item) {

        $data_block = "";

        $data_block_size = $lexicon_item->disk_block_size;
        $data_content_size = $lexicon_item->content_size;
        if ($lexicon_item->file_offset !== null) {
            //$invlists_handle = fopen($this->invlists_filename, "c+b");
            $invlists_handle = $this->open_invlists_file();

            $file_offset_of_block = $lexicon_item->file_offset;
            fseek($invlists_handle, $file_offset_of_block, SEEK_SET);
            $data_block = fread($invlists_handle, $data_block_size);
            $this->close_invlists_file();
            //fclose($invlists_handle);
        }

        return $data_block;
    }


}
