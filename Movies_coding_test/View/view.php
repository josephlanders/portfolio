<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/list_movies.php");
require_once(dirname(__FILE__) . "/movie_detail.php");

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
