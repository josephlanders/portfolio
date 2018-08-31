<?php

/* s3163776@student.rmit.edu.au
 * Joseph Landers
 * ISYS1078/1079 Assignment 1
 * 
 * This is the code that indexes a data/text file containing <DOC> tags
 */

/*  Statistics for write-up
 *                 // Merging on the outer loop causes higher memory usage 
  // 29.5MB by 400th document versus 19MB  with array to string merge
  // Using the inner loop (1 docs inverted list) merge array into string - 29.1MB memory usage, 29.83 - 0.7 MB - 143S
  // Using the outer loop (2621 docs inverted list) merge array into string = 69.6MB memory usage, 633s
  // string to string merge - inner loop - 148-165 seconds, 29.79MB memory usage
  // outer 29.7 B - 430s
  // 69.65MB - 215s
  // array to array 69.5MB 636 s
  //
  //$small_inverted_list = array();
  //$small_inverted_list = array();

 */

require_once "doc_class.php";
require_once "term_statistics.php";
require_once "lexicon_class.php";
require_once "utility.php";
require_once "invlists_manager.php";
require_once "lexicon_file_manager.php";
require_once "map_file_manager.php";
require_once "configuration.php";
require_once "stoplist_file_manager.php";

class Indexer {

    // Our unique reference to documents in memory, used as an incrementer
    private $all_doc_number = 0;
    private $print_content_terms = false;
    private $stoplist_file = "";
    private $stoplist_file_specified = false;
    // an array of class term_statistics
    // the key to this array is the "term" name since this is unique 
    // and storable as the key
    private $inverted_list = array();
    private $compression = false;
    private $clean_immediately = false;
    private $clean_individual = false;
    private $parse_docs_individually = false;
    private $debug = false;
    private $file_blob_read_length = 10000000;
    private $use_buffering = false;
    private $use_variable_length_disk_blocks = true;
    public $lexicon_filename = "lexicon";
    public $map_filename = "map";
    public $invlists_filename = "invlists";
    public $map_file_manager = null;
    public $invlists_manager = null;
    public $lexicon_file_manager = null;
    public $stoplist_file_manager = null;
    public $measure_times = false;
    public $verbose = false;
    public $maximum_fixed_block_size = 1000;
    public $maximum_fixed_block_size_postings = 3000;
    public $maximum_fixed_block_size_inverted_list_length = 12000;
    public $block_cache_memory_buffer_size = 10000;
    public $write_memory_buffer_during_processing = true;
    public $use_memory_buffer_cache_eviction = false;
    private $configuration = null;
    public $unset_time = 0;
    public $garbage_time = 0;
    public $collection_to_index = "";
    public $integer_keyword = "L";
    public $integer_length = 4;
    private $gc_blob_collected = 0;
    private $gc_doc_collected = 0;
    private $gc_merge_collected = 0;
    private $gc_cleartext_collected = 0;
    private $gc_rawtext_collected = 0;
    private $gc_tokens_collected = 0;
    private $gc_cleanedtokens_collected = 0;
    private $eof = false;

