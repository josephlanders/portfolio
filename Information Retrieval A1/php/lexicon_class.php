<?php

class lexicon
{
    public $term = "";    
    public $file_offset = null;
    public $disk_block_size = null;
    public $memory_block_size = null;
    public $content_size = null;
    public function __construct($array_init) {
        foreach ($array_init as $key => $value)
        {
            $this -> $key = $value;
        }
    }
    
}

