<?php

class doc {
    public $raw_text = "";
    public $text = "";
    public $docid = "";
    public $id = 0;
    public $headline = "";
    public $tokens = array();
    public $cleaned_tokens = array();

    public function __construct($array) {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __destruct() {
        if (isset($this->raw_text)) {
            $this -> raw_text = null;
            unset($this->raw_text);
        }
        if (isset($this->text)) {
            $this -> text = null;
            unset($this->text);
        }

        if (isset($this->tokens)) {

            foreach ($this->tokens as $token_key => $token) {
                $this->tokens[$token_key] = null;
                unset($this->tokens[$token_key]);
            }

            $this->tokens = null;
            unset($this->tokens);
        }

        if (isset($this->cleaned_tokens)) {

            foreach ($this->cleaned_tokens as $cleaned_token_key => $cleaned_token) {
                $this->cleaned_tokens[$cleaned_token_key] = null;
                unset($this->cleaned_tokens[$cleaned_token_key]);
            }

            $this->cleaned_tokens = null;
            unset($this->cleaned_tokens);
        }

        unset($this->headline);

        unset($this->docid);
        
        unset($this);

        //gc_collect_cycles();
    }

}
