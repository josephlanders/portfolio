<?php

namespace moviesite;

require_once("movie_provider.php");
require_once("memcache_store.php");
require_once("movie.php");
require_once("movie_provider.php");
require_once("database.php");

/**
 * Description of model
 *
 * @author z
 */
class model {

    private $response_array = "";
    private $movie_providers = array();
    private $memcache_store = null;
    private $memcache_expiry = 15;
    private $movies = array();
    private $getpost_array = array();
    private $use_exponential_backoff = true;
    
    // Set to 1
    private $num_retry_attempts = 2;
    private $verbose = false;
    
    private $database = null;

    function __construct() {

        $this->getpost_array = array_merge($_GET, $_POST);

        $this->memcache_store = new memcache_store();
        
        $this -> database = new \moviesite\database();

        $cinemaworld_array = array("name" => "cinemaworld", "movies_list_address" => "http://webjetapitest.azurewebsites.net/api/cinemaworld/movies",
            "movie_detail_address" => "http://webjetapitest.azurewebsites.net/api/cinemaworld/movie",
            "api_token" => "sjd1HfkjU83ksdsm3802k",
            "connect_timeout" => 3,
            "timeout" => 5);
        $cinemaworld_provider = new movie_provider($cinemaworld_array);

        $filmworld_array = array("name" => "filmworld", "movies_list_address" => "http://webjetapitest.azurewebsites.net/api/filmworld/movies",
            "movie_detail_address" => "http://webjetapitest.azurewebsites.net/api/filmworld/movie",
            "api_token" => "sjd1HfkjU83ksdsm3802k",
            "connect_timeout" => 3,
            "timeout" => 5);
        $filmworld_provider = new movie_provider($filmworld_array);

        $this->movie_providers = array("cinemaworld" => $cinemaworld_provider, "filmworld" => $filmworld_provider);
    }

    public function get_providers() {
        return $this->movie_providers;
    }

    public function get_provider($provider_name) {
        $provider = $this->movie_providers[$provider_name];
        return $provider;
    }

    /*
    public function get_collated_movie_details() {
        list($movie_details, $error_messages) = $this->get_movie_details_by_provider_and_id_memcache($provider_name, $id);

        return $movie_details;
    } */

    // Indexes Duplicates like so:
    //    $array["$movie_name ($year)"][$provider]
    public function get_collated_movie_list_with_details() {
        $movies = $this->movies;
        foreach ($this->movie_providers as $provider_name => $provider) {
            //var_dump($provider_name);
            //var_dump($provider);
            $movies[$provider_name] = $this->memcache_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
        }

        foreach ($movies as $provider_name => $provider_movies) {
            foreach ($provider_movies as $name_and_year => $movie) {
                $id = $movie->ID;
                list($movie, $error_messages) = $this->memcache_get_movie_details_by_provider_and_id($provider_name, $id);
                //$movie->set_details($movie_details);
                $movies[$provider_name][$name_and_year] = $movie;
            }
        }

        $all_movies = $this->collate_multiple_provider_movie_lists_by_name_and_year($movies);

        return $all_movies;
    }

    // Indexes Duplicates like so:
    //    $array["$movie_name ($year)"][$provider]
    public function get_collated_movie_list() {
        $movies = $this->movies;
        $error_messages = array();
        foreach ($this->movie_providers as $provider_name => $provider) {
            list($movies[$provider_name], $new_error_messages) = $this->memcache_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
            $error_messages = array_merge($error_messages, $new_error_messages);
        }

        $all_movies = $this->collate_multiple_provider_movie_lists_by_name_and_year($movies);

        return array($all_movies, $error_messages);
    }

    public function collate_multiple_provider_movie_lists_by_name_and_year($movies) {

        $all_movies = array();
        //Build movie list keyed on name
        // Format: 
        foreach ($movies as $provider_name => $provider_movies) {
            foreach ($provider_movies as $id => $movie) {
                $movie_title = $movie->get_title();
                $movie_year = $movie->get_year();
                $search_key = $movie_title . " (" . $movie_year . ")";
                if (isset($all_movies[$search_key])) {
                    
                } else {
                    $all_movies[$search_key] = array();
                }
                $all_movies[$search_key][$provider_name] = $movie;
            }
        }

        return $all_movies;
    }

