<?php //php 7.0.8

$code_thing = new code_thing();
   
 class code_thing
    {
        private $customers = array();
        private $accounts = array();
       
        public function __construct()
        {
            list($customers, $accounts) = $this -> read_data_in();
            $this -> customers = $customers;
            $this -> accounts = $accounts;
            $this -> process_data($customers, $accounts);
        }

        public function process_data($customers, $accounts)
        {
            $customers_that_share_the_same_accounts = array();
            //$customers = $this -> customers;
            //$accounts = $this -> accounts;
           
            foreach($customers as $key => $customer)
            {
                //$cust_accounts = $customer -> get_accounts();
                $cust_accounts_by_id = $customer -> get_accounts_as_list_by_id();

                $customers_sharing_accounts = array();
                                                   
                foreach($cust_accounts_by_id as $key2 => $account_by_id)
                {
                    $account = $this -> accounts[$account_by_id];
                    $customers_by_id = $account -> get_customers_as_list_by_id();
                   
                    $customers_sharing_accounts = $customers_sharing_accounts + $customers_by_id;
                }

        $customers_that_share_the_same_accounts[] = $customers_sharing_accounts;
            }
           
            //var_dump($customers_that_share_the_same_accounts);
           
            foreach($customers_that_share_the_same_accounts as $key => $list_of_customers)
            {
               
                echo "\nCustomers: ";
                foreach($list_of_customers as $key2 => $customer_id)
                {
                    echo ($customer_id);
                    echo ",";
                }
            }
           
        }

        public function read_data_in()
        {
            // foreach $customer $accounts
           
            /*
            1 => 101, 102
            2 => 102, 103, 104
            3 => 101, 104
            4 => 105
            5 => 103, 105
           
            (1 shares with) 101 => 1,3, 102 => 1,2 answer = 1,3,2
            (2 shares with) 102 => 1,2  103 => 2,5  104 => 2,3  answer 1,2,5,3
            (3 shares with) 101 => 1,3  104 => 2,3  answer 1,3,2
            (4 shares with) 105 => 4,5   answer 4,5
            (5 shares with) 103 => 2,5  105 => 4,5 answer 2,5,4
           
            output 1,3,2
            1,2,5,3
            1,3,2
            4,5
            2,5,4
             */
           
            $customer = new Customer("1");
            $customer2 = new Customer("2");
            $customer3 = new Customer("3");
            $customer4 = new Customer("4");
            $customer5 = new Customer("5");
            $account = new Account("101");
            $account2 = new Account("102");
            $account3 = new Account("103");
            $account4 = new Account("104");
            $account5 = new Account("105");
            $account6 = new Account("106");
           
            // Customer 1 has account 101,102
            $customer -> add_account($account);
            $customer -> add_account($account2);
           
            // Customer 2 has account 102,103,104
            $customer2 -> add_account($account2);
            $customer2 -> add_account($account3);
            $customer2 -> add_account($account4);
           
            // Customer 3 has account 101,104
            $customer3 -> add_account($account);
            $customer3 -> add_account($account4);
           
            // Customer 4 has account 105
            $customer4 -> add_account($account5);
           
            // Customer 5 has accounts 103,105
            $customer5 -> add_account($account3);
            $customer5 -> add_account($account5);
           
            $customers[$customer -> customer_id] = $customer;
            $customers[$customer2 -> customer_id] = $customer2;
            $customers[$customer3 -> customer_id] = $customer3;
            $customers[$customer4 -> customer_id] = $customer4;        
            $customers[$customer5 -> customer_id] = $customer5;        
           
            // Account 101 has customer 1,3
            $account -> add_customer($customer);
            $account -> add_customer($customer3);
           
            // Account 102 has customer 1,2
            $account2 -> add_customer($customer);
            $account2 -> add_customer($customer2);
           
            // Account 103 has customer 2,5
            $account3 -> add_customer($customer2);
            $account3 -> add_customer($customer5);           
           
            // Account 104 has customer 2,3
            $account4 -> add_customer($customer2);
            $account4 -> add_customer($customer3);
           
            // Account 105 has customer 4,5
            $account5 -> add_customer($customer4);
            $account5 -> add_customer($customer5);
           
           
            $accounts[$account -> account_id] = $account;
            $accounts[$account2 -> account_id] = $account2;
            $accounts[$account3 -> account_id] = $account3;
            $accounts[$account4 -> account_id] = $account4;
            $accounts[$account5 -> account_id] = $account5;
           
            //var_dump($customers);
            return array($customers, $accounts);
        }       
    }
               
    class customer
    {
        private $accounts_obj = array();
        private $accounts_id = array();
        public $customer_id = null;
       
        public function __construct($customer_id)
        {
            $this -> customer_id = $customer_id;
        }
       
       
        public function add_account($account)
        {
            $accounts_obj = $this -> accounts_obj;
           
            $account_id = $account -> account_id;
            $accounts_obj["$account_id"] = $account;
           
            $this -> accounts_obj = $accounts_obj;
        }
       
        public function get_accounts_as_list_by_id()
        {
            $accounts_as_list = array();
            foreach($this -> accounts_obj as $key => $cust_account)
            {
                $account_id = $cust_account -> account_id;
                $accounts_as_list[$account_id] = $account_id;
            }
           
            //var_dump($accounts_as_list);
           
            return $accounts_as_list;
        }
    }

    class account
    {
        private $customer_ids = array();
        public $account_id = null;
       
        public function __construct($account_id)
        {
            $this -> account_id = $account_id;
        }
       
        public function add_customer($customer)
        {
            $customer_ids = $this -> customer_ids;
            $customer_id = $customer -> customer_id;
            $customer_ids[$customer_id] = $customer_id;
           
            $this -> customer_ids = $customer_ids;
        }
       
        public function get_customers_as_list_by_id()
        {
            return $this -> customer_ids;
        }
    }
     
                   
                   
             
   
?>
