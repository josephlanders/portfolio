<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/index.php");

$movie_detail_page =  new movie_detail_page();

class movie_detail_page
{
    
    public function __construct()
    {
        $moviethingy = new moviethingy();
        $moviethingy->routing("movie_detail");
    }
}    
