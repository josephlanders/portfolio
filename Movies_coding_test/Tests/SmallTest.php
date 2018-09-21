<?php

use PHPUnit\Framework\TestCase;

class SmallTest extends TestCase {
    
    private $original_dir = null;

    #setUp executes every time.... this takes quite a while and is resource heavy because of the number of DB thingies to be set up.
    # We should set up once and clean up properly after each test

    public function setUp() {
        #PHPUnit_Framework_Error_Warning::$enabled = FALSE;
        #PHPUnit_Framework_Error_Notice::$enabled = FALSE;

        if ($this->original_dir != null) {
            $this->original_dir = getcwd();
            echo getcwd();
        }

        //require_once(dirname(__FILE__) . "/../Model/memcache_store.php");
        require_once(dirname(__FILE__) . "/../Model/model.php");

        $fields = array("getpost_array" => null, );
        $this->model = new moviesite\model();
    }

    public function tearDown() {
    }   
    
    public function testgetMovieListFromCache() {            
        
        echo "\n\ntestgetMovieListFromCache";
        $provider_name = "cinemaworld";
        list($movies_keyed_by_name_and_year, $error_messages) = $this -> model -> cache_logic_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
        
        //$this -> assertEquals($found, true);
       $this -> assertNotEquals($movies_keyed_by_name_and_year, array());
    }
    
    public function testgetMovieListFromMemCache() {            
        echo "\n\ntestgetMovieListFromMemCache";
        $provider_name = "cinemaworld";
        list($movies_keyed_by_name_and_year, $found, $error_messages) = $this -> model -> memcache_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
        
        $this -> assertEquals($found, true);
       //$this -> assertNotEquals($movies_keyed_by_name_and_year, array());
    }
    
    public function testgetMovieListFromDatabase() {            
        echo "\n\ntestgetMovieListFromDatabase";
        
        $provider_name = "cinemaworld";
        list($movies_keyed_by_name_and_year, $found, $error_messages) = $this -> model -> curl_get_movies_list_by_provider_keyed_by_name_and_year_retry($provider_name);
        
        $this -> assertEquals($found, true);
       //$this -> assertNotEquals($movies_keyed_by_name_and_year, array());
    }
    
    public function testgetMovieListFromCurl() {            
        echo "\n\ntestgetMovieListFromCurl";
        $provider_name = "cinemaworld";
        list($movies_keyed_by_name_and_year, $found, $error_messages) = $this -> model -> database_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
       
        $this -> assertEquals($found, true);
       //$this -> assertNotEquals($movies_keyed_by_name_and_year, array());
    }

    
    public function testgetMovieDetailsFromCache() {            
        echo "\n\ntestgetMovieDetailsFromCache";
        $provider_name = "cinemaworld";
        $id = "cw0076759";
        list($movie, $error_messages) = $this -> model -> cache_logic_get_movie_details_by_provider_and_id($provider_name, $id);
        
        //$this -> assertEquals($found, true);
       $this -> assertNotEquals($movie, null);
    }
    
    public function testgetMovieDetailsFromMemCache() {            
        echo "\n\ntestgetMovieDetailsFromMemCache";
        $provider_name = "cinemaworld";
        $id = "cw0076759";
        list($movie, $found,  $error_messages) = $this -> model -> memcache_get_movie_details_by_provider_and_id($provider_name, $id);
        
        $this -> assertEquals($found, true);
        //$this -> assertNotEquals($movie, null);
    }
    
    public function testgetMovieDetailsFromDatabase() {            
        echo "\n\ntestgetMovieDetailsFromDatabase";
        $provider_name = "cinemaworld";
        $id = "cw0076759";
        list($movie, $found,  $error_messages) = $this -> model -> database_get_movie_details_by_provider_and_id($provider_name, $id);
        
        $this -> assertEquals($found, true);
        //$this -> assertNotEquals($movie, null);
    }
    
    public function testgetMovieDetailsFromCurl() {            
        echo "\n\ntestgetMovieDetailsFromCurl";
        $provider_name = "cinemaworld";
        $id = "cw0076759";
        list($movie, $found, $error_messages) = $this -> model -> curl_get_movie_details_by_provider_and_id_retry($provider_name, $id);
        
        $this -> assertEquals($found, true);
        //$this -> assertNotEquals($movie, null);
    }
}

?>
