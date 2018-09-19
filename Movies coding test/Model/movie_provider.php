<?php

namespace moviesite;

class movie_provider {

    private $server_address = "";
    public $api_token = "";
    public $movies_list_address = "";
    public $movie_detail_address = "";
    public $name = "";
    public $connect_timeout = 5;
    public $timeout = 30;
    public $use_json_format = true;

    function __construct($fields) {
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }
    }

}
