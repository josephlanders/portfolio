<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/movie_provider.php");
require_once(dirname(__FILE__) . "/memcache_store.php");
require_once(dirname(__FILE__) . "/movie.php");
require_once(dirname(__FILE__) . "/movie_provider.php");
require_once(dirname(__FILE__) . "/database.php");
require_once(dirname(__FILE__) . "/../configuration/configuration.php");

class model {

    private $response_array = "";
    private $movie_providers = array();
    private $memcache_store = null;
    private $memcache_expiry = 15;
    private $movies = array();
    private $getpost_array = array();
    private $use_exponential_backoff = true;
    // Set to 1 minimum
    private $num_retry_attempts = 3;
    private $verbose = true;
    private $database = null;
    private $use_memcache = true;
    private $use_database = true;
    private $configuration = null;
    private $scrape_movies_expiry = 60;
    private $scrape_movie_detail_expiry = 30;

    function __construct() {
        if ($this->verbose == true) {
            echo "<br/>Memcached Expiry: " . $this->memcache_expiry . " seconds";
            echo "<br/>Curl retry attempts: " . $this->num_retry_attempts;
            echo "<br/>Curl Exponential Backoff: " . $this->use_exponential_backoff;
            echo "<br/>Movie List Scraped data expiry time: " . $this->scrape_movies_expiry . " seconds";
            echo "<br/>Movie detail Scraped data expiry time: " . $this->scrape_movie_detail_expiry . " seconds";
            echo "<br/>";
        }

        $this->getpost_array = array_merge($_GET, $_POST);

        $this->memcache_store = new memcache_store();

        $this->database = new \moviesite\database();

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

        $this->configuration = new \shared_code\configuration(dirname(__FILE__) . "/../config.cfg");
    }

    public function get_providers() {
        return $this->movie_providers;
    }

    public function get_provider($provider_name) {
        $provider = $this->movie_providers[$provider_name];
        return $provider;
    }

    public function cache_logic_get_movies_list_by_provider_keyed_by_name_and_year($provider_name) {
        $movies_keyed_by_name_and_year = array();
        $error_messages = array();
        $new_error_messags = array();
        $retrieved_from_memcache = false;
        $retrieved_from_database = false;
        $retrieved_from_curl = false;
        $updated_memcache = false;
        $updated_database = false;
        $cache_invalid = false;

        $now = new \DateTime("now");
        $setting_name = "scrapedtimestamp_" . $provider_name;
        $now_timestamp = $now->getTimestamp();
        $scraped_timestamp = $this->configuration->get_setting($setting_name);

        if ($now_timestamp - $scraped_timestamp > $this->scrape_movies_expiry) {
            $cache_invalid = true;
        }

        if ($cache_invalid == false) {
            if ($this->use_memcache == true) {
                list($movies_keyed_by_name_and_year, $retrieved_from_memcache, $new_error_messages) = $this->memcache_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
                $error_messages = array_merge($error_messages, $new_error_messages);
            }

            if ($retrieved_from_memcache == false) {
                if ($this->use_database == true) {
                    list($movies_keyed_by_name_and_year, $retrieved_from_database, $new_error_messages) = $this->database_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
                    $error_messages = array_merge($error_messages, $new_error_messages);
                }

                if ($retrieved_from_database == false) {
                    list($movies_keyed_by_name_and_year, $retrieved_from_curl, $new_error_messages) = $this->curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name);
                    $error_messages = array_merge($error_messages, $new_error_messages);
                }
            }


            if (($this->use_memcache == true && $retrieved_from_memcache == false) && ($retrieved_from_database == true || $retrieved_from_curl == true)) {
                $this->memcache_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $movies_keyed_by_name_and_year);
                $updated_memcache = true;
            }

