<?php

namespace moviesite;
?>

<?php

require_once (dirname(__FILE__) . "/movie.php");

class database {

    private $mysqli = null;
    private $hostname = "";
    private $username = "";
    private $password = "";
    private $database_name = "";
    private $persistent = true;

    public function __construct() {
        $this->hostname = "localhost";
        $this->username = "webmovies";
        $this->password = "moviesweb";
        // Don't do this on a production system.

        $this->database_name = "webmovies";
    }

    public function parse_keyvalue($string, $symbol) {
        $array = explode($symbol, $string);

        $key = $array[0];
        unset($array[0]);

        $val = implode($symbol, $array);

        return array($key, $val);
    }

    private function connect() {
        $connected = false;

        $error_messages = array();

        $mysqli = $this->mysqli;

        if ($mysqli == null) {
            #echo "Connecting";
            $connected = false;

            $server_hostname = "localhost";

            if ($this->persistent == true) {
                $server_connect_hostname = "p:" . $server_hostname;
            } else {
                $server_connect_hostname = $server_hostname;
            }

            $mysqli = new \mysqli($server_connect_hostname, $this->username, $this->password, $this->database_name);

            #PHP 5.3+
            #
                ## connect error is unsafe - if first server errors, second server also breaks!
            $connect_error = $mysqli->connect_error;
            $sqlstate = $mysqli->sqlstate;

            if ($sqlstate !== "00000") {
                $error_messages[] = 'Connect Error ' . $server_hostname . ' (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;

                $mysqli = null;
            } else {
                $connected = true;
            }


            if ($connected == false) {
                $error_messages[] = "Connect failed - Unable to connect to any of the database servers" . "<br/>";
                $GLOBALS["cache_query"] = false;
            }

            if ($connected == false) {
                $error_message = implode("", $error_messages);
                throw new \Exception($error_message);
            } else {
                #echo "Connected";
            }
        } else {
            $connected = true;
            #echo "Connected Already";
        }
        $this->mysqli = $mysqli;

        return $connected;
    }

    private function disconnect() {
        /* close any non-persistent connections 
         * Does nothing on persistent connections
         * 
         * For non-persistent connections, disconnecting seems to break stuff.
         */
        # Persistent true + close connection = fail
        # Persistent false + close connection = fail

        if ($this->persistent == false) {
            $this->mysqli->close();
            $this->mysqli = null;
        }

        # We don't clean up persistent connection mysqli because
        # We want to continue using the same server / connection
    }

    function __destruct() {
        #print "Destroying " . $this->name . "\n";
        if ($this->persistent == true) {
            if ($this->mysqli != null) {
                $this->mysqli->close();
                $this->mysqli = null;
            }
        }
    }

    private function &execute_query_array($query) {
        $rows = array();
        if ($this->mysqli != null) {
            #var_dump($this -> mysqli);
            $result = $this->mysqli->query($query);
            if ($result != false) {
#      printf("Select returned %d rows.\n", $result->num_rows);


                while ($row = $result->fetch_array()) {
                    $rows[] = $row;
                }

                /* free result set */
                $result->close();
                $this->mysqli->next_result();
                $GLOBALS["cache_query"] = true;
            } else {
                printf("Errormessage: %s\n\n", $this->mysqli->error);
#            echo "Mysql error: " . $this->mysqli->error;
                $GLOBALS["cache_query"] = false;
                //throw new \Exception($this->mysqli->error);
            }
        } else {
            echo "Unable to execute query - no mysql connection";
            $GLOBALS["cache_query"] = false;
        }

        return $rows;
    }

