package main

import (  
    "fmt"
)

type Customer struct {  
    customer_id int
    cust_accounts map[int]Account
    cust_accounts_ids map[int]int
}

type Account struct {  
    account_id int
    acc_customers_ids map[int]int
}

var customers map[int]Customer
var accounts map[int]Account

func main() {
    fmt.Println("Hi")
    
    customers = make(map[int]Customer);
    accounts = make(map[int]Account);
    
    read_data_in();
    
    customers_that_share_the_same_accounts := make([]map[int]int, 10);
           
    for _, customer := range customers {

        cust_accounts_by_id := customer.get_accounts_as_map_by_id();

        customers_sharing_accounts := make(map[int]int);
                       
        for _, account_by_id := range cust_accounts_by_id {
            account := accounts[account_by_id];             
            customers_by_id := account.get_customers_as_map_by_id();
 
            // HashMap Merge
            for _, customer_id := range customers_by_id {
                customers_sharing_accounts[customer_id] = customer_id;
            }
        }

        customers_that_share_the_same_accounts = append(customers_that_share_the_same_accounts, customers_sharing_accounts)
    }
           
    for _, list_of_customers := range customers_that_share_the_same_accounts {
        fmt.Print("\nCustomers: ");
        for _, customer_id := range list_of_customers {
            fmt.Print(customer_id);
                fmt.Print(",");
        }
    }
}

func read_data_in() {
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
        customer := New_customer(1);
         customer2 := New_customer(2);
         customer3 := New_customer(3);
         customer4 := New_customer(4);
         customer5 := New_customer(5);
         account := New_account(101);
         account2 := New_account(102);
         account3 := New_account(103);
         account4 := New_account(104);
         account5 := New_account(105);
         //account6 := New_account(106);

        // Customer 1 has account 101,102
        customer.add_account(account);
        customer.add_account(account2);

        // Customer 2 has account 102,103,104
        customer2.add_account(account2);
        customer2.add_account(account3);
        customer2.add_account(account4);

        // Customer 3 has account 101,104
        customer3.add_account(account);
        customer3.add_account(account4);

        // Customer 4 has account 105
        customer4.add_account(account5);

        // Customer 5 has accounts 103,105
        customer5.add_account(account3);
        customer5.add_account(account5);

        customers[customer.customer_id] = customer;
        customers[customer2.customer_id] = customer2;
        customers[customer3.customer_id] = customer3;
        customers[customer4.customer_id] = customer4;
        customers[customer5.customer_id] = customer5;

        // Account 101 has customer 1,3
        account.add_customer(customer);
        account.add_customer(customer3);

        // Account 102 has customer 1,2
        account2.add_customer(customer);
        account2.add_customer(customer2);

        // Account 103 has customer 2,5
        account3.add_customer(customer2);
        account3.add_customer(customer5);

        // Account 104 has customer 2,3
        account4.add_customer(customer2);
        account4.add_customer(customer3);

        // Account 105 has customer 4,5
        account5.add_customer(customer4);
        account5.add_customer(customer5);

        accounts[account.account_id] = account;
        accounts[account2.account_id] = account2;
        accounts[account3.account_id] = account3;
        accounts[account4.account_id] = account4;
        accounts[account5.account_id] = account5;


//fmt.Println("map:", customers);
//fmt.Println("map:", accounts);

        //return ret;
    
}

func New_customer(customer_id int) Customer {  
    m := make(map[int]Account);
    n := make(map[int]int);
    e := Customer {customer_id, m, n}
    return e
}

func New_account(account_id int) Account {  
    m := make(map[int]int);
    e := Account {account_id, m}
    return e
}

func (a Account) add_customer(customer Customer) {
    customer_id := customer.customer_id;
   a.acc_customers_ids[customer_id] = customer_id;
}

func (c Customer) add_account(account Account) {
    account_id := account.account_id;
   c.cust_accounts[account_id] = account;
   c.cust_accounts_ids[account_id] = account_id;
}

func (a Account) get_customers_as_map_by_id() map[int]int {
    return a.acc_customers_ids;
}

func (c Customer) get_accounts_as_map_by_id() map[int]int {
    return c.cust_accounts_ids;
}