    public function get_movies_list_and_details_by_provider_indexed_by_name_and_year() {
        $movies = $this->movies;
        foreach ($this->movie_providers as $provider_name => $provider) {

            $movies[$provider->name] = $this->database_get_movies_list_by_provider_keyed_by_name_and_year($provider);

            foreach ($movies[$provider->name] as $key => $movie) {
                $id = $movie->ID;
                list($movie_with_details, $error_messages) = $this->database_get_movie_details_by_provider_and_id($provider, $id);

                //$movie->set_details($movie_details);

                $movies[$provider->name][$id] = $movie_with_details;
            }
        }

        $all_movies = array();
        //Build movie list keyed on name
        // Format: 
        foreach ($movies as $provider_name => $provider_movies) {
            foreach ($provider_movies as $id => $movie) {
                $movie_title = $movie->get_title();
                $movie_year = $movie->get_year();
                $search_key = $movie_title . "(" . $movie_year . ")";
                if (isset($all_movies[$search_key])) {
                    
                } else {
                    $all_movies[$search_key] = array();
                }
                $all_movies[$search_key][$provider_name] = $movie;
            }
        }

        return $all_movies;
    }

    public function memcache_get_movies_list_by_provider_keyed_by_name_and_year($provider_name) {
        $found = false;
        $error_messages = array();
        $searchkeyvalues = array("provider" => $provider_name, "movies" => "movies",
            "keyed_by" => "name_and_year");
        
        // Try memcached first, 
        if ($this->verbose == true) {
            echo "\nTrying memcached for movies list for " . $provider_name;
        }
        list($found, $movies_keyed_by_name_and_year, $error_message) = $this->memcache_store->get_search($searchkeyvalues);

        if ($found == true) {
            if ($this->verbose == true) {
                echo "\nFound movies list in memcached";
            }
        }

        if ($found == false) {
            if ($this->verbose == true) {
                echo "\nNothing found for movies list in memcached ";
            }
            list($movies_keyed_by_name_and_year, $error_messages) = $this->database_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);

            if ($movies_keyed_by_name_and_year != array()) {
                if ($this->verbose == true) {
                    echo "\nStoring movies list in memcached ";
                }
                $this->memcache_store->store_search($searchkeyvalues, $movies_keyed_by_name_and_year, $this->memcache_expiry);
            }
        }

