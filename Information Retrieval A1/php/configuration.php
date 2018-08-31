<?php

class configuration
{
    public $config_array = array();
   public function __construct($arr)
   {
       foreach ($arr as $key => $value)
       {
           $this -> config_array[$key] = $value;
       }
   }
       
       public function get_config_array()
       {
           return $this -> config_array;
       }
    
   
}

