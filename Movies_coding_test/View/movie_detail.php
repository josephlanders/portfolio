<?php

namespace moviesite;

class movie_detail {

    private $data = array();
    public function __construct($data) {
        $this -> data = $data;
    }

    public function draw() {
        $data = $this -> data;
        $provider_name = $data["provider_name"];
        $movie = $data["movie"];
        $alternate_movies = $data["alternate_movies"];
        $error_messages = $data["error_messages"];
        ?>



        <div style="float:left;clear:left;margin-left:200px;width:800px;;padding:20px;border:grey 1px solid">
            <div style="float:left;clear:both;">
                <text style="color: red;">
                <?php
                foreach ($error_messages as $key => $error_message) {
                    echo $error_message . "<br/>";
                }
                ?>
                </text>
            </div>
            <div style="float:left;clear:both;"><a href="/list_movies.php">Back to movies listing</a></div>
            <div style="float:left;clear:left;margin-top:20px;">
                <div style="float:left;">
                    <h2 style="float:left;width:200px;font-size: 15px;">Provider: <?php echo $provider_name; ?></h2>
                    <h2 style="float:left;clear:left;width:200px;font-size: 15px;"><?php echo $movie->get_display_name(); ?></h2>
                    <h2 style="float:left;clear:left;width:200px;font-size: 15px;"><?php echo $movie->get_display_price(); ?></h2>
                    <p style="float:left;clear:left;width:200px;font-size: 15px;"><?php echo $movie->Plot; ?></p>
                </div>
                <img style="float:left;height:450px;width:300px;margin-left:20px;border:grey 1px solid" alt="<?php echo $movie->get_display_name(); ?>" style="float:left;width:150px;height:225px;" src="<?php echo $movie->get_poster(); ?>" />                        
            </div>
            <div style="float:left;margin-left:100px;margin-top:100px;">
                <h3>Similar movies from other providers </h3>
                <?php
                foreach ($alternate_movies as $movie_name_and_year => $provider_and_movie) {
                    foreach ($provider_and_movie as $provider_name => $movie_by_provider) {
                        ?>
                        <div style="float:left;width:150px;height:350px;padding:20px;">

                            <a href="/movie_detail.php?provider=<?php echo $provider_name; ?>&id=<?php echo $movie_by_provider->get_id(); ?>" />
                            <img style="float:left;width:150px;height:225px;border:grey 1px solid" alt="<?php echo $movie_by_provider->get_display_name(); ?>" src="<?php echo $movie_by_provider->get_poster(); ?>" />
                            </a>
                            <div style="float:left;width:150px;height:125px;">                                
                                <h5><?php echo $provider_name; ?></h5>
                                <h5><?php echo $movie_by_provider->get_display_name(); ?></h5>
                                <h5><?php echo $movie_by_provider->get_display_price(); ?></h5>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div style="float:left;clear:both;margin-top:20px;">Movie details retrieved from provider at: <?php echo $movie->datemodified; ?></div>
            <div style="float:left;clear:both;">Data retrieval time: <?php echo $data["controller_time"]; ?></div>
        </div>
        <?php
    }
    
    public function title()
    {
        $data = $this -> data;
        $provider_name = $data["provider_name"];
        $movie = $data["movie"];
        echo $movie -> get_display_name() . " by " . $provider_name;
    }
}