        return array($movies_keyed_by_name_and_year, $error_messages);
    }
    
    public function database_get_movies_list_by_provider_keyed_by_name_and_year($provider_name)
    {
        $error_messages = array();
       
        $movies_keyed_by_name_and_year = $this -> database ->get_all_movies_by_provider_keyed_by_name_and_year($provider_name);
        
        if ($movies_keyed_by_name_and_year == array())
        {
            list($movies_keyed_by_name_and_year, $error_messages) = $this->curl_get_movies_list_by_provider_keyed_by_name_and_year_retry($provider_name);
            
            // Write to database
            foreach ($movies_keyed_by_name_and_year as $name_and_year => $movie)
            {
                $this -> database ->add_movie($movie);
            }
        }
        
        return array($movies_keyed_by_name_and_year, $error_messages);
    }

    public function memcache_get_movies_list_by_provider_keyed_by_id($provider_name) {
        $found = false;
        $error_messages = array();
        $searchkeyvalues = array("provider" => $provider_name, "movies" => "movies",
            "keyed_by" => "id");
        // Try memcached first, 
        if ($this->verbose == true) {
            echo "\nTrying memcached for movies list " . $provider_name;
        }
        list($found, $movies_keyed_by_id, $error_message) = $this->memcache_store->get_search($searchkeyvalues);
        
        $error_messages[] = $error_message;
        
        if ($found == true) {
            if ($this->verbose == true) {
                echo "\nFound movies list in memcached";
            }
        }

        if ($found == false) {
            if ($this->verbose == true) {
                echo "\nNothing found for movies list in memcached ";
            }
            list($movies_keyed_by_id, $new_error_messages) = $this->curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name);
            
            $error_messages = array_merge($error_messages, $new_error_messages);

            if ($movies_keyed_by_id != array()) {
                if ($this->verbose == true) {
                    echo "\nStoring movies list in memcached ";
                }
                $this->memcache_store->store_search($searchkeyvalues, $movies_keyed_by_id, $this->memcache_expiry);
            }
        }

        return array($movies_keyed_by_id, $error_messages);
    }

    public function memcache_get_movie_details_by_provider_and_id($provider_name, $id) {
        $found = false;
        $movie = null;
        $error_messages = array();

        $searchkeyvalues = array("provider" => $provider_name, "movie" => "movie", "id" => $id);
        // Try memcached first, 
        if ($this->verbose == true) {
            echo "\nTrying to find movie details in memcached " . $provider_name . " " . $id;
        }
        list($found, $movie, $error_message) = $this->memcache_store->get_search($searchkeyvalues);

        if ($found == true) {
            if ($this->verbose == true) {
                echo "\nFound movie detail in memcached";
            }
        }

        if ($found == false) {
            if ($this->verbose == true) {
                echo "\nNothing found for movie details in memcached ";
            }
            list($movie, $error_messages) = $this->database_get_movie_details_by_provider_and_id($provider_name, $id);

            if ($movie != null) {
                if ($this->verbose == true) {
                    echo "\nStoring movie details in memcached ";
                }
                $this->memcache_store->store_search($searchkeyvalues, $movie, $this->memcache_expiry);
            }
        }

        return array($movie, $error_messages);
    }

    public function curl_get_movie_details_by_provider_and_id_retry($provider_name, $id) {
        $movie = null;
        $error_messages = array();

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "\nServer retries number: " . $i;

                // Round Robin
                echo "\nTrying provider " . $provider_name;
            }
            try {
                list($movie_details, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id($provider_name, $id);
                $error_messages = array_merge($error_messages, $new_error_messages);
                
                $movie_details["Provider"] = $provider_name;
                
                $movie = new \moviesite\movie($movie_details);
                $movie -> hasDetails = true;
                //$movie -> set_details($movie_details);
                $success = true;
                break;
            } catch (\Exception $ex) {
                $error_messages[] = $ex->getMessage();
                if ($this->verbose == true) {
                    echo "exception: " . implode(",", $error_messages);
                }
            }

            if (($success == false) && ($this->use_exponential_backoff == true)) {
                // Implement exponential backoff
                $sleep_time = $i * $i;
                sleep($sleep_time);
            }
        }
        
        // Ignore error messages if we are eventually about to succeed as it will annoy the end-user
        if ($success == true)
        {
            $error_messages = array();
        }

        return array($movie, $error_messages);
    }

    public function curl_get_movies_list_by_provider_keyed_by_id($provider_name) {
        $movies_keyed_by_id = array();
        $error_messages = array();

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "\nServer retries number: " . $i;

                // Round Robin
                echo "\nTrying provider " . $provider_name;
            }
            try {
                $movies_as_raw_array = $this->get_movies_list($provider);

                foreach ($movies_as_raw_array["Movies"] as $key => $raw_movie) {
                    $id = $raw_movie["ID"];
                    
                    $raw_movie["Provider"] = $provider_name;
                    $new_movie = new movie($raw_movie);
                    $movies_keyed_by_id[$id] = $new_movie;
                }
                $success = true;
                break;
            } catch (\Exception $ex) {
                $error_messages[] = $ex ->getMessage();
                if ($this->verbose == true) {
                    echo "exception: " . implode(",", $error_messages);
                }
            }

            if (($success == false) && ($this->use_exponential_backoff == true)) {
                // Implement exponential backoff
                $sleep_time = $i * $i;
                sleep($sleep_time);
            }
        }
        
        // Ignore error messages on successful retry
        if ($success == true)
        {
            $error_messages = array();
        }

        return array($movies_keyed_by_id, $error_messages);
    }

    public function curl_get_movies_list_by_provider_keyed_by_name_and_year_retry($provider_name) {
        $movies_keyed_by_name_and_year = array();
        $error_messages = array();

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "\nServer retries number: " . $i;

                // Round Robin
                echo "\nTrying provider " . $provider_name;
            }
            try {
                list($movies_as_raw_array, $new_error_messages) = $this->curl_get_movies_list_by_provider($provider_name);
                
                $error_messages = array_merge($error_messages, $new_error_messages);

                foreach ($movies_as_raw_array["Movies"] as $key => $raw_movie) {
                    $searchkey = $raw_movie["Title"] . " (" . $raw_movie["Year"] . ")";
                    $raw_movie["Provider"] = $provider_name;
                    $new_movie = new movie($raw_movie);
                    $movies_keyed_by_name_and_year[$searchkey] = $new_movie;
                }
                $success = true;

                if ($this->verbose == true) {
                    echo "Data found";
                }
                break;
            } catch (\Exception $ex) {
                $error_messages[] = $ex->getMessage();
                if ($this->verbose == true) {
                    echo "exception: " . implode(",", $error_messages);
                }
            }

            if (($success == false) && ($this->use_exponential_backoff == true)) {
                // Implement exponential backoff
                $sleep_time = $i * $i;
                sleep($sleep_time);
            }
        }
        
        // Ignore error messages on successful retry
        if ($success == true)
        {
            $error_messages = array();
        }

        return array($movies_keyed_by_name_and_year, $error_messages);
    }

    public function curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name) {
        $movies_keyed_by_id = array();
        $error_messages = array();

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "\nServer retries number: " . $i;

                // Round Robin
                echo "\nTrying provider " . $provider_name;
            }
            try {
                list($movies_as_raw_array, $new_error_messages) = $this->curl_get_movies_list_by_provider($provider_name);
                
                $error_messages = array_merge($error_messages, $new_error_messages);

                foreach ($movies_as_raw_array["Movies"] as $key => $raw_movie) {
                    $id = $raw_movie["ID"];
                    $raw_movie["Provider"] = $provider_name;
                    $new_movie = new movie($raw_movie);
                    $movies_keyed_by_id[$id] = $new_movie;
                }
                $success = true;
                break;
            } catch (\Exception $ex) {
                $error_messages[] = $ex->getMessage();
                if ($this->verbose == true) {
                    echo "exception: " . implode(",", $error_messages);
                }
            }

            if (($success == false) && ($this->use_exponential_backoff == true)) {
                // Implement exponential backoff
                $sleep_time = $i * $i;
                sleep($sleep_time);
            }
        }
        
        // Ignore error messages if we are eventually about to succeed as it will annoy the end-user
        if ($success == true)
        {
            $error_messages = array();
        }


        return array($movies_keyed_by_id, $error_messages);
    }

    public function get_movie_with_details_attached($provider_name, $id) {

        $error_messages = array();
        
        //list($movie, $error_messages) = $this->get_movie_by_provider_and_id($provider_name, $id);

        //if ($movie != null) {

            list($movie, $new_error_messages) = $this->database_get_movie_details_by_provider_and_id($provider_name, $id);
            
            $error_messages = array_merge($error_messages, $new_error_messages);

        //}

        return array($movie, $error_messages);
    }
        
    function database_get_movie_details_by_provider_and_id($provider_name, $id)
    {
        $found = false;
        $hasdetails = false;
        $error_messages = array();
        
        $movies = $this -> database ->get_all_movies_by_provider_keyed_by_id($provider_name);       
        
        if ($movies == array())
        {
            return;
        }
        
        if (isset($movies[$id]))
        {
            $found = true;
            $movie = $movies[$id];
            $hasdetails = $movie -> hasDetails;            
        }
       
        if ($found == false)
        {
            echo "Movie not found on database";
            list($movie, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id_retry($provider_name, $id);
            
            $error_messages = array_merge($error_messages, $new_error_messages);
            // Add 
            if ($movie != null)
            {
                $this -> database -> add_movie($movie);
            } 
        } else {
            
            if ($hasdetails == false)
            {
                echo "Movie in movie list but has no details";;
                list($movie, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id_retry($provider_name, $id);
                
                $error_messages = array_merge($error_messages, $new_error_messages);
                
                if ($movie != null)
                {
                    $this -> database -> update_movie($movie);
                }
            }
        }
        return array($movie, $error_messages);
    }


    public function get_movie_by_provider_and_id($provider_name, $id) {
        $movie = null;
        list($movies, $error_messages) = $this->memcache_get_movies_list_by_provider_keyed_by_id($provider_name);

        if (isset($movies[$id])) {
            $movie = $movies[$id];
        }

        return array($movie, $error_messages);
    }

    public function get_alternate_movies($visited_provider_name, $name, $year) {
        list($all_movies, $error_messages) = $this->get_collated_movie_list();

        $searchkey = $name . " (" . $year . ")";

        $found_movies = array();
        if (isset($all_movies[$searchkey])) {
            $found_movies[$searchkey] = $all_movies[$searchkey];
        }

        if (isset($found_movies[$searchkey][$visited_provider_name])) {
            unset($found_movies[$searchkey][$visited_provider_name]);
        }

        return array($found_movies, $error_messages);
    }

    // Incomplete
    public function get_alternate_movies_and_details($visited_provider_name, $name, $year) {
        list($all_movies, $error_messages) = $this->get_collated_movie_list();

        $searchkey = $name . " (" . $year . ") ";
        $movie = array();
        if (isset($all_movies[$searchkey])) {
            $movie = $all_movies[$searchkey];

            foreach ($movie as $provider_name => $provider_movie) {
                list($new_movie, $new_error_messages) = $this->memcache_get_movie_details_by_provider_and_id($provider_name, $id);
                
                $error_messages = array_merge($error_messages, $new_error_messages);
                //$movie->set_details($movie_details);
                
            }
        }

        return $movie;
    }

    public function process_postback($keep) {
        $merged = $this->getpost_array;


        // Always return null for empty fields that are wanted but 
        // not passed in to save us doing isset call.
        foreach ($keep as $key => $value) {
            if (!isset($merged[$key])) {
                $merged[$key] = null;
            }
        }

        return $merged;
    }

    public function curl_get_movies_list_by_provider($provider_name) {

        $provider = $this->get_provider($provider_name);

        $error_messages = array();

        $response_array = array();
        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options

        $movies_url = $provider->movies_list_address;

        curl_setopt($ch, CURLOPT_URL, $movies_url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        $headers_array = array("x-access-token: " . $provider->api_token, //"insert_developer_user_name_here",
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, "curl_handler_recv"));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $provider->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $provider->timeout); //timeout in seconds
        // grab URL and pass it to the browser
        $response = curl_exec($ch);

        $errno = curl_errno($ch);
        $errstr = curl_error($ch);

        $response_string = $response;
        $response_array = $this->decode_data($response, $provider->use_json_format);

        if ($response_array == array()) {
            throw new \Exception("get_movies_list_by_provider: Connection to movie service " . $provider_name . " timed out, this usually happens the service is down.");
            #echo "timed out";
        }

        // close cURL resource, and free up system resources
        curl_close($ch);

        return array($response_array, $error_messages);
    }

    public function curl_get_movie_details_by_provider_and_id($provider_name, $id) {

        $provider = $this->get_provider($provider_name);

        $error_messages = array();

        $response_array = array();
        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options

        $movie_url = $provider->movie_detail_address . "/" . $id;

        curl_setopt($ch, CURLOPT_URL, $movie_url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        $headers_array = array("x-access-token: " . $provider->api_token, //"insert_developer_user_name_here",
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, "curl_handler_recv"));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $provider->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $provider->timeout); //timeout in seconds
        // grab URL and pass it to the browser
        $response = curl_exec($ch);

        $errno = curl_errno($ch);
        $errstr = curl_error($ch);

        $response_string = $response;
        $response_array = $this->decode_data($response, $provider->use_json_format);

        if ($response_array == array()) {
            throw new \Exception("get_movie_details: Connection to movie service " . $provider_name . " timed out, this usually happens the service is down.");
            #echo "timed out";
        }

        // close cURL resource, and free up system resources
        curl_close($ch);

        return array($response_array, $error_messages);
    }

    function decode_data($data, $use_json_format) {

        $response_array = array();

        // Data is JSON 

        if ($use_json_format == true) {

            $response_array = json_decode($data, JSON_OBJECT_AS_ARRAY);
        } else {
            $response_array = $this->parse_response_array($data);
        }

        return $response_array;
    }

    function parse_response_array($string) {
        /* parse the response */
        $response_string_array = explode("&", $string);

        $proper_array = array();

        foreach ($response_string_array as $value) {
            list($key, $val) = explode("=", $value);

            $val = urldecode($val);

            $proper_array["$key"] = $val;
        }
        unset($key);
        unset($val);

        return $proper_array;
    }

}
