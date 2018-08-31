package code;

import java.util.HashMap;

public class configuration 
{
    public HashMap<String, String> config_strings = new HashMap<String, String>();
    public HashMap<String, Boolean> config_bools = new HashMap<String, Boolean>();
    public HashMap<String, Integer> config_ints = new HashMap<String, Integer>();
    
    public configuration(HashMap<String, String> config_strings, HashMap<String, Boolean>  config_bools, 
            HashMap<String, Integer> config_ints)
    {
        this.config_strings = config_strings;
        this.config_bools = config_bools;
        this.config_ints = config_ints;
    }
    
    public configuration()
    {
    }
    
    public HashMap<String, String> get_configuration()
    {
        return this.config_strings;
    }

    public Boolean get_boolean(String key)
    {
        Boolean bool = null;
        if (config_bools.containsKey(key))
        {
             bool = config_bools.get(key);
        }
        return bool;
    }
    
    public void set_boolean(String key, Boolean bool)
    {
        this.config_bools.put(key, bool);
    }
    
}
