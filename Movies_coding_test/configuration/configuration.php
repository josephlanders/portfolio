<?php

namespace shared_code;

class configuration {

    private $config_array = array();
    private $line_ending = "\n";
    public $filename = "";

    public function __construct($filename) {

        if ($filename != null) {
            $this->read_file($filename);
        }
    }

    public function read_file($filename) {
        #var_dump($filename);
        #key value
        #key=value
        /* Open - read into array - close */
        if ($filename == null) {
            #die("Configuration filename can't be null - you must use an existing config");
            return;
        }
        $this->filename = $filename;

        $handle = @fopen($filename, "r");
        if ($handle == NULL) {
            echo "Failed to open configuration file " . $filename;
            return;
        }

        while (($buffer = fgets($handle, 4096)) !== false) {
            #trim \n from file for WINDOWS we must trim \r\n
            #$buffer = rtrim($buffer,$this -> line_ending);
            $buffer = rtrim($buffer);

            if (mb_substr($buffer, 0, 1) == "#") {
                continue;
            }
            if ($buffer == "") {
                continue;
            }

            list ($setting, $value) = $this->parse_keyvalue($buffer, "=");

            $this->config_array["$setting"] = $value;
        }

        #var_dump($this -> config_array);
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($handle);
    }

    public function get_setting($setting) {
        $str = null;
        if (isset($this->config_array["$setting"])) {
            $str = $this->config_array["$setting"];
        }
        return $str;
    }

    public function get_setting_boolean_as_string($setting) {
        #var_dump($this -> config_array);
        $str = $this->get_setting($setting);
        #echo $str;
        if ($str == "false") {
            return false;
        } else if ($str == "true") {
            return true;
        }
        return false;
    }

    public function get_settings() {
        return $this->config_array;
    }

    # Set from a simple key => value array

    public function set_settings(array $fields) {

        foreach ($fields as $value) {
            if (is_bool($value)) {
                if ($value == true) {
                    $value = "true";
                } else if ($value == false) {
                    $value = "false";
                }
            }
        }
        #$this -> config_array = $fields;
        $this->config_array = array_merge($this->config_array, $fields);
        $this->save();
    }

    public function set_settings_from_db_array(array $fields) {

        # THis is an array of rows of data 
        # Such that it is an array of arrays,
        # $array($row1, $row2, $row3); 
        # Where $row1 = array("keyname" => "asbc", "value" => "asddgfasd");

        $settings_simple_array = array();
        foreach ($fields as $key => $row) {
            $keyname = $row["keyname"];
            $keyvalue = $row["value"];

            $settings_simple_array[$keyname] = $keyvalue;
        }


        foreach ($settings_simple_array as $value) {
            if (is_bool($value)) {
                if ($value == true) {
                    $value = "true";
                } else if ($value == false) {
                    $value = "false";
                }
            }
        }
        #$this -> config_array = $fields;
        $this->config_array = array_merge($this->config_array, $settings_simple_array);
        $this->save();
    }

    public function set_setting($key, $value) {
        $this->config_array[$key] = $value;
        $this->save();
    }

    public function set_setting_boolean_as_string($key, $value) {
        if ($value == true) {
            $value = "true";
        } else if ($value == false) {
            $value = "false";
        }
        $this->config_array[$key] = $value;
        $this->save();
    }

    public function update_settings(array $fields) {
        $this->config_array = array_merge($this->config_array, $fields);
    }

    public function parse_keyvalue($string, $symbol) {
        $array = explode($symbol, $string);

        $key = $array[0];
        unset($array[0]);

        $val = implode($symbol, $array);

        return array($key, $val);
    }

    function save() {
        if ($this->filename != null) {
            #$handle = fopen($this->filename, "w");
            $lines = array();

            #if ($handle != null) {
            #var_dump($this -> config_array);
            foreach ($this->config_array as $key => $value) {
                #Security measure - truncate on \n to avoid field\nsite_id\npaypal_config\n being inserted
                # Some lines terminate with \0 and no \n
                # Some lines terminate \n\0
                if (strstr($value, "\n" == true)) {
                    $value = strstr($value, "\n", true);
                }

                $lines[] = $key . "=" . $value . "\n";
                #echo "=";
                #echo $value;
                #echo "\n";
                #echo "<br/>";
            }
            #fflush($handle);
            #fclose($handle);
            #}
            #			$this -> config_array["$setting"] = $value;
            file_put_contents($this->filename, $lines);
        }
    }

    function save_old() {
        $handle = fopen($this->filename, "w");

        if ($handle != null) {
            #var_dump($this -> config_array);
            foreach ($this->config_array as $key => $value) {
                #Security measure - truncate on \n to avoid field\nsite_id\npaypal_config\n being inserted
                # Some lines terminate with \0 and no \n
                # Some lines terminate \n\0
                if (strstr($value, "\n" == true)) {
                    $value = strstr($value, "\n", true);
                }
                fwrite($handle, $key . "=" . $value . "\n");
                #echo "=";
                #echo $value;
                #echo "\n";
                #echo "<br/>";
            }
            fflush($handle);
            fclose($handle);
        }
        #			$this -> config_array["$setting"] = $value;
    }

    function dump() {
        foreach ($this->config_array as $key => $value) {
            echo $key;
            echo " ";
            echo $value;
            echo "<br/>";
        }
        #			$this -> config_array["$setting"] = $value;
    }

}
?>

