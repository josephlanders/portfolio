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

        System.out.println("Method 2 - Create a tree via recursion, storing child nodes in employee.childEmp and recursively call to print");

        Integer sublevel = 0;
        for(Integer manager_ID : manager_IDs)
        {
            Employee current_employee = employees.get(manager_ID);
            find_workers_create_tree(current_employee, sublevel);
        }
        
        System.out.println();
        
        sublevel = 0;
        for(Integer manager_ID : manager_IDs)
        {
            Employee current_employee = employees.get(manager_ID);
            
            /*
            if (current_employee == null)
            {
                System.out.println("Can't print - Employee doesn't exist in employee HashMap " + manager_ID);
                continue;
            } */
            
            //System.out.println(current_employee.toString());
            System.out.println(current_employee.name);
            recursive_print(current_employee, sublevel);
        }


    }

    public void find_workers_create_tree(Employee current_employee, Integer sublevel)
    {
        Boolean error = false;
        
        if (sublevel > 5)
        {
            System.out.println("find_servants2: Too many sublevels");
            error = true;
        }
        
        if (current_employee == null)
        {
            System.out.println("find_servants2: Current employee is null");
            error = true;
        } 
        
        Integer manager_ID = null;
        
        if (error == false)
        {
        manager_ID = current_employee.id;
        
        if (manager_ID == null)
        {
           System.out.println("find_servants2: Employee has no employee.id!" + current_employee.toString());
           error = true;
        }
        }
        
        // Could re-factor this but it will make the code below too nested.
        if (error == true)
        {
            return;
        }
    
        ArrayList<Integer> manager_IDs = managers_employees.get(manager_ID);
    
        current_employee.sublevel = sublevel;
    
        if (manager_IDs == null)
        {
            if (verbose == true)
            {
                System.out.println("Manager " + manager_ID + " has no employees linked ");
            }
        } else 
        {
            Integer new_sublevel = sublevel + 1;
            for(Integer sub_manager_ID : manager_IDs)
            {
                
                Employee managers_worker = employees.get(sub_manager_ID);
                if (managers_worker == null)
                {
                    System.out.println("Can't find employee with ID " + sub_manager_ID + " linked to manager " + current_employee.id + " " + current_employee.name);
                    continue;
                }
                
                find_workers_create_tree (managers_worker, new_sublevel);
                if (verbose == true)
                {
                    System.out.println("Adding employee " + managers_worker.name + " " + managers_worker.id + " as a worker of manager " + current_employee.name + " " + current_employee.id);
                }
                current_employee.putChildEmp(managers_worker);
            }
            
            
        }
    }
    
    public void recursive_print(Employee current_employee, Integer sublevel)
    {
        if (sublevel > 5)
        {
            return;
        }
        
        ArrayList<Employee> children = current_employee.childEmp;
        
        sublevel = sublevel + 1;
        
        if (verbose == true)
        {
            System.out.println("There are " + children.size() + " children of " + current_employee.name);
        }
        for(Employee empl : children)
        {
            Integer emp_sublevel = empl.sublevel;
            for(int i = 0; i < emp_sublevel ; i++)
            {
                System.out.print("\t");
            }
            System.out.println(empl.name);
            recursive_print(empl, sublevel);
        }
    }
        
        
}


class Employee {
    String name = "";
    Integer id = 0;
    Integer sublevel = 0;
    Integer managerID = 0;
    Boolean verbose = false;

    ArrayList<Employee> childEmp = new ArrayList<Employee>();

    public Employee(String name, Integer id, Integer managerID)
    {
        this.name = name;
        this.id = id;
        this.managerID = managerID;
    }

    public void putChildEmp(Employee child)
    {
        Boolean found = false;
        Integer empID = child.id;
        for(int i = 0; i < childEmp.size(); i++)
        {
            Employee current_child = childEmp.get(i);
            Integer current_child_empID = current_child.id;
            if (empID == current_child_empID)
            {
               found = true;
               if (verbose == true)
               {
                   System.out.println("Not adding - Child " + child.name + " " + child.id + " already exists in current_child " + current_child.name + " " +  current_child.id);
               }
               break;
            }
        }
        
        if (found == false)
        {
           childEmp.add(child);
        }
    }

    public String toString()
    {
        String s = "sublevel " + sublevel + "\t name " + name + "\t id " + id + "\t managerID " + managerID;
        return s;
    }
}
