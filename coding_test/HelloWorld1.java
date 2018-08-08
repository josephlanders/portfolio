import java.util.HashMap;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Map;

/*
    Joseph Landers
    josephlanders@gmail.com
    0432 903 535
*/

public class HelloWorld{

    public HashMap<Integer, Employee> employees = null;
    public HashMap<Integer, ArrayList<Integer>> managers_employees = null;
    public Boolean verbose = false;


    public static void main(String []args){
        HelloWorld h = new HelloWorld();
    }

    public HelloWorld()
    {
        employees = new HashMap<Integer, Employee>();
        
        // Assume Database Structure along the lines of
        // Employee ID, Name, Manager ID
        
        // In the case of multiple managers would need different setup

        employees.put(100, new Employee("Alan", 100, 150));
        employees.put(220, new Employee("Martin", 220, 100));
        employees.put(150, new Employee("Jamie", 150, null));
        employees.put(275, new Employee("Alex", 275, 100));
        employees.put(400, new Employee("Steve", 400, 150));
        employees.put(190, new Employee("David", 190, 400));

        managers_employees = new HashMap<Integer, ArrayList<Integer>>();
        
        // In production we might have to find the root nodes and also build our Managers to Employees lists
        // Find root nodes by iterating over the list of employees and finding employees with no manager ID.
        //ArrayList<Integer> root_nodes = find_root_nodes(employees);

        // Create an arrayList of arrayLists - (manager id, [employee id1, employee id2, ...]), manager id2 ... etc
        // Can do this by iterating over the employees list and updating the nested arraylist
        //HashMap<Integer, ArrayList<Integer>> managers_employees = link_employees_to_managers(employees);

        // However for this coding test, manually define the hierarchy as below

        // Managers and their Employees
        managers_employees.put(150, new ArrayList(Arrays.asList(100, 400)));
        managers_employees.put(100, new ArrayList(Arrays.asList(220, 275)));
        managers_employees.put(400, new ArrayList(Arrays.asList(190)));
        managers_employees.put(null, new ArrayList(Arrays.asList(150)));

        // Find root nodes (Employees with No Managers)
        ArrayList<Integer> manager_IDs = managers_employees.get(null);

        if (manager_IDs.size() == 0)
        {
            //System.out.println("No root managers!");
            //exit(1);
        } else {
            //System.out.println("Some root managers! " + manager_IDs.size());
        }

        ArrayList<Employee> employee_list = new ArrayList<Employee>();

        for(Integer manager_ID : manager_IDs)
        {
            Integer sublevel = 0;
            ArrayList<Employee> workers = find_workers_create_list(manager_ID, sublevel);
            employee_list.addAll(workers);
        }

        //System.out.println("Size of worker list " + workers.size());
        
        System.out.println("Method 1 - Create a list via recursion and print via iteration");

        for(Employee worker : employee_list)
        {
            if (verbose == true)
            {
                System.out.println(worker.toString());
            }
        }
        
        System.out.println("");

        for(Employee worker : employee_list)
        {
            Integer sublevel = worker.sublevel;
            for(int i = 0; i < sublevel ; i++)
            {
                System.out.print("\t");
            }
            System.out.println(worker.name);
        }
    
    }

    public ArrayList<Employee> find_workers_create_list(Integer manager_ID, Integer sublevel)
    {
        ArrayList<Employee> worker_list = new ArrayList<Employee>();    
    
        ArrayList<Integer> manager_IDs = managers_employees.get(manager_ID);
    
        Employee current_employee = employees.get(manager_ID);
        
        Boolean error = false;
        
        // Avoid infinite recursion where a manager, is a manager of their employee or w/e! :P
        if (sublevel > 5)
        {
            error = true;
        }
        
        if (current_employee == null)
        {
            System.out.println("Employee object is null/ employee record doesn't exist for employee ID " + manager_ID);
            error = true;
        }
        
        if (error == false && current_employee.id == null)
        {
            System.out.println("Employee has no ID, not a valid employee " + current_employee.name);
            // Note: employees with null 
            error = true;
        }
        
        if (error == true)
        {
            return worker_list;
        }
        
        if (current_employee == null)
        {
            if (verbose == true)
            {
                System.out.println("Ignoring current employee because employee object doesn't exist: " + manager_ID + " ");
            }
        } else {
        current_employee.sublevel = sublevel;
    
        worker_list.add(current_employee);
    
        if (manager_IDs == null)
        {
            if (verbose == true)
            {
                //System.out.println("Sublevel " + sublevel + " No employees for this manager ID: " + manager_ID);
            }
        } else 
        {
            for(Integer sub_manager_ID : manager_IDs)
            {
                Integer new_sublevel = sublevel + 1;
                ArrayList<Employee> more_workers = find_workers_create_list (sub_manager_ID, new_sublevel);
                worker_list.addAll(more_workers);
            }
        }
        }
    
        //System.out.println("Sublevel " + sublevel + " Size of final output " + final_output.size() + " for empl" + manager_ID);

        return worker_list;
    }
}


class Employee {
    String name = "";
    Integer id = 0;
    Integer sublevel = 0;
    Integer managerID = 0;
    Boolean verbose = false;
    ArrayList<Integer> children = new ArrayList<Integer>();

    public Employee(String name, Integer id, Integer managerID)
    {
        this.name = name;
        this.id = id;
        this.managerID = managerID;
    }

    public void putChild(Integer childID)
    {
        children.add(childID);
    }

    public ArrayList<Integer> getChildren()
    {
        return this.children;
    }

    public String toString()
    {
        String s = "sublevel " + sublevel + "\t name " + name + "\t id " + id + "\t managerID " + managerID;
        return s;
    }
}
