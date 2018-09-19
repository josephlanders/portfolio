<?php

namespace moviesite;

/**
 * Description of view
 *
 * @author z
 */
class list_movies {

    public function __construct() {
        
    }

    public function draw($data) {
        $all_movies_list = $data["all_movies_list"];
        $error_messages = $data["error_messages"];
        ?>
        <div style="float:left;clear:both;width:100%;height:100%;">
            <div style="float:left;clear:both;">
                <text style="color: red;">
                <?php
                foreach ($error_messages as $key => $error_message)
                {
                    echo $error_message . "<br/>";
                }
                ?>
                </text>
            </div>
            <?php
            foreach ($all_movies_list as $movie_name_and_year => $movie_merged) {
                ?>

                <?php
                foreach ($movie_merged as $provider_name => $movie_by_provider) {
                    ?>

                    <div style="float:left;clear:left;width:800px;height:300px;padding:20px;margin-left:25%;margin-right:25%;margin-top:10px;border:grey 1px solid">
                        <div style="float:left;width:400px;">
                            <div style="float:left;"><h2 style="font-size: 15px;">Provider: <?php echo $provider_name; ?></h2></div> 
                            <div style="float:left;"><h2 style="font-size: 15px;">"<?php echo $movie_by_provider->get_display_name(); ?>"</h2></div>
                        </div>
                        <div style="float:left;width:200px;height:300px;margin-left:20px;">
                        <a href="/movie_detail.php?provider=<?php echo $provider_name; ?>&id=<?php echo $movie_by_provider->get_ID(); ?>">
                            <img style="float:left;width:200px;height:282px;border:grey 1px solid" alt="<?php echo $movie_by_provider->get_display_name(); ?>" style="float:left;width:150px;height:225px;" src="<?php echo $movie_by_provider->get_poster(); ?>" />
                        </a>
                        </div>

                    </div>


                    <?php
                }
                ?>

                <?php
            }
            ?>
        </div>
        <?php
    }

}
