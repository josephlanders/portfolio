package anz;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Iterator;
import java.util.List;
import java.util.Set;

/**
 *
 * @author z
 */
public class Bank {

    private HashMap<Integer, Customer> customers = new HashMap<>();
    private HashMap<Integer, Account> accounts = new HashMap<>();

    public static void main(String[] args) {
        Bank code_thing = new Bank();
    }

    public Bank() {
        Object[] ret = this.read_data_in();

        ArrayList<ArrayList<Integer>> customers_that_share_the_same_accounts = new ArrayList<>();

        Iterator<Map.Entry<Integer, Customer>> it = customers.entrySet().iterator();

        while (it.hasNext()) {
            Map.Entry<Integer, Customer> pair = it.next();
            Integer key = pair.getKey();
            Customer customer = pair.getValue();

            ArrayList<Integer> cust_accounts_by_id = customer.get_accounts_as_list_by_id();

            HashSet<Integer> s = new HashSet<Integer>();
            Iterator<Integer> it2 = cust_accounts_by_id.iterator();

            while (it2.hasNext()) {
                Integer account_by_id = it2.next();

                Account account = this.accounts.get(account_by_id);
                ArrayList<Integer> customers_by_id = account.get_customers_as_list_by_id();

                HashSet<Integer> s2 = new HashSet<Integer>(customers_by_id);

                s.addAll(s2);
            }

            ArrayList<Integer> customers_sharing_accounts = new ArrayList<Integer>(s);

            customers_that_share_the_same_accounts.add(customers_sharing_accounts);
        }

        Iterator <ArrayList<Integer>> it3 = customers_that_share_the_same_accounts.iterator();

        while (it3.hasNext()) {
            ArrayList<Integer> list_of_customers = it3.next();

            System.out.print("\nCustomers: ");

            Iterator <Integer> it4 = list_of_customers.iterator();

            while (it4.hasNext()) {
                Integer customer_id = it4.next();
                System.out.print(customer_id + ",");
            }

        }
    }

    public Object[] read_data_in() {
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
        Customer customer = new Customer(1);
        Customer customer2 = new Customer(2);
        Customer customer3 = new Customer(3);
        Customer customer4 = new Customer(4);
        Customer customer5 = new Customer(5);
        Account account = new Account(101);
        Account account2 = new Account(102);
        Account account3 = new Account(103);
        Account account4 = new Account(104);
        Account account5 = new Account(105);
        Account account6 = new Account(106);

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

        customers.put(customer.customer_id, customer);
        customers.put(customer2.customer_id, customer2);
        customers.put(customer3.customer_id, customer3);
        customers.put(customer4.customer_id, customer4);
        customers.put(customer5.customer_id, customer5);

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

        accounts.put(account.account_id, account);
        accounts.put(account2.account_id, account2);
        accounts.put(account3.account_id, account3);
        accounts.put(account4.account_id, account4);
        accounts.put(account5.account_id, account5);

        Object[] ret = new Object[2];

        ret[0] = customers;
        ret[1] = accounts;

        return ret;
    }
}

class Customer {

    private HashMap<Integer, Account> accounts_obj = new HashMap<>();
    private HashMap<Integer, Integer> account_ids = new HashMap<>();
    public Integer customer_id = null;

    public Customer(Integer customer_id) {
        this.customer_id = customer_id;
    }

    public void add_account(Account account_obj) {
        Integer account_id = account_obj.account_id;
        this.accounts_obj.put(account_id, account_obj);        
        this.account_ids.put(account_id, account_id);
    }
    
    public ArrayList<Integer> get_accounts_as_list_by_id() {
        ArrayList<Integer> ids = new ArrayList<>(this.account_ids.values());
        return ids;
    }

    
    public HashMap<Integer,Integer> get_accounts_as_map_by_id() {
        return this.account_ids;
    }
    
}

class Account {

    private HashMap<Integer, Integer> customer_ids = new HashMap<>();
    public Integer account_id = null;

    public Account(Integer account_id) {
        this.account_id = account_id;
    }
    public void add_customer(Customer customer) {
        Integer customer_id = customer.customer_id;
        this.customer_ids.put(customer_id, customer_id);
    }

    public HashMap<Integer, Integer> get_customers_as_map_by_id() {
        return this.customer_ids;
    }   
    
    public ArrayList<Integer> get_customers_as_list_by_id() {
        ArrayList<Integer> ids = new ArrayList<>(this.customer_ids.values());
        return ids;
    }

}
