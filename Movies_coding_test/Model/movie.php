<?php

namespace moviesite;

class movie {
   private $Provider = "";
   private $ID = "";
   public $hasDetails = false;
   private $Title = "";
   private $Year = "";
   public $Rated = "";
   public $Released = "";
   public $Genre = "";
   public $Director = "";
   public $Writer = "";
   public $Actors = "";
   public $Plot = "";
   public $Language = "";
   public $Country = "";
   public $Awards = "";
   public $Poster = "";
   public $Metascore = "";
   public $Rating = "";
   public $Votes = "";
   public $Type = "";
   private $Price = null;
   public $Detail = "";
   public $datemodified = null;

    function __construct($array) {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    function set_detail($detail) {
        $this->detail = $detail;
    }

    function set_details($details) {
        foreach ($details as $key => $detail) {
            $this->$key = $detail;
        }
    }

    // Incomplete
    function set_details_using_object($movie)
    {
        
    }
    
    function get_display_name()
    {
        return $this -> Title . " (" . $this -> Year . ")";
    }
    
    function get_price()
    {
        $price = $this -> Price;
        return $price;
    }
        
    function get_display_price()
    {
        $price = null;
        if ($this -> Price != null)
        {
            $price = "$" . $this -> Price;
        }
        return $price;
    }
    
    
    function get_title()
    {
        $title = $this -> Title;
        return $title;
    }
    

    function get_year()
    {
        $year = $this -> Year;
        return $year;
    }
    
    function get_poster()
    {
        $poster = $this -> Poster;
        return $poster;
    }
    
    function get_ID()
    {
        $ID = $this -> ID;
        
        return $ID;
    }
    
    function get_detail()
    {
        $Detail = $this -> Detail;
        
        return $Detail;
    }
    
    function get_provider()
    {
        $provider = $this -> Provider;
        return $provider;
    }

/* cinemaworld movie list
 * 
 * array(1) { ["Movies"]=> array(7) { 
 * [0]=> array(5) { ["Title"]=> string(34) "Star Wars: Episode IV - A New Hope" ["Year"]=> string(4) "1977" ["ID"]=> string(9) "cw0076759" ["Type"]=> string(5) "movie" ["Poster"]=> string(128) "http://ia.media-imdb.com/images/M/MV5BOTIyMDY2NGQtOGJjNi00OTk4LWFhMDgtYmE3M2NiYzM0YTVmXkEyXkFqcGdeQXVyNTU1NTcwOTk@._V1_SX300.jpg" } 
 * [1]=> array(5) { ["Title"]=> string(46) "Star Wars: Episode V - The Empire Strikes Back" ["Year"]=> string(4) "1980" ["ID"]=> string(9) "cw0080684" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BMjE2MzQwMTgxN15BMl5BanBnXkFtZTcwMDQzNjk2OQ@@._V1_SX300.jpg" } 
 * [2]=> array(5) { ["Title"]=> string(42) "Star Wars: Episode VI - Return of the Jedi" ["Year"]=> string(4) "1983" ["ID"]=> string(9) "cw0086190" ["Type"]=> string(5) "movie" ["Poster"]=> string(131) "http://ia.media-imdb.com/images/M/MV5BMTQ0MzI1NjYwOF5BMl5BanBnXkFtZTgwODU3NDU2MTE@._V1._CR93,97,1209,1861_SX89_AL_.jpg_V1_SX300.jpg" } 
 * [3]=> array(5) { ["Title"]=> string(28) "Star Wars: The Force Awakens" ["Year"]=> string(4) "2015" ["ID"]=> string(9) "cw2488496" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BOTAzODEzNDAzMl5BMl5BanBnXkFtZTgwMDU1MTgzNzE@._V1_SX300.jpg" } 
 * [4]=> array(5) { ["Title"]=> string(41) "Star Wars: Episode I - The Phantom Menace" ["Year"]=> string(4) "1999" ["ID"]=> string(9) "cw0120915" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BMTQ4NjEwNDA2Nl5BMl5BanBnXkFtZTcwNDUyNDQzNw@@._V1_SX300.jpg" } 
 * [5]=> array(5) { ["Title"]=> string(44) "Star Wars: Episode III - Revenge of the Sith" ["Year"]=> string(4) "2005" ["ID"]=> string(9) "cw0121766" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BNTc4MTc3NTQ5OF5BMl5BanBnXkFtZTcwOTg0NjI4NA@@._V1_SX300.jpg" } 
 * [6]=> array(5) { ["Title"]=> string(44) "Star Wars: Episode II - Attack of the Clones" ["Year"]=> string(4) "2002" ["ID"]=> string(9) "cw0121765" ["Type"]=> string(5) "movie" ["Poster"]=> string(92) "http://ia.media-imdb.com/images/M/MV5BMTY5MjI5NTIwNl5BMl5BanBnXkFtZTYwMTM1Njg2._V1_SX300.jpg" } } } 
 * 
 * filmworld movie list
 * array(1) { ["Movies"]=> array(6) { [0]=> array(5) { ["Title"]=> string(34) "Star Wars: Episode IV - A New Hope" ["Year"]=> string(4) "1977" ["ID"]=> string(9) "fw0076759" ["Type"]=> string(5) "movie" ["Poster"]=> string(128) "http://ia.media-imdb.com/images/M/MV5BOTIyMDY2NGQtOGJjNi00OTk4LWFhMDgtYmE3M2NiYzM0YTVmXkEyXkFqcGdeQXVyNTU1NTfwOTk@._V1_SX300.jpg" } [1]=> array(5) { ["Title"]=> string(46) "Star Wars: Episode V - The Empire Strikes Back" ["Year"]=> string(4) "1980" ["ID"]=> string(9) "fw0080684" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BMjE2MzQwMTgxN15BMl5BanBnXkFtZTfwMDQzNjk2OQ@@._V1_SX300.jpg" } [2]=> array(5) { ["Title"]=> string(42) "Star Wars: Episode VI - Return of the Jedi" ["Year"]=> string(4) "1983" ["ID"]=> string(9) "fw0086190" ["Type"]=> string(5) "movie" ["Poster"]=> string(131) "http://ia.media-imdb.com/images/M/MV5BMTQ0MzI1NjYwOF5BMl5BanBnXkFtZTgwODU3NDU2MTE@._V1._CR93,97,1209,1861_SX89_AL_.jpg_V1_SX300.jpg" } [3]=> array(5) { ["Title"]=> string(41) "Star Wars: Episode I - The Phantom Menace" ["Year"]=> string(4) "1999" ["ID"]=> string(9) "fw0120915" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BMTQ4NjEwNDA2Nl5BMl5BanBnXkFtZTfwNDUyNDQzNw@@._V1_SX300.jpg" } [4]=> array(5) { ["Title"]=> string(44) "Star Wars: Episode III - Revenge of the Sith" ["Year"]=> string(4) "2005" ["ID"]=> string(9) "fw0121766" ["Type"]=> string(5) "movie" ["Poster"]=> string(96) "http://ia.media-imdb.com/images/M/MV5BNTc4MTc3NTQ5OF5BMl5BanBnXkFtZTfwOTg0NjI4NA@@._V1_SX300.jpg" } [5]=> array(5) { ["Title"]=> string(44) "Star Wars: Episode II - Attack of the Clones" ["Year"]=> string(4) "2002" ["ID"]=> string(9) "fw0121765" ["Type"]=> string(5) "movie" ["Poster"]=> string(92) "http://ia.media-imdb.com/images/M/MV5BMTY5MjI5NTIwNl5BMl5BanBnXkFtZTYwMTM1Njg2._V1_SX300.jpg" } } } 
 * 
 * cinemaworld movie details
 * array(20) { ["Title"]=> string(34) "Star Wars: Episode IV - A New Hope" ["Year"]=> string(4) "1977" ["Rated"]=> string(2) "PG" ["Released"]=> string(11) "25 May 1977" ["Runtime"]=> string(7) "121 min" ["Genre"]=> string(26) "Action, Adventure, Fantasy" ["Director"]=> string(12) "George Lucas" ["Writer"]=> string(12) "George Lucas" ["Actors"]=> string(56) "Mark Hamill, Harrison Ford, Carrie Fisher, Peter Cushing" ["Plot"]=> string(230) "Luke Skywalker joins forces with a Jedi Knight, a cocky pilot, a wookiee and two droids to save the galaxy from the Empire's world-destroying battle-station, while also attempting to rescue Princess Leia from the evil Darth Vader." ["Language"]=> string(7) "English" ["Country"]=> string(3) "USA" ["Awards"]=> string(47) "Won 6 Oscars. Another 48 wins & 28 nominations." ["Poster"]=> string(128) "http://ia.media-imdb.com/images/M/MV5BOTIyMDY2NGQtOGJjNi00OTk4LWFhMDgtYmE3M2NiYzM0YTVmXkEyXkFqcGdeQXVyNTU1NTcwOTk@._V1_SX300.jpg" ["Metascore"]=> string(2) "92" ["Rating"]=> string(3) "8.7" ["Votes"]=> string(7) "915,459" ["ID"]=> string(9) "cw0076759" ["Type"]=> string(5) "movie" ["Price"]=> string(5) "123.5" } 
 * 
 * filmworld movie details
 * array(19) { ["Title"]=> string(34) "Star Wars: Episode IV - A New Hope" ["Year"]=> string(4) "1977" ["Rated"]=> string(2) "PG" ["Released"]=> string(11) "25 May 1977" ["Runtime"]=> string(7) "121 min" ["Genre"]=> string(26) "Action, Adventure, Fantasy" ["Director"]=> string(12) "George Lucas" ["Writer"]=> string(12) "George Lucas" ["Actors"]=> string(56) "Mark Hamill, Harrison Ford, Carrie Fisher, Peter Cushing" ["Plot"]=> string(230) "Luke Skywalker joins forces with a Jedi Knight, a cocky pilot, a wookiee and two droids to save the galaxy from the Empire's world-destroying battle-station, while also attempting to rescue Princess Leia from the evil Darth Vader." ["Language"]=> string(7) "English" ["Country"]=> string(3) "USA" ["Poster"]=> string(128) "http://ia.media-imdb.com/images/M/MV5BOTIyMDY2NGQtOGJjNi00OTk4LWFhMDgtYmE3M2NiYzM0YTVmXkEyXkFqcGdeQXVyNTU1NTfwOTk@._V1_SX300.jpg" ["Metascore"]=> string(2) "92" ["Rating"]=> string(3) "8.7" ["Votes"]=> string(7) "915,459" ["ID"]=> string(9) "fw0076759" ["Type"]=> string(5) "movie" ["Price"]=> string(4) "29.5" } 
 */

}
