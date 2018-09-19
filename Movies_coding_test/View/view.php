<?php

namespace moviesite;

require_once("View\list_movies.php");
require_once("View\movie_detail.php");

/**
 * Description of view
 *
 * @author z
 */
class view {
    //put your code here
    public function __construct()
    {

    }    
    
    public function make_headers()
    {
        $headers = "";
        ?>
<?php
    }
    
    public function draw_page($page, $data)
    {
        
        ?>
<html>
    <head>
<?php
        $this -> make_headers();
        ?>
    </head>
    <body>
        <?php
        
        $page_content = "";
        if ($page == "movie_detail")
        {
            $movie_detail = new \moviesite\movie_detail();
            $movie_detail -> draw($data);
        }
        
        if ($page == "list_movies")
        {
            $list_movies = new \moviesite\list_movies();
            $list_movies -> draw($data);
        }
        
        ?>
    </body>
</html>
    <?php
        
        return $page_content;

    }
}
