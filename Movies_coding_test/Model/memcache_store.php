<?php

namespace moviesite;

class memcache_store {

    private $searches = array();
    private $totalsearches = null;
    private $memcached = null;
    private $expiry = 60;
    private $model = null;
    public $last_key = null;
    public $last_key_unpacked = null;

    /*
     * Create a memcached store
     */

    public function __construct() {

        $extension_loaded = extension_loaded("memcache");
        if ($extension_loaded == false) {
            die("memcache extension not loaded");
        }

        // Probably segfaults if extension=memcached.so isn't loaded.
        $this->memcached = new \Memcache('mc3');

        $this->memcached->addServer(
                'localhost', 11211
        );
    }

    /*
     * Retrieves objectss from the cache
     * 
     * 
     * searchkeyvalues is an array like
     *  array(categoryid => 15, c => d)
     * Returns the object if found
     * 
     * Throws an \Exception if not found
     */

    public function get_search($searchkeyvalues) {
        $found = false;
        $message = "";
        $key = $this->make_key($searchkeyvalues);
        $memcached = $this->memcached;
        $object = $memcached->get($key);

        if (is_bool($object) && (boolean) $object == false) {
            //$message = "Not Found / Server down?";
            //throw new \Exception($message);
        } else {
            $found = true;
        }

        return array($found, $object, $message);
    }

    public function store_search($searchkeyvalues, $object, $expiry) {
        $error_messages = array();
        $stored = false;

        if ($expiry === true) {
            $expiry = $this->expiry;
        }

        $memcached = $this->memcached;

        $key = $this->make_key($searchkeyvalues);

        //$res = $memcached->set($key, $object, $expiry);
        $res = $memcached->set($key, $object, MEMCACHE_COMPRESSED, $expiry);

        if ($res == true) {
            $stored = true;
        }

        if ($res == false) {
            $error_messages[] = "Not added / Server down?";
            //var_dump($key);
            //var_dump($object);
            //throw new \Exception($message);
        }

        return array($stored, $error_messages);
    }

    public function delete($searchkeyvalues) {
        $memcached = $this->memcached;

        $key = $this->make_key($searchkeyvalues);

        $deleted = $memcached->delete($key);

        return $deleted;
    }

    private function make_key($searchkeyvalues) {
        $key = "";
        ksort($searchkeyvalues);
        $key .= $this->implode_assoc("|", $searchkeyvalues);

        return $key;
    }

    /*
     * Implode (concatenate) the key/values of an associative array
     * into a string
     * 
     * glue is a string
     * array is an array
     * 
     * returns a string
     */

    public function implode_assoc($glue, $array) {
        $string = "";

        $count = count($array);
        $i = 1;
        foreach ($array as $key => $value) {
            $string .= $key . $value;

            if ($i < $count) {
                $string .= $glue;
            }
            # Use an unlikely character to be used elsewhere 
            # character to represent space
        }

        return $string;
    }

    public function flush() {
        $this->memcached->flush();
    }

    public function parse_keyvalue($string, $symbol) {
        $array = explode($symbol, $string);

        $key = $array[0];
        unset($array[0]);

        $val = implode($symbol, $array);

        return array($key, $val);
    }

}