<?php

namespace moviesite;

require_once(dirname(__FILE__) . "/Model/model.php");
require_once(dirname(__FILE__) . "/Controller/controller.php");
require_once(dirname(__FILE__) . "/View/view.php");

class moviethingy {

    private $model = null;
    private $controller = null;
    private $view = null;
    
    public function __construct() {
        $this->model = new \moviesite\model();
        $this->controller = new \moviesite\controller($this -> model);
        $this->view = new \moviesite\view();
    }

    public function routing($page) {
        $view = $this->view;
        $controller = $this -> controller;
        
        $before = microtime(true);
        
        $data = $controller -> control_stuff($page);
        
        $after = microtime(true);
        
        $total_time = $after - $before;
        
        "time taken: " . $total_time;

        $data["controller_time"] = $total_time;
        $view -> draw_page($page, $data);        
    }
}
?>


        <?php
        
          /*
           * 
           <!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
<?php
//
?>
    </body>
</html>

          array(19) {
          ["Title"]=>
          string(44) "Star Wars: Episode II - Attack of the Clones"
          ["Year"]=>
          string(4) "2002"
          ["Rated"]=>
          string(2) "PG"
          ["Released"]=>
          string(11) "16 May 2002"
          ["Runtime"]=>
          string(7) "142 min"
          ["Genre"]=>
          string(26) "Action, Adventure, Fantasy"
          ["Director"]=>
          string(12) "George Lucas"
          ["Writer"]=>
          string(79) "George Lucas (screenplay), Jonathan Hales (screenplay), George Lucas (story by)"
          ["Actors"]=>
          string(67) "Ewan McGregor, Natalie Portman, Hayden Christensen, Christopher Lee"
          ["Plot"]=>
          string(214) "Ten years after initially meeting, Anakin Skywalker shares a forbidden romance with Padmé, while Obi-Wan investigates an assassination attempt on the Senator and discovers a secret clone army crafted for the Jedi."
          ["Language"]=>
          string(7) "English"
          ["Country"]=>
          string(3) "USA"
          ["Poster"]=>
          string(143) "https://images-na.ssl-images-amazon.com/images/M/MV5BNDRkYzA4OGYtOTBjYy00YzFiLThhYmYtMWUzMDBmMmZkM2M3XkEyXkFqcGdeQXVyNDYyMDk5MTU@._V1_SX300.jpg"
          ["Metascore"]=>
          string(2) "54"
          ["Rating"]=>
          string(3) "6.7"
          ["Votes"]=>
          string(7) "469,134"
          ["ID"]=>
          string(9) "fw0121765"
          ["Type"]=>
          string(5) "movie"
          ["Price"]=>
          string(6) "1249.5"
          }
         */

                /*
         * array(20) {
          ["Title"]=>
          string(44) "Star Wars: Episode III - Revenge of the Sith"
          ["Year"]=>
          string(4) "2005"
          ["Rated"]=>
          string(5) "PG-13"
          ["Released"]=>
          string(11) "19 May 2005"
          ["Runtime"]=>
          string(7) "140 min"
          ["Genre"]=>
          string(26) "Action, Adventure, Fantasy"
          ["Director"]=>
          string(12) "George Lucas"
          ["Writer"]=>
          string(12) "George Lucas"
          ["Actors"]=>
          string(65) "Ewan McGregor, Natalie Portman, Hayden Christensen, Ian McDiarmid"
          ["Plot"]=>
          string(192) "During the near end of the clone wars, Darth Sidious has revealed himself and is ready to execute the last part of his plan to rule the Galaxy. Sidious is ready for his new apprentice, Lord..."
          ["Language"]=>
          string(7) "English"
          ["Country"]=>
          string(3) "USA"
          ["Awards"]=>
          string(56) "Nominated for 1 Oscar. Another 25 wins & 51 nominations."
          ["Poster"]=>
          string(96) "http://ia.media-imdb.com/images/M/MV5BNTc4MTc3NTQ5OF5BMl5BanBnXkFtZTcwOTg0NjI4NA@@._V1_SX300.jpg"
          ["Metascore"]=>
          string(2) "68"
          ["Rating"]=>
          string(3) "7.6"
          ["Votes"]=>
          string(7) "522,705"
          ["ID"]=>
          string(9) "cw0121766"
          ["Type"]=>
          string(5) "movie"
          ["Price"]=>
          string(5) "125.5"
          }
          
        
         * array(20) {
          ["Title"]=>
          string(44) "Star Wars: Episode III - Revenge of the Sith"
          ["Year"]=>
          string(4) "2005"
          ["Rated"]=>
          string(5) "PG-13"
          ["Released"]=>
          string(11) "19 May 2005"
          ["Runtime"]=>
          string(7) "140 min"
          ["Genre"]=>
          string(26) "Action, Adventure, Fantasy"
          ["Director"]=>
          string(12) "George Lucas"
          ["Writer"]=>
          string(12) "George Lucas"
          ["Actors"]=>
          string(65) "Ewan McGregor, Natalie Portman, Hayden Christensen, Ian McDiarmid"
          ["Plot"]=>
          string(192) "During the near end of the clone wars, Darth Sidious has revealed himself and is ready to execute the last part of his plan to rule the Galaxy. Sidious is ready for his new apprentice, Lord..."
          ["Language"]=>
          string(7) "English"
          ["Country"]=>
          string(3) "USA"
          ["Awards"]=>
          string(56) "Nominated for 1 Oscar. Another 25 wins & 51 nominations."
          ["Poster"]=>
          string(96) "http://ia.media-imdb.com/images/M/MV5BNTc4MTc3NTQ5OF5BMl5BanBnXkFtZTcwOTg0NjI4NA@@._V1_SX300.jpg"
          ["Metascore"]=>
          string(2) "68"
          ["Rating"]=>
          string(3) "7.6"
          ["Votes"]=>
          string(7) "522,705"
          ["ID"]=>
          string(9) "cw0121766"
          ["Type"]=>
          string(5) "movie"
          ["Price"]=>
          string(5) "125.5"
          }
         */
        ?>
