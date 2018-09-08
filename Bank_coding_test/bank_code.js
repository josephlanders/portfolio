class Account {

    constructor(account_id)
    {
        this.account_id = account_id;
        this.customers = new Map();
        this.customers_ids = new Map();
    }
    
     add_customer(customer)
    {
        var customer_id = customer.customer_id;
        this.customers_ids.set(customer_id, customer_id);
    }
    
     get_customers_as_map_by_id()
    {
        return this.customers_ids;
    }
}

class Customer {
    constructor(customer_id)
    {
        this.customer_id = customer_id;
        this.accounts = new Map();
        this.accounts_ids = new Map();
    }
    
    add_account(account)
    {
        var account_id = account.account_id;
        this.accounts_ids.set(account_id, account_id);
    }
    
    get_accounts_as_map_by_id()
    {
        return this.accounts_ids;
    }
}


class Bank_code {
    
    constructor()
    {
        this.customers = new Map();
        this.accounts = new Map();
        this.read_data_in();
        
        var customers_that_share_the_same_accounts = [];
        
        for (var [key, customer] of this.customers) {
            
            var cust_accounts_by_id = customer.get_accounts_as_map_by_id();

            var customers_sharing_accounts = new Map();
            
            for (var [key2, account_by_id] of cust_accounts_by_id) {
                var account = this.accounts.get(key2);
                var customers_by_id = account.get_customers_as_map_by_id();
                
                for (var [key3, customer_id] of customers_by_id) {
                   customers_sharing_accounts.set(customer_id, customer_id); 
                }
            }
            
            customers_that_share_the_same_accounts.push(customers_sharing_accounts);
        }
        
        var x = "";
        for (x in customers_that_share_the_same_accounts) {
            var list_of_customers = customers_that_share_the_same_accounts[x];
            var line = "Customers: ";
                for (var [key2, customer_id] of list_of_customers) {
                    line += customer_id + ",";
                }
        
            console.log(line);
        }
    }
    
    read_data_in()
    {
        let customer = new Customer(1);
        let customer2 = new Customer(2);
        let customer3 = new Customer(3);
        let customer4 = new Customer(4);
        let customer5 = new Customer(5);
        let account = new Account(101);
        let account2 = new Account(102);
        let account3 = new Account(103);
        let account4 = new Account(104);
        let account5 = new Account(105);
        let account6 = new Account(106);

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

        this.customers.set(customer.customer_id, customer);
        this.customers.set(customer2.customer_id, customer2);
        this.customers.set(customer3.customer_id, customer3);
        this.customers.set(customer4.customer_id, customer4);
        this.customers.set(customer5.customer_id, customer5);

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

        this.accounts.set(account.account_id, account);
        this.accounts.set(account2.account_id, account2);
        this.accounts.set(account3.account_id, account3);
        this.accounts.set(account4.account_id, account4);
        this.accounts.set(account5.account_id, account5);
    }
}


let bank_code = new Bank_code(); 
 
