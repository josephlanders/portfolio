<?php

class SmallTest extends PHPUnit_Framework_TestCase {
    
    private $original_dir = null;

    #setUp executes every time.... this takes quite a while and is resource heavy because of the number of DB thingies to be set up.
    # We should set up once and clean up properly after each test

    public function setUp() {
        PHPUnit_Framework_Error_Warning::$enabled = FALSE;
        #PHPUnit_Framework_Error_Notice::$enabled = FALSE;

        if ($this->original_dir != null) {
            $this->original_dir = getcwd();
            echo getcwd();
        }

        //require_once("../Model/memcache_store.php");
        require_once("../Model/model.php");

        $fields = array("getpost_array" => null, );
        //$this->model = new moviesite\model($fields);
    }

    public function tearDown() {
    }   
}

?>