    private function &execute_query_single($query) {
        $row = null;
        if ($this->mysqli != null) {

            $result = $this->mysqli->query($query);
            if ($result != false) {

                if (is_bool($result)) {
                    if ($result) {
#           echo $result;
                        return $row;
                    }
                }
#      printf("Select returned %d rows.\n", $result->num_rows);
                $row = $result->fetch_array();
                /* free result set */
                $result->close();
                $this->mysqli->next_result();
                $GLOBALS["cache_query"] = true;
            } else {
                printf("Errormessage: %s\n", $this->mysqli->error);
                $GLOBALS["cache_query"] = false;
                #throw new \Exception($this->mysqli->error);
            }
        } else {
            echo "Unable to execute query - no mysql connection";
            $GLOBALS["cache_query"] = false;
        }

#while (list($key, $value) = each($row)) {
#    echo "Key: $key; Value: $value<br />\n";
#}

        return $row;
    }

    public function make_safe($string) {
        $string = $this->mysqli->real_escape_string($string);
        $string = "'" . $string . "'";

        return $string;
    }

    public function &add_movie($movie) {
#TODO: Make safe
        $this->connect();

        $Provider = $movie->get_provider();
        $ID = $movie->get_ID();
        $hasDetails = false;
        $Title = $movie->get_title();
        $Year = $movie->get_year();
        $Rated = $movie->Rated;
        $Released = $movie->Released;
        $Genre = $movie->Genre;
        $Director = $movie->Director;
        $Writer = $movie->Writer;
        $Actors = $movie->Actors;
        $Plot = $movie->Plot;
        $Language = $movie->Language;
        $Country = $movie->Country;
        $Awards = $movie->Awards;
        $Poster = $movie->Poster;
        $Metascore = (int) $movie->Metascore;
        $Rating = (double) $movie->Rating;
        $Votes = (int) $movie->Votes;
        $Type = $movie->Type;
        $Price = (double) $movie->get_price();

        $Provider = $this->make_safe($Provider);
        $ID = $this->make_safe($ID);
        if ($hasDetails == true) {
            $hasDetails = "true";
        } else {
            $hasDetails = "false";
        }
        //$hasDetails = $this -> make_safe($hasDetails);
        $Title = $this->make_safe($Title);
        $Year = $this->make_safe($Year);
        $Rated = $this->make_safe($Rated);
        $Released = $this->make_safe($Released);
        $Genre = $this->make_safe($Genre);
        $Director = $this->make_safe($Director);
        $Writer = $this->make_safe($Writer);
        $Actors = $this->make_safe($Actors);
        $Plot = $this->make_safe($Plot);
        $Language = $this->make_safe($Language);
        $Country = $this->make_safe($Country);
        $Awards = $this->make_safe($Awards);
        $Poster = $this->make_safe($Poster);
        //$Metascore = $this->make_safe($Metascore);
        //$Rating = $this->make_safe($Rating);
        //$Votes = $this->make_safe($Votes);
        $Type = $this->make_safe($Type);
        //$Price = $this->make_safe($Price);



        $query = "CALL sp_create_movie(
            $Provider,
   $ID,
   $hasDetails,
   $Title,
   $Year,
   $Rated,
   $Released,
   $Genre,
   $Director,
   $Writer,
   $Actors,
   $Plot,
   $Language,
   $Country,
   $Awards,
   $Poster,
   $Metascore,
   $Rating,
   $Votes,
   $Type,
   $Price)";
        echo $query;
        /* Select queries return a resultset */
        /* Get the data into an array so that we can close the mysql connection */
        $result = $this->execute_query_single($query);

        $this->disconnect();

