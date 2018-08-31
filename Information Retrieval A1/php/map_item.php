<?php

class map_item {
    public $docid = "";
    public $id = "";
    public function __construct($arr)
    {
        foreach ($arr as $key => $value)
        {
            $this -> $key = $value;
        }
    }
}
