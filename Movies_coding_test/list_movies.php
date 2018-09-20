<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/index.php");

$list_movies_page =  new list_movies_page();

class list_movies_page
{    
    public function __construct()
    {
        $moviethingy = new moviethingy();
        $moviethingy->routing("list_movies");
    }
}    
?>