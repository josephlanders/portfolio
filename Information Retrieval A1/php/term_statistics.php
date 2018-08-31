<?php

class term_statistics {

    public $term = "";
    public $number_of_unique_documents_occurs_in = 0;
    public $occurances_per_document_array = array();
    public $occurances_per_document_string = "";
    public $length_of_inv_list = 0;

    public function __construct($term) {
        $this->term = $term;
    }

    public function __destruct() {
        if (isset($this->occurances_per_document_array)) {
            foreach ($this->occurances_per_document_array as $key => $value) {
                $this->occurances_per_document_array[$key] = null;
                unset($this->occurances_per_document_array[$key]);
            }

            $this->occurances_per_document_array = null;
            unset($this->occurances_per_document_array);
        }

        unset($this);
    }

}