        return $result;
    }

    function &get_all_movies() {
        $movies = array();

        $this->connect();

        $query = "CALL sp_get_all_movies(  )";
        /* Select queries return a resultset */
        /* Get the data into an array so that we can close the mysql connection */
        $movies_as_array = $this->execute_query_array($query);

        $this->disconnect();


        $movies = array();
        if ($movies_as_array != null) {
            foreach ($movies_as_array as $movie_as_array) {
                $movie = new movie($movie_as_array);
                $movie_id = $movie->get_ID();
                $provider_name = $movie->get_provider();
                $movies[$provider_name][$movie_id] = $movie;
            }
        }



        return $movies;
    }

    function &get_all_movies_by_provider_keyed_by_id($provider_name) {
        $movies = array();



        $this->connect();

        $provider_name = $this->make_safe($provider_name);

        $query = "CALL sp_get_all_movies_by_provider( $provider_name )";


        /* Select queries return a resultset */
        /* Get the data into an array so that we can close the mysql connection */
        $movies_as_array = $this->execute_query_array($query);

        $this->disconnect();


        $movies = array();
        if ($movies_as_array != null) {
            foreach ($movies_as_array as $movie_as_array) {
                $movie = new movie($movie_as_array);
                $id = $movie->get_ID();
                $movies[$id] = $movie;
            }
        }



        return $movies;
    }

    function &get_all_movies_by_provider_keyed_by_name_and_year($provider_name) {
        $movies = array();



        $this->connect();

        $provider_name = $this->make_safe($provider_name);

        $query = "CALL sp_get_all_movies_by_provider( $provider_name )";


        /* Select queries return a resultset */
        /* Get the data into an array so that we can close the mysql connection */
        $movies_as_array = $this->execute_query_array($query);

        $this->disconnect();


        $movies = array();
        if ($movies_as_array != null) {
            foreach ($movies_as_array as $movie_as_array) {
                $movie = new movie($movie_as_array);
                $movie_display_name = $movie->get_display_name();
                $movies[$movie_display_name] = $movie;
            }
        }



        return $movies;
    }

    public function &update_movie($movie) {
        $Provider = $movie->get_provider();
        $ID = $movie->get_ID();
        $hasDetails = $movie -> hasDetails;
        $Title = $movie->get_title();
        $Year = $movie->get_year();
        $Rated = $movie->Rated;
        $Released = $movie->Released;
        $Genre = $movie->Genre;
        $Director = $movie->Director;
        $Writer = $movie->Writer;
        $Actors = $movie->Actors;
        $Plot = $movie->Plot;
        $Language = $movie->Language;
        $Country = $movie->Country;
        $Awards = $movie->Awards;
        $Poster = $movie->Poster;
        $Metascore = (int) $movie->Metascore;
        $Rating = (double)  $movie->Rating;
        $Votes = (int) $movie->Votes;
        $Type = $movie->Type;
        $Price = (double) $movie->get_price();

        $this->connect();


        $Provider = $this->make_safe($Provider);
        $ID = $this->make_safe($ID);
        if ($hasDetails == true) {
            $hasDetails = "true";
        } else {
            $hasDetails = "false";
        }
        //$hasDetails = $this -> make_safe($hasDetails);
        $Title = $this->make_safe($Title);
        $Year = $this->make_safe($Year);
        $Rated = $this->make_safe($Rated);
        $Released = $this->make_safe($Released);
        $Genre = $this->make_safe($Genre);
        $Director = $this->make_safe($Director);
        $Writer = $this->make_safe($Writer);
        $Actors = $this->make_safe($Actors);
        $Plot = $this->make_safe($Plot);
        $Language = $this->make_safe($Language);
        $Country = $this->make_safe($Country);
        $Awards = $this->make_safe($Awards);
        $Poster = $this->make_safe($Poster);
        //$Metascore = $this->make_safe($Metascore);
        //$Rating = $this->make_safe($Rating);
        //$Votes = $this->make_safe($Votes);
        $Type = $this->make_safe($Type);
        //$Price = $this->make_safe($Price);

        $movieid = 0;

        $query = "CALL sp_update_movie(
$Provider,
   $ID,
   $hasDetails,
   $Title,
   $Year,
   $Rated,
   $Released,
   $Genre,
   $Director,
   $Writer,
   $Actors,
   $Plot,
   $Language,
   $Country,
   $Awards,
   $Poster,
   $Metascore,
   $Rating,
   $Votes,
   $Type,
   $Price)";
        echo $query;

        /* Select queries return a resultset */
        /* Get the data into an array so that we can close the mysql connection */
        $countryid_in_array = $this->execute_query_single($query);

        $this->disconnect();

        return $countryid_in_array;
    }

}
?>
