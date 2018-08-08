<?php

/* s3163776@student.rmit.edu.au
 * Joseph Landers
 * ISYS1078/1079 Assignment 1
 * 
 * This is the code that searches the indexed data files for term occurances by term entered
 */

require_once "doc_class.php";
require_once "term_statistics.php";
require_once "utility.php";
require_once "map_file_manager.php";
require_once "configuration.php";

class searcher {

    // an array of class term_statistics
    // the key to this array is the "term" name since this is unique 
    // and storable as the key
    public $lexicon_filename = "";
    public $map_filename = "";
    public $invlists_filename = "";
    private $inverted_list = array();
    private $all_doc_array = array();
    private $utility = null;
    private $compression = false;
    private $measure_time = false;
    public $query_terms_array = array();
    public $lexicon_file_manager = null;
    public $invlists_file_manager = null;
    public $map_manager = null;
    public $verbose = false;

    public function __construct($configuration) {
        if ($configuration !== null)
        {
            
        $config_array = $configuration -> get_config_array();
        foreach ($config_array as $key => $value)
        {
            $this -> $key = $value;
        }
        
        }
    }
    
    public function parse_arguments()
    {
                $ret = $this->arguments($_SERVER['argv']);

        $arg_count = count($ret["arguments"]);

        $this -> lexicon_filename = $ret["arguments"][0];

        $this -> invlists_filename = $ret["arguments"][1];

        $this -> map_filename = $ret["arguments"][2];

        $query_terms_array = array();

        for ($i = 3; $i < $arg_count; $i++) {
            $query_terms_array[] = $ret["arguments"][$i];
        }
        
        $this -> query_terms_array = $query_terms_array;

        foreach ($ret["flags"] as $key => $flags) {

            if ($flags[0] == "p") {
                $this->print_content_terms = true;
            }

            if ($flags[0] == "c") {
                $this->compression = true;
            }


            if ($flags[0] == "t") {
                $this->measure_time = true;
            }
            
            if ($flags[0] == "x") {
                $this->verbose = true;
            }
        }
        

    }
    
    public function initialise()
    {
        $config_array = array("measure_time" => $this->measure_time);
                    
        $this -> configuration = new configuration($config_array);

        $this -> lexicon_file_manager = new lexicon_file_manager(array("lexicon_filename" => $this -> lexicon_filename,
            "configuration" => $this -> configuration));
        $this -> lexicon_file_manager->load_lexicon();

        $this -> map_file_manager = new map_file_manager(array("map_filename" => $this -> map_filename, "configuration" => $this -> configuration));
        $this -> map_file_manager->load_map();


        $this -> invlists_manager = new invlists_manager(array("invlists_filename" => $this -> invlists_filename,
            "lexicon_file_manager" => $this -> lexicon_file_manager,
            "configuration" => $this -> configuration));

        $this->utility = new utility(array("configuration" => $this -> configuration,
            "invlists_file_manager" => $this -> invlists_file_manager,
            "map_file_manager" => $this -> map_file_manager,
            "lexicon_file_manager" => $this -> lexicon_file_manager));

    }
    
    public function start_processing()
    {
        $query_terms_array = $this -> query_terms_array;
        $start = microtime(true);
        foreach ($query_terms_array as $query_term) {
            $term_statistics = $this -> search($query_term);
        
            if ($term_statistics != null)
            {
                echo $query_term . "\n";
                echo $term_statistics->number_of_unique_documents_occurs_in . "\n";

                $occurances_per_document_array = $term_statistics->occurances_per_document_array;
                   
                foreach ($occurances_per_document_array as $ordinal_number => $occurances_per_document) {
                    $doc = $this -> map_file_manager -> get_mapping($ordinal_number);
                         
                    if ($doc != null)
                    {
                           
                    echo $doc->docid . " " . $occurances_per_document . "\n";
                    } else {
                        echo "error retrieving doc from map";
                    }
                } 
            }else {
                    echo "Search term not found: " . $query_term . "\n";
                }
        }
        
        
        $end = microtime(true);
        $time_taken = $end - $start;
        if ($this->measure_time == true) {
            echo "\ntime taken: " . $time_taken . " seconds";
        }
    }
    
    public function search($query_term)
    {
        $query_term = preg_replace("/-/", " ", $query_term);
        $term_statistics = null;
            $this -> lexicon_file_manager->read_lexicon_to_memory();
            $lexicon_item = $this -> lexicon_file_manager->get_lexicon_item_from_lexicon($query_term);
            if ($lexicon_item != null) {
                $term_statistics = $this -> invlists_manager->get_inverted_index_from_disk($lexicon_item);
                
                if ($this -> verbose == true)
                {
                   var_dump($term_statistics);
                }

            } else {
                echo "not in lexicon: " . $query_term . "\n";
            }
            
       return $term_statistics;
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
}
