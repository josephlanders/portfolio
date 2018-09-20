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
        $provider_name = "filmworld";
        list($movies_keyed_by_name_and_year, $error_messages) = $this -> model -> cache_logic_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
        
       $this -> assertNotEquals($movies_keyed_by_name_and_year, array());
    }
    
    public function testgetMovieDetailsFromCache() {            
        $provider_name = "filmworld";
        $id = "cw0076759";
        list($movie, $error_messages) = $this -> model -> cache_logic_get_movie_details_by_provider_and_id($provider_name, $id);
        
       $this -> assertNotEquals($movie, array());
    }
}

?>