    public function __construct($configuration) {
        gc_enable();
        if ($configuration !== null) {

            $config_array = $configuration->get_config_array();
            foreach ($config_array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function initialise() {
        $config_array = array("use_buffering" => $this->use_buffering,
            "use_variable_length_disk_blocks" => $this->use_variable_length_disk_blocks,
            "measure_times" => $this->measure_times,
            "verbose" => $this->verbose,
            "maximum_fixed_block_size" => $this->maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $this->maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $this->write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $this->block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $this->use_memory_buffer_cache_eviction);


        $this->configuration = new configuration($config_array);

        if ($this->verbose) {
            
        }

        $this->lexicon_file_manager = new lexicon_file_manager(array("lexicon_filename" => $this->lexicon_filename,
            "configuration" => $this->configuration));


        $this->map_file_manager = new map_file_manager(array("map_filename" => $this->map_filename,
            "configuration" => $this->configuration));


        $this->invlists_manager = new invlists_manager(array("invlists_filename" => $this->invlists_filename, "lexicon_filename" => $this->lexicon_filename,
            "lexicon_file_manager" => $this->lexicon_file_manager,
            "configuration" => $this->configuration));

        $this->stoplist_file_manager = new stoplist_file_manager(array("stoplist_filename" => $this->stoplist_file,
            "configuration" => $this->configuration));
        $this->stoplist_file_manager->load_stoplist();


        $this->utility = new utility(array("lexicon_filename" => $this->lexicon_filename,
            "invlists_filename" => $this->invlists_filename,
            "map_filename" => $this->map_filename,
            "configuration" => $this->configuration,
            "invlists_manager" => $this->invlists_manager,
            "lexicon_file_manager" => $this->lexicon_file_manager,
            "map_file_manager" => $this->map_file_manager));
    }

    public function clear_files() {
        $this->lexicon_file_manager->initialise_lexicon_file();
        $this->map_file_manager->initialise_map_file();
        $this->invlists_manager->initialise_invlists_file();
    }

    public function parse_arguments() {
        $ret = $this->arguments($_SERVER['argv']);

        foreach ($ret["options"] as $key => $options) {
            
        }

        foreach ($ret["flags"] as $key => $flags) {

            if ($flags[0] == "p") {
                $this->print_content_terms = true;
            }

            if ($flags[0] == "c") {
                $this->compression = true;
            }

            if ($flags[0] == "debug") {
                $this->debug = true;
            }

            if ($flags[0] == "t") {
                $this->measure_times = true;
            }

            if ($flags[0] == "x") {
                $this->verbose = true;
            }

            if ($flags[0] == "e") {
                $this->write_memory_buffer_during_processing = false;
            }

            if ($flags[0] == "s") {
                $this->stoplist_file_specified = true;

                $stoplist_file = $ret["arguments"][0];
                $this->stoplist_file = $stoplist_file;
            }
        }

        // The doc collection filename is always the last arg
        $arg_count = count($ret["arguments"]);
        $this->collection_to_index = "";
        if (isset($ret["arguments"][$arg_count - 1])) {
            $this->collection_to_index = $ret["arguments"][$arg_count - 1];
        }
    }

    public function start_processing() {
        $collection_to_index = $this->collection_to_index;

        $start = microtime(true);

        if ($this->verbose == true) {
            
        }

        $this->parse_data_one_doc_at_a_time($collection_to_index);

        $this->invlists_manager->close_invlists_file_real();
        $this->lexicon_file_manager->write_lexicon();
        $this->map_file_manager->write_map();

        $end = microtime(true);
        $time_taken = $end - $start;
        if ($this->measure_times == true) {
            echo "\ntime taken: " . $time_taken . " seconds";
        }
    }

    // The main program code to parse the data file and process it
    public function parse_data_one_doc_at_a_time($collection_to_index) {

        $handle = fopen($collection_to_index, "r") or die("can't open index file " . $collection_to_index);

        // Relative read position in the blob
        $blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        $file_start_pos = 0;

        $all_doc_array = array();

        $blob = "";
        $blob_doc_array = array();
        $stitch_blob = false;
        $length_to_read = $this->file_blob_read_length;

        if ($this->verbose == true) {
            echo "Reading file blobs of maximum length: " . $length_to_read . "\n";
        }


        $docs_processed = 0;
        $docs_processed_last = 0;

        $arr = fstat($handle);

        $filesize = $arr["size"];
        // Clear the inverted list on each set of documents (so that we can recover some memory)
        $inverted_list = array();
        while (!feof($handle)) {


            if ($this->verbose == true) {
                echo "memory usage before blob processed " . memory_get_usage() . " \n";
            }

            $new_blob = "";

            // DO NOT DELETE
            $blob_start_pos = 0;

            $blob_doc_array = array();



            list($blob, $length_to_read) = $this->get_next_blob($handle, $filesize, $file_start_pos, $length_to_read, $stitch_blob);

            // Returns an array of docs found in the blob
            list($blob_end_pos, $found_doc, $stitch_blob) = $this->get_docs_from_blob($blob, $blob_start_pos, $blob_doc_array);

            $blob_start_pos = $blob_end_pos;

            $docs_processed_last = $docs_processed;
            $docs_processed += count($blob_doc_array);
            if ($this->verbose == true) {
                //echo "docs processed " . $docs_processed . "\n";
            }

            $file_start_pos = $this->get_next_file_pointer($file_start_pos, $blob_start_pos, $stitch_blob);

            $success = $this->increment_file_pointer($handle, $file_start_pos);

            $blob = null;
            unset($blob);
            if ($this->clean_immediately == true) {
                $this->gc_blob_collected += gc_collect_cycles();
            }

            if ($found_doc == true) {
                if ($this->verbose == true) {
                    echo "Memory usage before blob docs processed: " . memory_get_usage() . " \n";
                }
                // Parse all the complete docs from the blob into the doc_class array
                $this->extract_doc_text_from_doc_array($blob_doc_array);


                $this->clear_raw_text($blob_doc_array);

                // Once we have docs parsed we can add to mappings
                $this->map_file_manager->add_mappings($blob_doc_array);

                $docs_processed_inner_loop = 0;
                $ts = microtime(true);
                foreach ($blob_doc_array as $key => $blob_doc) {
                    $small_inverted_list = array();
                    if ($this->verbose == true) {
                        //echo "Memory usage before blob docs processed: " . memory_get_usage() . " \n";
                    }

                    $this->get_content_terms_as_tokens_one_doc($blob_doc);

                    $this->clear_text_one_doc($blob_doc);
                    $this->clear_tokens_one_doc($blob_doc);

                    $this->get_term_frequency_per_document_one_doc_array($small_inverted_list, $blob_doc);
                    $this->clear_cleaned_tokens_one_doc($blob_doc);
                    $this->get_term_frequency_by_unique_document_count($small_inverted_list);

                    // Uncomment this to process data in memory as strings not as arrays
                    //$this->get_term_frequency_per_document_one_doc_string($small_inverted_list, $blob_doc);
                    //$this->clear_cleaned_tokens_one_doc($blob_doc);
                    ////$this->get_term_frequency_by_unique_document_count_string($small_inverted_list);

                    if ($this->verbose == true) {
                        
                    }

                    //$this->merge_inverted_lists_string_with_string($inverted_list, $small_inverted_list);
                    $this->merge_inverted_lists_array_with_string($inverted_list, $small_inverted_list);
                    //$this->merge_inverted_lists_array_with_array($inverted_list, $small_inverted_list);

                    if ($this->clean_immediately == false) {
                        // Garbage collect after processing each blob
                        $this->gc_doc_collected += gc_collect_cycles();
                    }

                    $docs_processed_inner_loop++;

                    if ($this->verbose == true) {
                        if ($docs_processed_inner_loop % 50 == 0) {
                            echo "Proccessing from doc: " . $docs_processed_last . " docs processed inner loop: " . $docs_processed_inner_loop . "\n";
                            echo "Memory usage after blob docs processed: " . memory_get_usage() . " \n";
                            //echo "\ncycles collected: " . $this->gc_doc_collected . "\n";
                        }
                    }

                    $this->clear_blob_doc($blob_doc);
                }
                
                //uncomment to run the merge on the outer loop
                //$this->merge_inverted_lists_string_with_string($inverted_list, $small_inverted_list);
                //$this->merge_inverted_lists_array_with_string($inverted_list, $small_inverted_list);
                //$this->merge_inverted_lists_array_with_array($inverted_list, $small_inverted_list);
                //echo "\nB " . memory_get_usage() . "\n";
                $tf = microtime(true);
                $tt = $tf - $ts;
                if ($this->verbose == true) {
                    echo "time to do " . $docs_processed . " documents: " . $tt . "seconds\n";
                }

                $this->clear_inverted_list($small_inverted_list);
                $this->clear_blob_doc_array($blob_doc_array);

                if ($this->verbose == true) {

                    /*
                      echo "\nblob cycles collected: " . $this->gc_blob_collected . "\n";
                      echo "\ndoc cycles collected: " . $this->gc_doc_collected . "\n";
                      echo "\ncleartext cycles collected: " . $this->gc_cleartext_collected . "\n";
                      echo "\nrawtext cycles collected: " . $this->gc_rawtext_collected . "\n";
                      echo "\ntokens cycles collected: " . $this->gc_tokens_collected . "\n";
                      echo "\ncleanedtokens cycles collected: " . $this->gc_cleanedtokens_collected . "\n";
                      echo "\nmerge cycles collected: " . $this->gc_merge_collected . "\n";
                     * 
                     */


                    echo "\ndocs processed " . $docs_processed . "\n";
                }
            }
            if ($this->verbose == true) {
                echo "Memory usage after blob processed: " . memory_get_usage() . " \n";

                /*
                  echo "\nblob cycles collected: " . $this->gc_blob_collected . "\n";
                  echo "\ndoc cycles collected: " . $this->gc_doc_collected . "\n";
                  echo "\ncleartext cycles collected: " . $this->gc_cleartext_collected . "\n";
                  echo "\nrawtext cycles collected: " . $this->gc_rawtext_collected . "\n";
                  echo "\ntokens cycles collected: " . $this->gc_tokens_collected . "\n";
                  echo "\ncleanedtokens cycles collected: " . $this->gc_cleanedtokens_collected . "\n";
                  echo "\nmerge cycles collected: " . $this->gc_merge_collected . "\n";
                 * 
                 */
            }
        }

        $this->utility->write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering($inverted_list);
        // This is the highly inefficient way - writes one file at a time but good for testing
        //$this->utility->write_out_inverted_list_array_struct_to_disk_variable_length_blocks_without_write_buffering($inverted_list);
        fclose($handle);
        //exit();
    }

    public function count_file_docs($collection_to_index) {
        $handle = fopen($collection_to_index, "r") or die("can't open index file " . $collection_to_index);

        // Relative read position in the blob
        $blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        $file_start_pos = 0;

        $all_doc_array = array();

        $blob = "";
        $blob_doc_array = array();
        $stitch_blob = false;
        $length_to_read = $this->file_blob_read_length;
        $total_docs = 0;

        $arr = fstat($handle);
        $filesize = $arr["size"];
        while (!feof($handle)) {
            $new_blob = "";

            if ($this->verbose == true) {
                echo "Memory usage before blob processed: " . memory_get_usage() . " \n";
            }

            $blob_doc_array = array();

            // Clear the inverted list on each set of documents
            $inverted_list = array();

            list($blob, $length_to_read) = $this->get_next_blob($handle, $filesize, $file_start_pos, $length_to_read, $stitch_blob);

            list($blob_start_pos, $found_doc, $stitch_blob) = $this->get_docs_from_blob($blob, $blob_start_pos, $blob_doc_array);

            $total_docs += count($blob_doc_array);


            $file_start_pos = $this->get_next_file_pointer($file_start_pos, $blob_start_pos, $stitch_blob);

            $success = $this->increment_file_pointer($handle, $file_start_pos);

            $blob = null;
            unset($blob);
            if ($this->clean_immediately == true) {
                $this->gc_blob_collected += gc_collect_cycles();
            }

            if ($this->verbose == true) {
                echo "Memory usage after blob processed: " . memory_get_usage() . " \n";
            }
        }

        fclose($handle);

        return $total_docs;
    }

    public function clear_inverted_list(&$inverted_list) {
        foreach ($inverted_list as $key => $term_statistics) {
            $term_statistics->__destruct();
            if (isset($inverted_list[$key])) {
                unset($inverted_list[$key]);
            }
        }

        $inverted_list = null;
        unset($inverted_list);
    }

    public function get_length_to_read($length_to_read, $filesize, $file_start_pos, $stitch_blob) {

        if ($stitch_blob == true) {
            $length_to_read = $length_to_read + $this->file_blob_read_length;
        } else {
            $length_to_read = $this->file_blob_read_length;
        }

        // Only read to end of file.
        if ($file_start_pos + $length_to_read > $filesize) {
            // Read Past EOF so that FEOF is triggered
            $length_to_read = $filesize - $file_start_pos + 1;
            $this->eof = true;
        }

        return $length_to_read;
    }

    public function get_next_blob($handle, $filesize, $file_start_pos, $length_to_read, $stitch_blob) {

        $new_blob = "";

        $length_to_read = $this->get_length_to_read($length_to_read, $filesize, $file_start_pos, $stitch_blob);


        $this->read_blob($handle, $length_to_read, $new_blob);

        if ($stitch_blob == true) {
            $blob_start_pos = 0;
            $blob = $new_blob;
        } else {
            // Reset blob read position as we have a new blob
            $blob_start_pos = 0;
            $blob = $new_blob;
        }

        return array($blob, $length_to_read);
    }

    public function get_next_file_pointer($file_start_pos, $blob_start_pos, $stitch_blob) {

        if ($stitch_blob == true) {
            
        } else {
            $file_start_pos += $blob_start_pos;
        }
        //echo "file position is now " . $file_start_pos . "\n";

        return $file_start_pos;
    }

    public function merge_inverted_lists_array_with_string(&$inverted_list, &$small_inverted_list) {
        foreach ($small_inverted_list as $small_key => $small_term_statistics) {
            if (!isset($inverted_list["$small_key"])) {
                $inverted_list["$small_key"] = $small_term_statistics;

                //stringify
                $small_occurances_per_document_array = $small_term_statistics->occurances_per_document_array;
                $small_occurances_per_document_string = "";
                foreach ($small_occurances_per_document_array as $key => $value) {
                    $small_occurances_per_document_string .= pack($this->integer_keyword, $key);
                    $small_occurances_per_document_string .= pack($this->integer_keyword, $value);
                }

                $small_term_statistics->occurances_per_document_string = $small_occurances_per_document_string;

                foreach ($small_term_statistics->occurances_per_document_array as $key => $value) {
                    $small_term_statistics->occurances_per_document_array["$key"] = null;
                    unset($small_term_statistics->occurances_per_document_array["$key"]);
                }
                $small_term_statistics->occurances_per_document_array = array();

                if ($this->clean_immediately == true) {
                    $this->gc_merge_collected += gc_collect_cycles();
                }
            } else {
                $term_statistics = $inverted_list["$small_key"];
                $small_term_statistics = $small_inverted_list["$small_key"];

                $old_occurances = $term_statistics->number_of_unique_documents_occurs_in;

                $old_occurances_per_document_string = $term_statistics->occurances_per_document_string;
                $small_occurances = $small_term_statistics->number_of_unique_documents_occurs_in;
                $small_occurances_per_document_array = $small_term_statistics->occurances_per_document_array;
                $small_occurances_per_document_string = "";
                foreach ($small_occurances_per_document_array as $key => $value) {
                    $small_occurances_per_document_string .= pack($this->integer_keyword, $key);
                    $small_occurances_per_document_string .= pack($this->integer_keyword, $value);
                }
                //$stringify
                $occurances = $old_occurances + $small_occurances;

                // All we have to do is combine strings without sorting.
                $occurances_per_document_string = $old_occurances_per_document_string . $small_occurances_per_document_string;

                $term_statistics->number_of_unique_documents_occurs_in = $occurances;
                $term_statistics->occurances_per_document_string = $occurances_per_document_string;
            }
        }
    }

    public function merge_inverted_lists_string_with_string(&$inverted_list, &$small_inverted_list) {
        foreach ($small_inverted_list as $small_key => $small_term_statistics) {
            if (!isset($inverted_list["$small_key"])) {
                $inverted_list["$small_key"] = $small_term_statistics;
            } else {
                $term_statistics = $inverted_list["$small_key"];
                $small_term_statistics = $small_inverted_list["$small_key"];

                $old_occurances = $term_statistics->number_of_unique_documents_occurs_in;
                $old_occurances_per_document_string = $term_statistics->occurances_per_document_string;
                $small_occurances = $small_term_statistics->number_of_unique_documents_occurs_in;
                $small_occurances_per_document_string = $small_term_statistics->occurances_per_document_string;

                $occurances = $old_occurances + $small_occurances;
                $occurances_per_document_string = $old_occurances_per_document_string . $small_occurances_per_document_string;

                $term_statistics->number_of_unique_documents_occurs_in = $occurances;
                $term_statistics->occurances_per_document_string = $occurances_per_document_string;
            }
        }
    }

    public function merge_inverted_lists_array_with_array(&$inverted_list, &$small_inverted_list) {
        foreach ($small_inverted_list as $small_key => $small_term_statistics) {
            if (!isset($inverted_list["$small_key"])) {
                $inverted_list["$small_key"] = $small_term_statistics;
            } else {
                $term_statistics = $inverted_list["$small_key"];
                $small_term_statistics = $small_inverted_list["$small_key"];

                $old_occurances = $term_statistics->number_of_unique_documents_occurs_in;
                $old_occurances_per_document_array = $term_statistics->occurances_per_document_array;
                $small_occurances = $small_term_statistics->number_of_unique_documents_occurs_in;
                $small_occurances_per_document_array = $small_term_statistics->occurances_per_document_array;

                $occurances = $old_occurances + $small_occurances;
                $occurances_per_document_array = $old_occurances_per_document_array;

                foreach ($small_occurances_per_document_array as $key => $value) {
                    $occurances_per_document_array[$key] = $value;
                }

                $term_statistics->number_of_unique_documents_occurs_in = $occurances;
                $term_statistics->occurances_per_document_array = $occurances_per_document_array;
            }
        }
    }

    // Count number of documents this term occurs in uniquely
    public function get_term_frequency_by_unique_document_count_string(&$inverted_list) {
        foreach ($inverted_list as $term_key_is_term_name => $term_statistics) {

            $occurances_per_document_string = $term_statistics->occurances_per_document_string;
            $number_of_unique_documents_occurs_in = $occurances_per_document_string / (2 * $this->integer_length);
            $term_statistics->number_of_unique_documents_occurs_in = $number_of_unique_documents_occurs_in;
        }

        return $inverted_list;
    }

    // Count number of documents this term occurs in uniquely
    public function get_term_frequency_by_unique_document_count(&$inverted_list) {
        foreach ($inverted_list as $term_key_is_term_name => $term_statistics) {
            $number_of_unique_documents_occurs_in = count($term_statistics->occurances_per_document_array);
            $term_statistics->number_of_unique_documents_occurs_in = $number_of_unique_documents_occurs_in;
        }

        return $inverted_list;
    }

    public function clear_text(&$blob_doc_array) {
        foreach ($blob_doc_array as $key => $doc_class) {

            $this->clear_text_one_doc($doc_class);
        }
        if ($this->clean_immediately == true) {
            $this->gc_cleartext_collected += gc_collect_cycles();
        }

        return $blob_doc_array;
    }

    public function clear_text_one_doc(&$doc_class) {

        $doc_class->text = null;
        unset($doc_class->text);
        $doc_class->text = "";
        return $doc_class;
    }

    public function clear_raw_text(&$blob_doc_array) {
        foreach ($blob_doc_array as $key => $doc_class) {
            $doc_class->raw_text = null;
            unset($doc_class->raw_text);
        }
        if ($this->clean_immediately == true) {
            $this->gc_rawtext_collected += gc_collect_cycles();
        }

        return $blob_doc_array;
    }

    public function clear_tokens(&$blob_doc_array) {
        foreach ($blob_doc_array as $key => $doc_class) {

            $this->clear_tokens_one_doc($doc_class);
        }
        if ($this->clean_immediately == true) {
            $this->gc_tokens_collected += gc_collect_cycles();
        }

        return $blob_doc_array;
    }

    public function clear_tokens_one_doc(&$doc_class) {

        foreach ($doc_class->tokens as $token_key => $token) {
            $doc_class->tokens[$token_key] = null;
            unset($doc_class->tokens[$token_key]);
        }


        $doc_class->tokens = null;
        unset($doc_class->tokens);


        $doc_class->tokens = array();

        return $doc_class;
    }

    public function clear_cleaned_tokens(&$blob_doc_array) {
        foreach ($blob_doc_array as $key => $doc_class) {
            $doc_class = $this->clear_cleaned_tokens_one_doc($doc_class);
        }
        if ($this->clean_immediately == true) {
            $this->gc_cleanedtokens_collected += gc_collect_cycles();
        }

        return $blob_doc_array;
    }

    public function clear_cleaned_tokens_one_doc(&$doc_class) {


        foreach ($doc_class->cleaned_tokens as $token_key => $token) {
            $doc_class->cleaned_tokens[$token_key] = null;
            unset($doc_class->cleaned_tokens[$token_key]);
        }

        $doc_class->cleaned_tokens = null;
        unset($doc_class->cleaned_tokens);

        return $doc_class;
    }

    public function clear_blob_doc(&$blob_doc) {

        $blob_doc->__destruct();
        $blob_doc = null;
        unset($blob_doc);

        if ($this->clean_immediately == true) {
            $this->gc_clearblobdoc_collected += gc_collect_cycles();
        }
    }

    public function clear_blob_doc_array(&$blob_doc_array) {
        foreach ($blob_doc_array as $key => $doc_class) {

            $blob_doc_array["$key"]->__destruct();
            $blob_doc_array["$key"] = null;
            unset($blob_doc_array["$key"]);
        }

        if ($this->clean_immediately == true) {
            $this->gc_clearblobdoc_collected += gc_collect_cycles();
        }

        $blob_doc_array = null;
        unset($blob_doc_array);
    }

    // Count term frequency per document and store in array
    // store in term_statistics class -> occurances_per_document array
    //   the key is the ordinal_number and the value is the number of occurances
    public function get_term_frequency_per_document(&$inverted_list, &$blob_doc_array) {
        foreach ($blob_doc_array as $key => &$doc_class) {
            $this->get_term_frequency_per_document_one_doc_array($inverted_list, $doc_class);
        }

        //return $blob_doc_array;
    }

    public function get_term_frequency_per_document_one_doc_string(&$inverted_list, &$doc_class) {
        $cleaned_tokens = $doc_class->cleaned_tokens;

        // Collate tokens for this document in memory first
        foreach ($cleaned_tokens as $cleaned_token_key => $cleaned_token) {
            if (isset($inverted_list["$cleaned_token"])) {
                $term_statistics = $inverted_list["$cleaned_token"];
            } else {
                $term_statistics = new term_statistics($cleaned_token);
            }

            $id = $doc_class->id;

            $number_of_unique_documents_occurs_in = $term_statistics->number_of_unique_documents_occurs_in;
            $occurances_per_document_string = $term_statistics->occurances_per_document_string;
            $inv_list_unpacked = array();
            $found = false;
            $expected_array_size = $number_of_unique_documents_occurs_in * 2;

            $inv_list_unpacked = unpack($this->integer_keyword . "*", $occurances_per_document_string);
            // slow way to update a counter in an inverted list
            // It's bad because we have to search the whole array
            // If the array were in order of docID we could probably do a 
            // binary search
            for ($i = 1; $i < $expected_array_size; $i = $i + 2) {
                $key = $inv_list_unpacked[$i];
                $value = $inv_list_unpacked[$i + 1];

                if ($id == $key) {
                    $found = true;
                    $value += 1;
                    $inv_list_unpacked[$i + 1] = $value;
                    break;
                }
            }
            if ($found == false) {
                $nextpos = 1 + ($number_of_unique_documents_occurs_in * 2);
                $inv_list_unpacked[$nextpos] = $id;
                $inv_list_unpacked[$nextpos + 1] = 1;

                $number_of_unique_documents_occurs_in += 1;
            }
            # PHP 5.4+ 
            /*
              if (count($inv_list_unpacked) > 0) {
              $occurances_per_document_string = pack($this->integer_keyword . "*", ...$inv_list_unpacked);
              } */

            $occurances_per_document_string = "";
            foreach ($inv_list_unpacked as $key => $value) {
                $occurances_per_document_string .= pack($this->integer_keyword, $value);
            }

            $term_statistics->occurances_per_document_string = $occurances_per_document_string;
            $term_statistics->number_of_unique_documents_occurs_in = $number_of_unique_documents_occurs_in;

            $inverted_list["$cleaned_token"] = $term_statistics;

            foreach ($inv_list_unpacked as $key => $value) {
                $inv_list_unpacked[$key] = null;
                unset($inv_list_unpacked[$key]);
            }

            $inv_list_unpacked = null;
            unset($inv_list_unpacked);
        }


        //return $doc_class ;
    }

    public function get_term_frequency_per_document_one_doc_array(&$inverted_list, &$doc_class) {

        $cleaned_tokens = $doc_class->cleaned_tokens;

        // Collate tokens for this document in memory first
        foreach ($cleaned_tokens as $cleaned_token_key => $cleaned_token) {
            if (isset($inverted_list["$cleaned_token"])) {
                $term_statistics = $inverted_list["$cleaned_token"];
            } else {
                $term_statistics = new term_statistics($cleaned_token);
            }

            //$number_of_unique_documents_occurs_in = $term_statistics->number_of_unique_documents_occurs_in;

            $occurances_per_document_array = $term_statistics->occurances_per_document_array;
            $id = $doc_class->id;
            if (isset($occurances_per_document_array["$id"])) {
                $occurances_per_document_array["$id"] = $occurances_per_document_array["$id"] + 1;
            } else {
                $occurances_per_document_array["$id"] = 1;
                //$number_of_unique_documents_occurs_in += 1;
            }
            $term_statistics->occurances_per_document_array = $occurances_per_document_array;
            //$term_statistics->number_of_unique_documents_occurs_in = $number_of_unique_documents_occurs_in;

            $inverted_list["$cleaned_token"] = $term_statistics;
        }


        //return $doc_class ;
    }

    // From the extracted text stored in the doc_class
    //  Filter it and return as tokenised list
    public function get_content_terms_as_tokens(&$blob_doc_array) {

        $i = 0;
        foreach ($blob_doc_array as $id => $doc_class) {
            $this->get_content_terms_as_tokens_one_doc($doc_class);
        }

        //return $blob_doc_array;
    }

    public function get_content_terms_as_tokens_one_doc(&$doc_class) {
        $doc_headline = $doc_class->headline;
        $doc_text = $doc_class->text;
        $tokens = array();
        $tokens2 = array();
        $doc_complete = $doc_text . " " . $doc_headline;
        $tokens = explode(" ", $doc_complete);

        $doc_class->text = null;
        unset($doc_class->text);
        $cleaned_tokens = array();

        $excluded_tokens = array();
        
        
        $add_token = true;
        //var_dump($tokens);
        foreach ($tokens as $token_key => $token) {
            if (($token == "") || ($token == "\r") || ($token == "\n") || ($token == "\r\n") || (strlen($token) == 0)) {
                $add_token = false;
            }
            $add_token = true;

            $start_token = $token;

            $token = preg_replace("[\n\r]", "", $token);
            $token = preg_replace("/<[^>]*>/", "", $token);
            //Remove  punctuation (any symbols that are not letters or numbers)
            // Remove excess markup tags
            // how?
            $token = preg_replace("/[^a-zA-Z0-9-]+/", "", $token);

            $subtokens = preg_split("/-/", $token);
            $subtoken_count = count($subtokens);
            if ($subtoken_count > 1) {
                for ($j = 0; $j < $subtoken_count; $j++) {
                    $tokens2[] = $subtokens[$j];
                }
            }

            if (($token == "") || (strlen($token) == 0)) {
                $add_token = false;
            }

            //echo "adding to tokens2 " . $token . "\n";
            if ($add_token == true)
            {
               $tokens2[] = $token;
            }
            // Early cleanup
            //$tokens[$token_key] = null;
            //unset($tokens[$token_key]);
        }


        foreach ($tokens2 as $token_key => $token) {
            $add_token = true;
            $token = preg_replace("/-/", " ", $token);
            
            $token = trim($token);
            
            if (($token == "") || (strlen($token) == 0)) {
                $add_token = false;
            }
            // Consider how to token normalise - acronyms and hyphenated words
            // IMHO it's better to fold the entire text to lower rather than individual calls.
            // in terms of performance but I suppose we might lose information doing this before normalising
            // Case fold to lower case
            $token = strtolower($token);

            $end_token = $token;

            if ($add_token == true)
            {
               $tokens2[$token_key] = $token;
            } else {
               unset($tokens2[$token_key]);
            }
        }

        foreach ($tokens2 as $token_key => $token) {
            $add_token = true;
            //echo "start token was: " . $token . " end token was " . $token . " length " . strlen($token) .  "\n";
            if ($this->stoplist_file_specified == true) {
                // Exclude stopped words

                $excluded = $this->stoplist_file_manager->in_stoplist($token);
                if ($excluded == true) {
                    $add_token = false;
                }
            }

            if ($add_token == true) {
                $cleaned_tokens[] = $token;
            }
        }

        $doc_class->cleaned_tokens = $cleaned_tokens;

        $docid = $doc_class->docid;
        $id = $doc_class->id;
        if ($this->print_content_terms == true) {
            echo "\nDOCID: " . $docid . "\n";
            echo "ID: " . $id . "\n";
            echo "Content terms: \n";
            foreach ($cleaned_tokens as $cleaned_token_key => $cleaned_token) {
                echo $cleaned_token . "\n";
            }
        }

        if ($this->clean_immediately == true) {
            $this->gc_cleanedtokens_collected += gc_collect_cycles();
        }
    }

    // Parse the doc text and extract content into the doc_class object
    public function extract_doc_text_from_doc_array(&$blob_doc_array) {

        foreach ($blob_doc_array as $blob_doc_number => $doc_class) {
            $docid_in_doc = $this->get_data_in_tag_multiline($doc_class, "<DOCID>", "</DOCID>");
            $docid_in_doc = trim($docid_in_doc);
            $doc_class->docid = $docid_in_doc;
            //echo "docid is " . $docid_in_doc;
            $headline_in_doc = $this->get_data_in_tag_multiline($doc_class, "<HEADLINE>", "</HEADLINE>");
            $doc_class->headline = $headline_in_doc;
            //echo "headline is " . $headline_in_doc;
            $text_in_doc = $this->get_data_in_tag_multiline($doc_class, "<TEXT>", "</TEXT>");
            $doc_class->text = $text_in_doc;
            //echo "text is " . $text_in_doc;
        }
        //gc_collect_cycles();
        //return $blob_doc_array;
    }

    // Search for the <DOC> start and </DOC> end tag
    //// There are other ways to find the end of a DOC such as looking for the next
    //// <DOC> tag where no </DOC> is found but we ignore this for simplicities sake
    //// and assume our data file is consistent
    public function get_docs_from_blob($blob, $blob_start_pos, &$blob_doc_array) {

        //var_dump($blob);
        $blob_doc_array = array();
        $docs_found = 0;

        $stitch_blob = false;
        $found_doc = false;
        $blob_end_pos = 0;

        // Find all the docs within our blob            
        do {
            //$docs_found++;
            //echo "starting blob read from " . $blob_start_pos . "\n";
            // Get one document from file            
            list($doc_class, $found_whole_doc_status, $blob_end_pos, $start_doc_found, $end_doc_found) = $this->find_doc($blob, $blob_start_pos, "<DOC>", "</DOC>");


            if ($doc_class != null) {
                $ordinal_number = $doc_class->id;
                $blob_doc_array["$ordinal_number"] = $doc_class;
                $docs_found++;
                $found_doc = true;
            }

            // Move blob pointer forward
            $blob_start_pos = $blob_end_pos;

            // Start of blob no docs found - No doc in blob?
            if (($found_whole_doc_status == false) && ($docs_found == 0)) {
                // If blob is empty - we want to stitch with another blob
                $stitch_blob = true;
                break;
            }

            // End of blob after some docs found
            if (($found_whole_doc_status == false) && ($docs_found > 0)) {
                break;
            }
        } while ($found_whole_doc_status == true);

        return array($blob_end_pos, $found_doc, $stitch_blob);
    }

    // Unused
    public function count_docs_in_blob($blob, $start_tag, $end_tag) {
        return 0;
    }

    public function read_blob($handle, $length_to_read, &$blob) {

        $blob = fread($handle, $length_to_read);
        if ($blob === false) {
            die("file read error");
        }
        //return $blob;
    }

    public function increment_file_pointer($handle, $new_start_position) {
        $seek_status = false;
        if (!feof($handle)) {

            if ($this->verbose == true) {
                echo "Moving file seek position: " . $new_start_position . "\n";
            }

            $seek_status = fseek($handle, $new_start_position, SEEK_SET);

            //echo "seeking to " . $new_start_position . "\n";
            if ($seek_status !== 0) {
                if (feof($handle)) {
                    //echo "seeked to end of file\n";
                }
                die("seek failed - please investigate\n");
            }

            //echo "new_start_position" . $new_start_position;
        }

        return $seek_status;
    }

    // Extract data from within a set of tags
    public function get_data_in_tag_multiline($doc_class, $start_tag, $end_tag) {
        $tag_text = "";

        $doc_raw_text = $doc_class->raw_text;
        $found_whole_tag_status = false;
        $start_tag_start_position = 0;
        $start_tag_end_position = 0;
        $end_tag_start_position = 0;
        $end_tag_end_position = 0;
        $start_tag_found = false;
        $end_tag_found = false;

        // Locate the tags
        $start_tag_start_position = strpos($doc_raw_text, $start_tag, 0);




        if ($start_tag_start_position !== FALSE) {
            $start_tag_found = true;
        } else {
            //echo "No start tag\n";
        }

        if ($start_tag_found == true) {
            $start_tag_end_position = $start_tag_start_position + strlen($start_tag);
        }

        $end_tag_start_position = strpos($doc_raw_text, $end_tag, $start_tag_start_position);

        if ($end_tag_start_position !== FALSE) {
            $end_tag_found = true;
        } else {
            //echo "No end tag\n";
        }

        $end_tag_end_position = $end_tag_start_position + strlen($end_tag);

        if ($start_tag_found == true && $end_tag_found == true) {
            // DID WE FIND <DOC> </DOC> not </DOC> <DOC>
            if ($start_tag_end_position < $end_tag_start_position) {
                $found_whole_tag_status = true;
            }
        }

        /*
          //echo "Tag is: " . $start_tag;
          //echo "Tag is: " . $end_tag;

          echo "\nSTart tag start pos: " . $start_tag_start_position;
          echo "\nSTart tag end pos: " . $start_tag_end_position;
          echo "\nend tag start pos: " . $end_tag_end_position;
          echo "\nend tag end pos: " . $end_tag_end_position;
         * 
         */

        if ($found_whole_tag_status == true) {
            // Next read pointer should be after the closing tag <DOC>  CONTENT </DOC> 
            // Read between the <DOC> </DOC>
            $length = $end_tag_start_position - $start_tag_end_position;

            $tag_text = substr($doc_raw_text, $start_tag_end_position, $length);

            $found_whole_tag_status = true;
        } else {
            //echo "Didn't find start and end tag\n";
        }

        return $tag_text;
    }

    // Search for <DOC> tag start and end 
    public function find_doc($blob, $blob_start_pos, $start_tag, $end_tag) {
        $blob_end_pos = $blob_start_pos;
        $found_whole_doc_status = false;
        $doc_text = "";
        $start_doc_found = false;
        $end_doc_found = false;
        $relative_start_pos = FALSE;
        $relative_end_pos = FALSE;
        $doc_class = null;
        $start_tag_start_position = 0;
        $end_tag_start_position = 0;
        $start_tag_end_position = 0;
        $end_tag_end_position = 0;

        $start_tag_start_position = strpos($blob, $start_tag, $blob_start_pos);

        if ($start_tag_start_position !== FALSE) {
            $start_doc_found = true;
        } else {
            //echo "No start tag\n";
        }

        if ($start_doc_found == true) {
            $start_tag_end_position = $start_tag_start_position + strlen($start_tag);
        }

        $end_tag_start_position = strpos($blob, $end_tag, $start_tag_start_position);

        if ($end_tag_start_position !== FALSE) {
            $end_doc_found = true;
        } else {
            //echo "No end tag\n";
        }

        $end_tag_end_position = $end_tag_start_position + strlen($end_tag);

        if ($start_doc_found == true && $end_doc_found == true) {
            // DID WE FIND <DOC> </DOC> not </DOC> <DOC>
            if ($start_tag_end_position < $end_tag_start_position) {
                $found_whole_doc_status = true;
            }
        }

        if ($found_whole_doc_status == true) {
            //echo "found a doc in find_doc\n";
            // Next read pointer should be after the closing tag <DOC>  CONTENT </DOC> 
            $blob_end_pos = $end_tag_end_position;

            // Read between the <DOC> </DOC>
            $length = $end_tag_start_position - $start_tag_end_position;

            $doc_text = substr($blob, $start_tag_end_position, $length);
            $ordinal_number = $this->all_doc_number;

            $doc_class = new doc(array("id" => $ordinal_number, "raw_text" => $doc_text));
            $found_whole_doc_status = true;
            $this->all_doc_number = $this->all_doc_number + 1;
        } else {
            //echo "Didn't find start and end tag\n";
        }



        return array($doc_class, $found_whole_doc_status, $blob_end_pos, $start_doc_found, $end_doc_found);
    }

# Free code from: http://www.php.net/manual/en/features.commandline.php#86616

    function arguments($args) {
        $ret = array(
            'exec' => '',
            'options' => array(),
            'flags' => array(),
            'arguments' => array(),
        );

        $ret['exec'] = array_shift($args);

        while (($arg = array_shift($args)) != NULL) {
            // Is it a option? (prefixed with --)
            if (substr($arg, 0, 2) === '--') {
                $option = substr($arg, 2);

                // is it the syntax '--option=argument'?
                if (strpos($option, '=') !== FALSE)
                    array_push($ret['options'], explode('=', $option, 2));
                else
                    array_push($ret['options'], $option);

                continue;
            }

            // Is it a flag or a serial of flags? (prefixed with -)
            if (substr($arg, 0, 1) === '-') {
                for ($i = 1; isset($arg[$i]); $i++)
                    $ret['flags'][] = $arg[$i];

                continue;
            }


            // finally, it is not option, nor flag
            $ret['arguments'][] = $arg;
            continue;
        }
        return $ret;
    }

    //DEBUG CODE TO Work out memory usage
    public function get_content_terms_as_tokens_memory_usage_verus_actual(&$blob_doc_array) {



        //$tokens = array();
        //$cleaned_tokens = array();
        $i = 0;
        //echo "docs: " . count($blob_doc_array) . "\n";
        //echo "A" . memory_get_usage() . "\n";
        $total_estimated = 0;
        $total_estimated2 = 0;
        $total_used_memory_before = memory_get_usage();
        foreach ($blob_doc_array as $id => $doc_class) {
            $token_size = $this->get_content_terms_as_tokens_memory_usage_one_doc($doc_class);
            $token_size2 = $this->get_content_terms_as_tokens_memory_usage_one_doc_serialised($doc_class);

            $memory_before = memory_get_usage();
            $this->get_content_terms_as_tokens_one_doc($doc_class);
            $memory_after = memory_get_usage();
            $memory_used = $memory_after - $memory_before;

            $difference = $memory_used - $token_size;
            //echo " Difference is: " . $difference . "\n";
            $total_estimated += $token_size;
            $total_estimated2 += $token_size2;
        }
        //die();
        $total_used_memory_after = memory_get_usage();
        $total_used = $total_used_memory_after - $total_used_memory_before;

        $total_difference = $total_used - $total_estimated;
        $total_difference2 = $total_used - $total_estimated2;
        echo "total difference (multiplication method is: " . $total_difference . "\n";
        echo "total difference (serialisation method is: " . $total_difference2 . "\n";
        //var_dump($total);
        return $total;
        //return $blob_doc_array;
    }

    //DEBUG CODE TO Work out memory usage
    public function get_content_terms_as_tokens_memory_usage_one_doc(&$doc_class) {
        $doc_headline = $doc_class->headline;
        $doc_text = $doc_class->text;
        //echo "B" . memory_get_usage() . "\n";
        $tokens = array();
        //$doc_complete = $doc_headline . " " . $doc_text;
        $doc_complete = $doc_text . " " . $doc_headline;

        //echo strlen($doc_complete) . "\n";
        $tokens = explode(" ", $doc_complete);
        $token_size = 0;
        foreach ($tokens as $key => $value) {
            $token_size += strlen($value);
        }

        return $token_size;
    }

    //DEBUG CODE TO Work out memory usage
    public function get_content_terms_as_tokens_memory_usage_one_doc_serialised(&$doc_class) {
        $doc_headline = $doc_class->headline;
        $doc_text = $doc_class->text;
        //echo "B" . memory_get_usage() . "\n";
        $tokens = array();
        //$doc_complete = $doc_headline . " " . $doc_text;
        $doc_complete = $doc_text . " " . $doc_headline;

        //echo strlen($doc_complete) . "\n";
        $tokens = explode(" ", $doc_complete);
        $token_size = 0;


        // $mem = memory_get_usage();
        $token_size += strlen(serialize($tokens));

        //echo $token_size . "\n";//die();
        // Return the unserialized memory usage
        //$token_sze +=  memory_get_usage() - $mem;

        return $token_size;
    }

    //DEBUG CODE TO Work out memory usage
    public function get_content_terms_as_tokens_memory_usage(&$blob_doc_array) {



        //$tokens = array();
        //$cleaned_tokens = array();
        $i = 0;
        //echo "docs: " . count($blob_doc_array) . "\n";
        //echo "A" . memory_get_usage() . "\n";
        $total = 0;
        foreach ($blob_doc_array as $id => $doc_class) {

            $token_size = $this->get_content_terms_as_tokens_memory_usage_one_doc($doc_class);
            $total += $token_size;
        }

        //var_dump($total);
        return $total;
        //return $blob_doc_array;
    }

    // Memory inefficient
    //  at the point it extracts the tokens for 2000 documents
    //  the memory here will spike
    // However there are less hashmap merges so this may be better on time 
    // (if it completes)
    public function parse_data_all_docs_at_a_time($collection_to_index) {

        $handle = fopen($collection_to_index, "r") or die("can't open index file " . $collection_to_index);

        // Relative read position in the blob
        $blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        $file_start_pos = 0;

        $all_doc_array = array();
        //$all_doc_number = 0;
        //      $this->utility->zero_map_file();

        $blob = "";
        $blob_doc_array = array();

        $stitch_blob = false;
        $stitch_window_start_offset = null;
        $new_blob = "";
        $length_to_read = $this->file_blob_read_length;
        $inverted_list = array();

        if ($this->verbose == true) {
            echo "Reading file blobs of maximum length: " . $length_to_read . "\n";
        }

        $write_out_inverted_list_time = 0;
        $docs_processed = 0;
        $docs_processed_last = 0;

        $arr = fstat($handle);
        //var_dump($arr);die();
        $filesize = $arr["size"];
        //echo "file length" . $filesize . "\n";

        $i = 0;
        while (!feof($handle)) {

            if ($this->verbose == true) {
                echo "Memory usage before blob processed: " . memory_get_usage() . " \n";
            }
            $i++;
            $blob_doc_array = array();

            // DO NOT DELETE
            $blob_start_pos = 0;


            list($blob, $length_to_read) = $this->get_next_blob($handle, $filesize, $file_start_pos, $length_to_read, $stitch_blob);

            list($blob_end_pos, $found_doc, $stitch_blob) = $this->get_docs_from_blob($blob, $blob_start_pos, $blob_doc_array);

            $blob_start_pos = $blob_end_pos;

            $docs_process_last = $docs_processed;
            $docs_processed += count($blob_doc_array);

            $file_start_pos = $this->get_next_file_pointer($file_start_pos, $blob_start_pos, $stitch_blob);

            $success = $this->increment_file_pointer($handle, $file_start_pos);

            if ($found_doc == true) {

                $blob = null;
                unset($blob);
                if ($this->clean_immediately == true) {
                    $this->gc_blob_collected += gc_collect_cycles();
                }
                $this->extract_doc_text_from_doc_array($blob_doc_array);

                $this->clear_raw_text($blob_doc_array);

                $this->get_content_terms_as_tokens($blob_doc_array);

                // Clear text
                $this->clear_text($blob_doc_array);
                // Clear tokens?
                $this->clear_tokens($blob_doc_array);
                $this->map_file_manager->add_mappings($blob_doc_array);
                $this->get_term_frequency_per_document($inverted_list, $blob_doc_array);

                if ($this->clean_immediately == false) {
                    // Garbage collect after processing each blob
                    $this->gc_doc_collected += gc_collect_cycles();
                }
            }
            if ($this->verbose == true) {
                echo "\ndocs processed " . $docs_processed . "\n";
            }
            $this->clear_cleaned_tokens($blob_doc_array);

            if ($this->verbose == true) {
                echo "Memory usage after blob docs processed: " . memory_get_usage() . " \n";
            }

            $this->clear_blob_doc_array($blob_doc_array);
        }

        $this->get_term_frequency_by_unique_document_count($inverted_list);

        $this->utility->write_out_inverted_list_array_struct_to_disk_variable_length_blocks_without_write_buffering($inverted_list);

        //$blob_doc_array = $this->clear_blob_doc_array($blob_doc_array);

        fclose($handle);
    }

}

// Document format
/*
 * <DOC>
  <DOCNO> LA010189-0001 </DOCNO>
  <DOCID> ... </DOCID>
  <DATE> ... </DATE>
  <SECTION> ... </SECTION>
  <LENGTH> ... </LENGTH>
  <HEADLINE>
  The article headline.
  </HEADLINE>
  ...
  <TEXT>
  The text content of the document.
  </TEXT>
  </DOC>
 */
