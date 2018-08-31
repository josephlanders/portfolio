<?php

require_once "doc_class.php";
require_once "term_statistics.php";
require_once "lexicon_class.php";
require_once "data_block.php";
require_once "lexicon_file_manager.php";
require_once "invlists_manager.php";

class utility {

    public $lexicon_filename = "lexicon";
    public $map_filename = "map";
    public $invlists_filename = "invlists";
    public $small_integer_length = 4; // This could be 3 bytes 
    public $small_integer_keyword = "L"; // 
    public $use_buffering = false;
    public $invlists_manager = null;
    public $lexicon_file_manager = null;
    public $use_variable_length_disk_blocks = false;
    public $measure_times = false;
    public $verbose = false;
    //public $padded_block_size = 3000;
    public $maximum_fixed_block_size = null;
    public $block_cache_memory_buffer_size = 9000000;
    // Delay writes
    public $write_memory_buffer_during_processing = true;
    private $configuration = null;
    public $create_time = 0;
    public $update_time = 0;
    public $write_time = 0;
    public $read_lexicon_time = 0;
    public $write_lexicon_time = 0;
    public $total_time = 0;
    public $integer_length = 4;

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

    public function write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering($inverted_list) {



        $total_start_time = microtime(true);
        
        $inverted_list_count = count($inverted_list);
        $inverted_list_processed = 0;
        foreach ($inverted_list as $inv_key => $term_statistics) {
            $term = $term_statistics->term;

            if ($term == "") {
                continue;
            }

            $lexicon_item = $this->lexicon_file_manager->get_lexicon_item_from_lexicon($term);

            if ($lexicon_item == null) {
                $padding = false;
                $padded_fixed_block_size = null;
                list($created_block_size, $created_content_size) = $this->invlists_manager->create_invlist_block_in_memory_cache_simple_array($term, $padding, $padded_fixed_block_size);

                $lexicon_item = new lexicon(array("term" => $term, "file_offset" => null,
                    "disk_block_size" => $created_block_size,
                    "content_size" => $created_content_size));

                $this->lexicon_file_manager->add_to_lexicon($lexicon_item);
            } else {
                
            }

            $updated_content_size = $this->invlists_manager->update_invlist_block_in_memory_cache_simple_array($lexicon_item, $term_statistics);

            $updated_block_size = $updated_content_size;

            $lexicon_item->disk_block_size = $updated_block_size;
            $lexicon_item->content_size = $updated_content_size;

            // Update FAT
            $this->lexicon_file_manager->update_lexicon_item($lexicon_item);
            $inverted_list_processed++;

            if ($inverted_list_processed % 50 == 0) {
                if ($this->verbose == true) {
                    echo " processed inverted list: " . $inverted_list_processed . " of " . $inverted_list_count . "\n";
                }
            }
        }

        $lexicon_array = $this->lexicon_file_manager->get_lexicon();
        $this->invlists_manager->write_buffer_simple_array($lexicon_array);

        $total_end_time = microtime(true);

        $total_time = $total_end_time - $total_start_time;
        $this->total_time += $total_time;

        if ($this->measure_times == true) {
            echo "\nInverted List Array Processing times\n";
            echo "Create (on disk or in cache) time was " . $this->create_time . " seconds\n";
            echo "Create (on disk or in cache) time was " . $this->create_time . " seconds\n";
            echo "Update (on disk or in cache) time was " . $this->update_time . " seconds\n";
            echo "Write (on disk or delayed until post-processing) time was " . $this->write_time . " seconds\n";
            echo "Read Lexicon time was " . $this->read_lexicon_time . " seconds\n";
            echo "Write Lexicon time was " . $this->write_lexicon_time . " seconds\n";
            echo "Inverted list processing time was " . $this->total_time . " seconds\n";
        }
    }

    public function write_out_inverted_list_array_struct_to_disk_variable_length_blocks_without_write_buffering(&$inverted_list) {
        $invlists_handle = $this->invlists_manager->open_invlists_file();

        $total_start_time = microtime(true);
        foreach ($inverted_list as $inv_key => $term_statistics) {
            $term = $term_statistics->term;

            if ($term == "") {
                continue;
            }

            $lexicon_item = $this->lexicon_file_manager->get_lexicon_item_from_lexicon($term);

            if ($lexicon_item == null) {
                $padding = false;
                $padded_fixed_block_size = null;
                list($file_offset_of_block, $created_block_size, $created_content_size) = $this->invlists_manager->create_invlist_block($term, $padding, $padded_fixed_block_size);

                $lexicon_item = new lexicon(array("term" => $term, "file_offset" => $file_offset_of_block,
                    "disk_block_size" => $created_block_size,
                    "content_size" => $created_content_size));

                $this->lexicon_file_manager->add_to_lexicon($lexicon_item);
            } else {
                
            }

            $updated_content_size = $this->invlists_manager->update_invlist_block_merge_on_disk($lexicon_item, $term_statistics);

            $updated_block_size = $updated_content_size;

            $next_block_location = $this->invlists_manager->get_next_block_location_from_disk();
            $lexicon_item->file_offset = $next_block_location;

            $next_block_location = $this->invlists_manager->update_next_block_location_in_memory($next_block_location, $updated_block_size);

            $this->invlists_manager->write_next_block_location_to_disk($next_block_location);

            $lexicon_item->disk_block_size = $updated_block_size;
            $lexicon_item->content_size = $updated_content_size;

            // Update FAT
            $this->lexicon_file_manager->update_lexicon_item($lexicon_item);
        }

        $this->invlists_manager->close_invlists_file();


        $total_end_time = microtime(true);

        $total_time = $total_end_time - $total_start_time;
        $this->total_time += $total_time;

        if ($this->measure_times == true) {
            echo "Inverted list processing time was " . $this->total_time . " seconds\n";
        }
    }

}
