<?php

namespace moviesite;

class controller {
    private $model = null;
    public function __construct($model)
    {
        $this -> model = $model;
    }   
    
    public function control_stuff($page) {
        $model = $this->model;
        $data = array();

        if ($page == "list_movies") {
            list($all_movies_list, $error_messages) = $model->get_collated_movie_list();
            
            $data = array("all_movies_list" => $all_movies_list, "error_messages" => $error_messages);           
        }

        if ($page == "movie_detail") {
            $keep = array("provider_name", "id");
            $postback = $model->process_postback($keep);
            $provider_name = $postback["provider"];
            $id = $postback["id"];
            
            list($movie, $error_messages) = $model->get_movie_with_details_attached($provider_name, $id);
            $alternate_movies = array();
            
            
            
            if ($movie != null) {
                $name = $movie->get_title();
                $year = $movie->get_year();

                list($alternate_movies, $alternative_error_messages) = $model->get_alternate_movies($provider_name, $name, $year);
                
                // Ignore these messages, less important
                //$error_messages = array_merge($error_messages, $alternative_error_messages);
            }
            
            if ($movie == null)
            {
                $movie = new \moviesite\movie(array());
            }

            $data = array("movie" => $movie, "alternate_movies" => $alternate_movies, "provider_name" => $provider_name,
                "error_messages" => $error_messages);
        }
        
        return $data;
    }

}
