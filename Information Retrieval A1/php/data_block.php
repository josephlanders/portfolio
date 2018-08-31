<?php

class data_block
{
    public $access_frequency = 0;
    public $data_block_handle = null;
    public $file_offset = null;
    public $term = "";
    public $disk_block_size = null;
    public $memory_block_size = null;
    public $content_size = null;
    public $data = "";
    
    public $header = "";
    public $inverted_list = "";
    public function __construct($arr)
    {
        foreach ($arr as $key => $value)
        {
           $this -> $key = $value;
        }        
    }
}
