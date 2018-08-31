<?php

require_once("doc_class.php");
require_once("map_item.php");

class map_file_manager {

    private $map_filename = "map";
    private $map_array = array();
    private $map_handle = null;
    private $immediate_map_rewrites = false;

    public function __construct($arr) {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    public function get_mapping($ordinal_number) {
        $doc_class = null;
        
        $map_array = $this -> map_array;

        if (isset($map_array["$ordinal_number"])) {
            $doc_class = $map_array["$ordinal_number"];
        }
        return $doc_class;
    }

    // We create an array of document objects - stating the id and DOCID.
    public function load_map() {
        $map_filename = $this->map_filename;
        $map_text = file_get_contents($map_filename);
        // Map contains docid,array_id (or unique id whatever u want to use)
        $line_by_line = explode("\n", $map_text);

        $map_array = array();

        foreach ($line_by_line as $line) {
            if ($line == "") {
                continue;
            }
            $args = explode(",", $line);

            $doc_id = $args[0];
            $ordinal_number = $args[1];

            $doc_class = new doc(array("id" => $ordinal_number, "docid" => $doc_id));

            $map_array["$ordinal_number"] = $doc_class;
        }

        $this->map_array = $map_array;
    }

    public function zero_map_file() {
        $map_filename = $this->map_filename;
        $map_text = "";
        file_put_contents($map_filename, $map_text);

        return true;
    }

    public function initialise_map_file() {
        file_put_contents($this->map_filename, "");
    }

    public function add_mappings($blob_doc_array) {
        
        $map_array = $this -> map_array;
        foreach ($blob_doc_array as $key => $blob_doc) {
            $id = $blob_doc->id;
            $docid = $blob_doc->docid;

            if (!isset($map_array["$id"])) {
                $new_map_item = new map_item(array("id" => $id, "docid" => $docid));
                $map_array["$id"] = $new_map_item;
            } else {
                //echo "already exists in map";
            }
        }

        //Writes lexicon every time we update the lex very bad :P
        if ($this->immediate_map_rewrites == true) {
            $this->write_map($map_array);
        }
        
        $this -> map_array = $map_array;
    }

    public function write_map() {
        $this->close_map_file_real();
        $this->zero_map_file();
        $map_array = $this->map_array;

        $map_handle = $this->open_map_file();
        fseek($map_handle, 0, SEEK_SET);
        $map_text = "";
        foreach ($map_array as $key => $map_item) {
            $map_text .= $this->create_map_item_text($map_item);
        }
        fwrite($map_handle, $map_text);
        $this->close_map_file();
    }

    public function write_whole_map_file() {
        $map_text = "";
        $map_filename = $this->map_filename;
        $map_array = $this->map_array;
        foreach ($map_array as $map_item) {
            $map_text .= $this->create_map_item_text($map_item);
        }
        file_put_contents($map_filename, $map_text);

        return true;
    }

    public function write_map_item($map_handle, $map_item) {
        $status = false;
        $text = "";

        $docid = $map_item->docid;
        $ordinal_number = $map_item->id;
        $map_text = $this->create_map_item_text();

        fwrite($map_handle, $map_text);
        $status = true;


        return $status;
    }

    public function create_map_item_text($map_item) {
        $map_text = "";

        $docid = $map_item->docid;
        $ordinal_number = $map_item->id;
        $map_text = $docid . "," . $ordinal_number . "\n";

        return $map_text;
    }

    public function open_map_file() {
        $map_filename = $this->map_filename;

        $map_handle = $this->map_handle;
        if ($map_handle == null) {

            $map_handle = fopen($map_filename, "c+b");

            $this->map_handle = $map_handle;
        }
        return $map_handle;
    }

    public function close_map_file() {
        $map_handle = $this->map_handle;
        if ($this->map_handle != null) {
            fflush($map_handle);
        }
        $this->close_map_file_real();
    }

    public function close_map_file_real() {
        $map_handle = $this->map_handle;
        if ($map_handle !== null) {
            fclose($map_handle);
        }
        $this->map_handle = null;
    }

    public function __destruct() {
    }
    

    // Unused
    public function write_whole_map_file_append($map_items) {
        $map_text = "";
        $map_filename = $this->map_filename;
        foreach ($map_items as $map_item) {
            $map_text .= $this->create_map_item_text($map_item);
        }
        file_put_contents($map_filename, $map_text, FILE_APPEND);

        return true;
    }

}