            if (($retrieved_from_memcache == false && ($this->use_database == true && $retrieved_from_database == false)) && $retrieved_from_curl == true) {
                $this->database_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $movies_keyed_by_name_and_year);
                $updated_database = true;
            }
        }

        if ($cache_invalid == true) {
            list($movies_keyed_by_name_and_year, $retrieved_from_curl, $new_error_messages) = $this->curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name);
            if ($retrieved_from_curl == true) {
                $this->memcache_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $movies_keyed_by_name_and_year);
                $this->database_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $movies_keyed_by_name_and_year);
                $updated_memcache = true;
                $updated_database = true;
            }
        }

        if ($retrieved_from_curl == true) {
            $now = new \DateTime("now");
            $setting_name = "scrapedtimestamp_" . $provider_name;
            $value = $now->getTimestamp();
            $this->configuration->set_setting($setting_name, $value);
        }



        if ($this->verbose == true) {
            echo "<br/><br/>(Debug data for the actions that occurred above)";
            echo "<br/>Movies list: " . $provider_name;
            echo "<br/>Cache invalid due to scrape time?: " . $cache_invalid;
            echo "<br/>Retrieved from memcache: " . $retrieved_from_memcache;
            echo "<br/>Retrieved from database: " . $retrieved_from_database;
            echo "<br/>Retrieved from curl: " . $retrieved_from_curl;

            echo "<br/>Updated memcache: " . $updated_memcache;
            echo "<br/>Updated database: " . $updated_database;
            echo "<br/>";
        }



        return array($movies_keyed_by_name_and_year, $error_messages);
    }

    public function cache_logic_get_movie_details_by_provider_and_id($provider_name, $id) {
        $movie = null;
        $error_messages = array();
        $new_error_messages = array();
        $retrieved_from_memcache = false;
        $retrieved_from_database = false;
        $retrieved_from_curl = false;
        $updated_memcache = false;
        $updated_database = false;
        $cache_invalid = false;

        if ($this->use_memcache == true) {
            list($movie, $retrieved_from_memcache, $new_error_messages) = $this->memcache_get_movie_details_by_provider_and_id($provider_name, $id);
            $error_messages = array_merge($error_messages, $new_error_messages);
        }

        if ($retrieved_from_memcache == true) {
            $now = new \DateTime("now");
            $now_timestamp = $now->getTimestamp();
            $scraped_datetime = $movie->datemodified;
            $scraped_datetime_obj = new \DateTime($scraped_datetime);
            $scraped_timestamp = $scraped_datetime_obj->getTimestamp();

            if ($now_timestamp - $scraped_timestamp > $this->scrape_movie_detail_expiry) {
                $cache_invalid = true;
            }
        }




        if ($cache_invalid == false) {
            //if (($retrieved_from_memcache == false) || ($retrieved_from_memcache == true && $movie -> hasDetails == false)) {
            if ($retrieved_from_memcache == false) {
                if ($this->use_database == true) {
                    list($movie, $retrieved_from_database, $new_error_messages) = $this->database_get_movie_details_by_provider_and_id($provider_name, $id);
                    $error_messages = array_merge($error_messages, $new_error_messages);
                }

                if ($retrieved_from_database == true) {
                    $now = new \DateTime("now");
                    $now_timestamp = $now->getTimestamp();
                    $scraped_datetime = $movie->datemodified;
                    $scraped_datetime_obj = new \DateTime($scraped_datetime);
                    $scraped_timestamp = $scraped_datetime_obj->getTimestamp();

                    if ($now_timestamp - $scraped_timestamp > $this->scrape_movie_detail_expiry) {
                        $cache_invalid = true;
                    }
                }

                if (($retrieved_from_database == false) || ($retrieved_from_database == true && $movie->hasDetails == false)) {
                    list($movie, $retrieved_from_curl, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id_retry($provider_name, $id);
                    $error_messages = array_merge($error_messages, $new_error_messages);
                }
            }


            if (($this->use_memcache == true && $retrieved_from_memcache == false) && ($retrieved_from_database == true || $retrieved_from_curl == true)) {
                $this->memcache_put_movie_details_by_provider_and_id($provider_name, $id, $movie);
                $updated_memcache = true;
            }

            if (($retrieved_from_memcache == false && ($this->use_database == true && $retrieved_from_database == false)) && $retrieved_from_curl == true) {
                $this->database_put_movie_details_by_provider_and_id($provider_name, $id, $movie);
                $updated_database = true;
            }

            if ($this->use_database == true && $retrieved_from_curl == true && $movie->hasDetails == true) {
                $this->database_put_movie_details_by_provider_and_id($provider_name, $id, $movie);
                $updated_database = true;
            }
        }

        if ($cache_invalid == true) {
            //echo "cache invalid";
            list($movie, $retrieved_from_curl, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id_retry($provider_name, $id);
            $error_messages = array_merge($error_messages, $new_error_messages);

            if ($retrieved_from_curl == true) {
                $this->memcache_put_movie_details_by_provider_and_id($provider_name, $id, $movie);
                $this->database_put_movie_details_by_provider_and_id($provider_name, $id, $movie);
                $updated_memcache = true;
                $updated_database = true;
            }
        }

        if ($this->verbose == true) {
            echo "<br/><br/>(Debug data for the actions that occurred above)";
            echo "<br/>Movie details: " . $id;
            echo "<br/>Cache invalid due to scrape time?: " . $cache_invalid;
            echo "<br/>Retrieved from memcache: " . $retrieved_from_memcache;
            echo "<br/>Retrieved from database: " . $retrieved_from_database;
            echo "<br/>Retrieved from curl: " . $retrieved_from_curl;

            echo "<br/>Updated memcache: " . $updated_memcache;
            echo "<br/>Updated database: " . $updated_database;
            echo "<br/>";
        }



        return array($movie, $error_messages);
    }

    // Indexes Duplicates like so:
    //    $array["$movie_name ($year)"][$provider]
    public function get_collated_movie_list_with_details() {
        $movies = $this->movies;
        $error_messages = array();

        foreach ($this->movie_providers as $provider_name => $provider) {

            list($movies[$provider_name], $new_error_messages) = $this->cache_logic_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
            $error_messages = array_merge($error_messages, $new_error_messages);
        }

        foreach ($movies as $provider_name => $provider_movies) {
            foreach ($provider_movies as $name_and_year => $movie) {
                $id = $movie->ID;
                list($movie, $error_messages) = $this->cache_logic_get_movie_details_by_provider_and_id($provider_name, $id);
                $name_and_year = $movie->get_display_name();
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
            list($movies[$provider_name], $new_error_messages) = $this->cache_logic_get_movies_list_by_provider_keyed_by_name_and_year($provider_name);
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

    //Unused
    public function get_movies_list_and_details_by_provider_indexed_by_name_and_year() {
        $movies = $this->movies;
        foreach ($this->movie_providers as $provider_name => $provider) {

            $movies[$provider->name] = $this->database_get_movies_list_by_provider_keyed_by_name_and_year($provider);

            foreach ($movies[$provider->name] as $key => $movie) {
                $id = $movie->ID;
                list($movie_with_details, $error_messages) = $this->database_get_movie_details_by_provider_and_id($provider, $id);

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
            echo "<br/>Trying memcached for movies list for " . $provider_name;
        }
        list($found, $movies_keyed_by_name_and_year, $error_message) = $this->memcache_store->get_search($searchkeyvalues);

        if ($this->verbose == true) {
            if ($found == true) {
                echo "<br/>Found movies list in memcached";
            } else {

                echo "<br/>Didn't find movies list in memcached";
            }
        }

        return array($movies_keyed_by_name_and_year, $found, $error_messages);
    }

    public function memcache_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $movies_keyed_by_name_and_year) {
        $found = false;
        $error_messages = array();
        $searchkeyvalues = array("provider" => $provider_name, "movies" => "movies",
            "keyed_by" => "name_and_year");



        if ($movies_keyed_by_name_and_year != array()) {
            if ($this->verbose == true) {
                echo "<br/>Storing movies list in memcached ";
            }


            $this->memcache_store->store_search($searchkeyvalues, $movies_keyed_by_name_and_year, $this->memcache_expiry);
        }

        return true;
    }

    public function database_put_movies_list_by_provider_keyed_by_name_and_year($provider_name, $new_movies_keyed_by_name_and_year) {
        $error_messages = array();

        $movies_keyed_by_name_and_year = $this->database->get_all_movies_by_provider_keyed_by_name_and_year($provider_name);

        // TODO: Make it write if there's been a change.
        //TODO: Don't continue with write if there was a DB error
        foreach ($new_movies_keyed_by_name_and_year as $name_and_year => $movie) {
            $name_and_year = $movie->get_display_name();
            if (!isset($movies_keyed_by_name_and_year[$name_and_year])) {
                $this->database->add_movie($movie);
            }
        }

        return true;
    }

    public function database_get_movies_list_by_provider_keyed_by_name_and_year($provider_name) {
        $error_messages = array();
        $found = false;

        $movies_keyed_by_name_and_year = $this->database->get_all_movies_by_provider_keyed_by_name_and_year($provider_name);
        //TODO:
        if ($movies_keyed_by_name_and_year != array()) {
            $found = true;
        }

        return array($movies_keyed_by_name_and_year, $found, $error_messages);
    }

    public function memcache_get_movie_details_by_provider_and_id($provider_name, $id) {
        $found = false;
        $movie = null;
        $error_messages = array();

        $searchkeyvalues = array("provider" => $provider_name, "movie" => "movie", "id" => $id);
        // Try memcached first, 
        if ($this->verbose == true) {
            echo "<br/>Trying to find movie details in memcached " . $provider_name . " " . $id;
        }

        list($found, $movie, $error_message) = $this->memcache_store->get_search($searchkeyvalues);


        if ($this->verbose == true) {
            if ($found == true) {
                echo "<br/>Found movie detail in memcached";
            } else {
                echo "<br/>Didn't find movie detail in memcached";
            }
        }

        return array($movie, $found, $error_messages);
    }

    public function memcache_put_movie_details_by_provider_and_id($provider_name, $id, $movie) {
        $found = false;
        $error_messages = array();

        $searchkeyvalues = array("provider" => $provider_name, "movie" => "movie", "id" => $id);



        if ($movie != null) {
            if ($this->verbose == true) {
                echo "<br/>Storing movie details in memcached ";
            }

            $this->memcache_store->store_search($searchkeyvalues, $movie, $this->memcache_expiry);
        }


        return array($movie, $found, $error_messages);
    }

    public function curl_get_movie_details_by_provider_and_id_retry($provider_name, $id) {
        $movie = null;
        $found = false;
        $error_messages = array();

        if ($this->verbose == true) {
            echo "<br/>Attempting to get movie details via curl";
        }

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "<br/>Server retries number: " . $i;

                // Round Robin
                echo "<br/>Trying provider " . $provider_name;
            }
            try {
                list($movie_details, $new_error_messages) = $this->curl_get_movie_details_by_provider_and_id($provider_name, $id);
                $error_messages = array_merge($error_messages, $new_error_messages);

                $movie_details["Provider"] = $provider_name;

                $movie = new \moviesite\movie($movie_details);
                $movie->hasDetails = true;

                if ($movie_details != array()) {
                    $found = true;
                    if ($this->verbose == true) {
                        echo "<br/>Got movie details via curl";
                    }
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
        if ($success == true) {
            $error_messages = array();
        }

        return array($movie, $found, $error_messages);
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
                echo "<br/>Server retries number: " . $i;

                // Round Robin
                echo "<br/>Trying provider " . $provider_name;
            }
            try {
                $movies_as_raw_array = $this->get_movies_list($provider);

                foreach ($movies_as_raw_array["Movies"] as $key => $raw_movie) {
                    $id = $raw_movie["ID"];

                    $raw_movie["Provider"] = $provider_name;
                    $new_movie = new movie($raw_movie);
                    $movies_keyed_by_id[$id] = $new_movie;
                }

                if ($movies_keyed_by_id != array()) {
                    echo "<br/>Got movies list via curl";
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

        // Ignore error messages on successful retry
        if ($success == true) {
            $error_messages = array();
        }

        return array($movies_keyed_by_id, $error_messages);
    }

    public function curl_get_movies_list_by_provider_keyed_by_name_and_year_retry($provider_name) {
        $movies_keyed_by_name_and_year = array();
        $error_messages = array();
        $found = false;

        if ($this->verbose == true) {
            echo "<br/>Attempting to get movie list via curl";
        }

        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "<br/>Server retries number: " . $i;

                // Round Robin
                echo "<br/>Trying provider " . $provider_name;
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

                if ($movies_keyed_by_name_and_year != array()) {
                    echo "<br/>Got movies list via curl";
                    $found = true;
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

        // Ignore error messages on successful retry
        if ($success == true) {
            $error_messages = array();
        }


        return array($movies_keyed_by_name_and_year, $found, $error_messages);
    }

    public function curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name) {
        $movies_keyed_by_id = array();
        $error_messages = array();
        $found = false;

        if ($this->verbose == true) {
            echo "<br/>Attempting to get movie list via curl";
        }


        $success = false;
        $num_attempts = $this->num_retry_attempts;
        for ($i = 0; $i < $num_attempts; $i++) {
            if ($success == true) {
                break;
            }
            if ($this->verbose == true) {
                echo "<br/>Server retries number: " . $i;

                // Round Robin
                echo "<br/>Trying provider " . $provider_name;
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

                if ($movies_keyed_by_id != array()) {
                    echo "<br/>Got movies list via curl";
                }

                if (count($movies_keyed_by_id) > 0) {
                    $found = true;
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
        if ($success == true) {
            $error_messages = array();
        }


        return array($movies_keyed_by_id, $found, $error_messages);
    }

    public function get_movie_with_details_attached($provider_name, $id) {

        $error_messages = array();

        //list($movie, $error_messages) = $this->get_movie_by_provider_and_id($provider_name, $id);
        //if ($movie != null) {

        list($movie, $new_error_messages) = $this->cache_logic_get_movie_details_by_provider_and_id($provider_name, $id);

        $error_messages = array_merge($error_messages, $new_error_messages);

        //}

        return array($movie, $error_messages);
    }

    function database_get_movie_details_by_provider_and_id($provider_name, $id) {
        $movie = null;
        $found = false;
        $hasdetails = false;
        $error_messages = array();

        if ($this->verbose == true) {
            echo "<br/>Checking database for movie details " . $id;
        }

        $movies = $this->database->get_all_movies_by_provider_keyed_by_id($provider_name);

        if ($movies == array()) {
            $found = false;
        }

        if (isset($movies[$id])) {
            $found = true;
            $movie = $movies[$id];
            $hasdetails = $movie->hasDetails;

            if ($this->verbose == true) {
                echo "<br/>Found movies details in database";
            }
        }

        return array($movie, $found, $error_messages);
    }

    function database_put_movie_details_by_provider_and_id($provider_name, $id, $movie) {
        $found = false;
        $hasdetails = false;
        $error_messages = array();

        if ($this->verbose == true) {
            echo "<br/>Checking database for movie " . $id;
        }

        $movies = $this->database->get_all_movies_by_provider_keyed_by_id($provider_name);

        if ($movies == array()) {
            $found = false;
        }

        if (isset($movies[$id])) {
            $found = true;
            $saved_movie = $movies[$id];
            $hasdetails = $saved_movie->hasDetails;
        }



        if ($hasdetails == false) {

            if ($movie != null) {
                $this->database->update_movie($movie);
            }

            if ($this->verbose == true) {
                echo "<br/>Movie has no details, updating in database";
            }
        } else {
            if ($this->verbose == true) {
                echo "<br/>Movie has details, doing nothing to database";
            }
        }
        return array($movie, $found, $error_messages);
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

    //unused
    //TODO: Break down into get and put
    public function memcache_get_movies_list_by_provider_keyed_by_id($provider_name) {
        $found = false;
        $error_messages = array();
        $searchkeyvalues = array("provider" => $provider_name, "movies" => "movies",
            "keyed_by" => "id");
        // Try memcached first, 
        if ($this->verbose == true) {
            echo "<br/>Trying memcached for movies list " . $provider_name;
        }

        list($found, $movies_keyed_by_id, $error_message) = $this->memcache_store->get_search($searchkeyvalues);


        $error_messages[] = $error_message;

        if ($found == true) {
            if ($this->verbose == true) {
                echo "<br/>Found movies list in memcached";
            }
        }

        if ($found == false) {
            if ($this->verbose == true) {
                echo "<br/>Didn't find movies list in memcached ";
            }
            list($movies_keyed_by_id, $new_error_messages) = $this->curl_get_movies_list_by_provider_keyed_by_id_retry($provider_name);

            $error_messages = array_merge($error_messages, $new_error_messages);

            if ($movies_keyed_by_id != array()) {
                if ($this->verbose == true) {
                    echo "<br/>Storing movies list in memcached ";
                }

                $this->memcache_store->store_search($searchkeyvalues, $movies_keyed_by_id, $this->memcache_expiry);
            }
        }

        return array($movies_keyed_by_id, $error_messages);
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
