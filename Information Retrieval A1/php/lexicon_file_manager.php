<?php

require_once("lexicon_class.php");

class lexicon_file_manager {

    public $lexicon_filename = "lexicon";
    public $lexicon_array = array();
    private $lexicon_handle = null;
    public $immediate_lexicon_rewrites = false;

    public function __construct($arr) {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    public function initialise_lexicon_file() {
        file_put_contents($this->lexicon_filename, "");
    }

    public function load_lexicon() {

        $this->lexicon_array = $this->read_lexicon_to_memory();

        $status = false;
        if ($this->lexicon_array == array()) {
            return false;
        } else {
            return true;
        }
        return $status;
    }
    
    public function create_lexicon_item_text($lexicon_item)
    {
        $text = "";
        $text .= $lexicon_item->term . "," . $lexicon_item->file_offset . ","
                . $lexicon_item->disk_block_size . "," . $lexicon_item->content_size . "\n";
        
        return $text;
    }

    public function write_lexicon_item($lexicon_handle, $lexicon_item) {
        $status = false;
        $text = $this ->create_lexicon_item_text($lexicon_item);
        fwrite($lexicon_handle, $text);
        $status = true;


        return $status;
    }

    public function in_lexicon($search_term) {
        $in_lexicon = false;

        $lexicon_array = $this->lexicon_array;

        if (isset($lexicon_array["$search_term"])) {
            $in_lexicon = true;
        }

        return $in_lexicon;
    }

    public function get_term_file_offset_from_lexicon($term) {
        $lexicon_item = $this->get_lexicon_item_from_lexicon($term);

        $offset = $lexicon_item->file_offset;
        return $offset;
    }

    public function get_lexicon_item_from_lexicon($search_term) {
        $lexicon_array = $this->lexicon_array;

        $lexicon_item = null;

        if (isset($lexicon_array["$search_term"])) {
            $lexicon_item = $lexicon_array["$search_term"];
        }
        
        return $lexicon_item;
    }

    public function read_lexicon_to_memory() {
        $lexicon_array = array();
        $lexicon_filename = $this->lexicon_filename;

        $lex_text = file_get_contents($lexicon_filename);

        if ($lex_text !== FALSE) {
            // Lexicon contains term,lookup_reference   
            // - in this case the term and lookup_reference are the same
            $line_by_line = explode("\n", $lex_text);

            foreach ($line_by_line as $line) {
                $args = explode(",", $line);

                // Ignore incomplete lines
                if (count($args) == 4) {


                    $term = $args[0];
                    $file_offset = $args[1];
                    $block_size = $args[2];
                    $content_size = $args[3];

                    $lexicon_array["$term"] = new lexicon(array("term" => $term, "file_offset" => $file_offset,
                        "disk_block_size" => $block_size,
                        "content_size" => $content_size));
                }
            }
        }
        if (strlen($lex_text == 0)) {
            //echo "Lexicon file is empty: " . $lexicon_filename . " \n";
        }
        
        return $lexicon_array;
    }

    public function update_lexicon_item($lexicon_item) {
        $lexicon_array = $this->lexicon_array;
        if ($lexicon_array == array()) {
            $lexicon_array = $this->read_lexicon_to_memory();
        }

        $term = $lexicon_item->term;
        $lexicon_array["$term"] = $lexicon_item;
        $this->lexicon_array = $lexicon_array;

        //Writes lexicon every time we update the lex very bad :P
        if ($this -> immediate_lexicon_rewrites == true)
        {
           $this->write_lexicon($lexicon_array);
        }
    }

    public function add_to_lexicon($lexicon_item) {
        $lexicon_array = $this->lexicon_array;
       
        if ($lexicon_array == array()) {
            $lexicon_array = $this->read_lexicon_to_memory();
        }
        $term = $lexicon_item->term;
        $lexicon_array["$term"] = $lexicon_item;
        $this->lexicon_array = $lexicon_array;
        
        //Writes lexicon every time we update the lex very bad :P
        if ($this -> immediate_lexicon_rewrites == true)
        {
           $this->write_lexicon($lexicon_array);
        }
    }

    public function zero_lexicon_file() {
        $lexicon_filename = $this->lexicon_filename;
        $lex_text = "";
        file_put_contents($lexicon_filename, $lex_text);

        return true;
    }

    public function write_lexicon() {
        $this->close_lexicon_file_real();
        $this -> zero_lexicon_file();
        $lexicon_array = $this->lexicon_array;

        $lexicon_handle = $this->open_lexicon_file();
        fseek($lexicon_handle, 0, SEEK_SET);
        $text = "";
        foreach ($lexicon_array as $key => $lexicon_item) {
            $text .= $this ->create_lexicon_item_text($lexicon_item);
        }
        fwrite($lexicon_handle, $text);
        $this->close_lexicon_file();
        $this->close_lexicon_file_real();
    }

    public function open_lexicon_file() {
        $lexicon_filename = $this->lexicon_filename;

        $lexicon_handle = $this->lexicon_handle;
        if ($lexicon_handle == null) {

            $lexicon_handle = fopen($lexicon_filename, "c+b");

            $this->lexicon_handle = $lexicon_handle;
        }
        return $lexicon_handle;
    }

    public function close_lexicon_file() {
        $lexicon_handle = $this->lexicon_handle;
        if ($lexicon_handle !== null)
        {
           fflush($lexicon_handle);
        }
    }

    public function close_lexicon_file_real() {
        $lexicon_handle = $this->lexicon_handle;
        if ($this->lexicon_handle !== null) {
            fclose($lexicon_handle);
            $this -> lexicon_handle = null;
        }
    }
    
    public function get_lexicon()
    {
        return $this -> lexicon_array;
    }

    public function __destruct() {
    }

}
