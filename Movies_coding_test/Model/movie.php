<?php

namespace moviesite;

/**
 * Description of movie
 *
 * @author z
 */
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



}
