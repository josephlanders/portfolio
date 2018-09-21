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

         */
        ?>
