<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/list_movies.php");
require_once(dirname(__FILE__) . "/movie_detail.php");

class view {

    //put your code here
    public function __construct() {
        
    }

    public function make_headers($page, $data, $movie_page) {
        ?>
        <meta charset="UTF-8">
        <title><?php echo $movie_page->title(); ?></title>
        <?php
        ?>
        <?php
    }

    public function draw_page($page, $data) {
        $movie_page = null;
        if ($page == "movie_detail") {
            $movie_page = new \moviesite\movie_detail($data);
        }

        if ($page == "list_movies") {
            $movie_page = new \moviesite\list_movies($data);
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
        <?php
        $this->make_headers($page, $data, $movie_page);
        ?>
            </head>
            <body>
        <?php
        $page_content = "";
        $movie_page->draw($data);
        ?>

            </body>    
        </html>
                <?php
                return $page_content;
            }

        }
        