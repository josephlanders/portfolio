<?php
namespace client_code;
/* This file and the code within it are distributed under commercial license.
 * Permission must be granted from the 
 * original author / copyright holder of the file before 
 * accessing, using, compiling, interpreting, modifying and redistributing the code.
 * 
 * email: josephlanders@gmail.com - visit contact.txt in root folder for more
 */
?>

<?php
require_once("sortable.php");
class user  implements sortable  {

    public $forename = "";
    public $surname = "";
    public $userid = 0;
    public $username = null;
    public $accountstate = 0;
    public $deactivationdate = null;
    public $signupdate = null;
    public $is_admin_user = false;
    private $comparator = 0;

    public function __construct(array $fields) {
        if ($fields == null) {
            return;
        }
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }
    }

    public function get_forename() {
        return $this->forename;
    }

    public function set_forename($forename) {
        $this->forename = $forename;
        return true;
    }

    public function get_username() {
        return $this->username;
    }

    public function set_username($username) {
        $this->username = $username;
        return true;
    }

    public function get_userid() {
        return $this->userid;
    }

    public function set_userid($userid) {
        $this->userid = $userid;
        return true;
    }
    
    public function set_comparator($value) {
        $this->comparator = $value;
    }

    public function get_comparator() {
        return $this->comparator;
    }

    public function compareTo($product) {
        $comp = $product->get_comparator();
        return $this->comparator - $comp;
    }

}
?>