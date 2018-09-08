class Customer(val customer_id: Int)                                  
{
    val accounts_ids = HashMap<Int, Int>();
    
    fun add_account(account: Account)
    {
        val account_id = account.account_id;
        accounts_ids.put(account_id, account_id);
    }
    
    fun get_accounts_as_map_by_id() : HashMap<Int, Int>
    {
        return this.accounts_ids;
    }
}

class Account(val account_id: Int)
{
    val customers_ids = HashMap<Int, Int>();
    
    fun add_customer(customer: Customer)
    {
        val customer_id = customer.customer_id;
        customers_ids.put(customer_id, customer_id);
    }
    
    fun get_customers_as_map_by_id() : HashMap<Int, Int>
    {
        return this.customers_ids;
    }
}

//val b = Bank_code();

fun main(args: Array<String>) {
    val b = Bank_code();
    b.process_data();
    //println("Hello, World!")
}

class Bank_code()
{
	val customers = HashMap<Int, Customer>();
	val accounts = HashMap<Int, Account>();

    /*
	fun main(args: Array<String>) {
    	println("Hello, World!")     
	} */
    
    fun process_data()
    {
        read_data_in();
        val customers_that_share_the_same_accounts = ArrayList<HashMap<Int, Int>>();
        
        for ((key, customer) in customers)
        {
            val cust_accounts_by_id = customer.get_accounts_as_map_by_id();
            
            val customers_sharing_accounts = HashMap<Int, Int>();
            
            for ((key2, account_by_id) in cust_accounts_by_id)
            {                
                val account = this.accounts.get(account_by_id);
                if (account == null)
                {
                    continue;
                }
                val customers_by_id = account.get_customers_as_map_by_id();                                
                
                for ((key3, customer_id) in customers_by_id)
				{           
                   customers_sharing_accounts.put(customer_id, customer_id); 
                }
                
            	
			}
			customers_that_share_the_same_accounts.add(customers_sharing_accounts);
		}
        
        for (list_of_customers in customers_that_share_the_same_accounts) {            
            var line = "Customers: ";
            for ((key, customer_id) in list_of_customers)
            {
                line += "${customer_id},";
            }
        
            System.out.println(line);
        }
	}

	fun read_data_in()
	{
        val customer = Customer(1);
        val customer2 = Customer(2);
        val customer3 = Customer(3);
        val customer4 = Customer(4);
        val customer5 = Customer(5);
        val account = Account(101);
        val account2 = Account(102);
        val account3 = Account(103);
        val account4 = Account(104);
        val account5 = Account(105);
        val account6 = Account(106);

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

        this.customers.put(customer.customer_id, customer);
        this.customers.put(customer2.customer_id, customer2);
        this.customers.put(customer3.customer_id, customer3);
        this.customers.put(customer4.customer_id, customer4);
        this.customers.put(customer5.customer_id, customer5);

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

        this.accounts.put(account.account_id, account);
        this.accounts.put(account2.account_id, account2);
        this.accounts.put(account3.account_id, account3);
        this.accounts.put(account4.account_id, account4);
        this.accounts.put(account5.account_id, account5);
	}
}
