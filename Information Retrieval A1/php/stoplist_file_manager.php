<?php

class stoplist_file_manager {

    public $stoplist_filename = "";
    public $stoplist_array = array();
    public $stoplist_array2 = array();

    public function __construct($arr) {
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }

    public function load_stoplist() {
        if ($this->stoplist_filename != null) {
            $stoplist_file = $this->stoplist_filename;
            $stoplist = file_get_contents($stoplist_file);
            $stoplist_array = explode("\n", $stoplist);

            // as 1D array
            $this->stoplist_array2 = $stoplist_array;

            // as 2D array
            foreach ($stoplist_array as $key => $stopword) {
                $this->stoplist_array["$stopword"] = $stopword;
            }
        }
    }

    public function in_stoplist($term) {
        $status = false;
        if (isset($this->stoplist_array["$term"])) {
            $status = true;
        }
        return $status;
    }

    public function in_stoplist2($term) {
        $status = false;
        if (in_array($term, $this->stoplist_array2)) {
            $status = true;
        }
        return $status;
    }

}
