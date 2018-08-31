package code;

import code.configuration;
import java.io.*;
import java.util.*;
public class stoplist_file_manager {
    
    public String stoplist_filename = "";
    public HashMap<String, String> stoplist = null;
    
    public stoplist_file_manager(String stoplist_filename, configuration configuration)
    {
        this.stoplist_filename = stoplist_filename;
    }
    
    public void load_stoplist() {
        HashMap<String, String> stoplist = new HashMap<String, String>();
        
        if (stoplist_filename.compareTo("") != 0) {
            FileReader f = null;
            try 
            {
            f = new FileReader(stoplist_filename);
            } catch (FileNotFoundException fnfe)
            {
                System.out.println(fnfe.getMessage());
                System.out.println("Stoplist filename not found: " + stoplist_filename);
                System.exit(0);
                
            } catch (Exception e)
            {
                System.out.println(e.getMessage());
            }
            if (f != null)
            {
            BufferedReader b = new BufferedReader(f);            
            
            String sCurrentLine;
            
            
            
            try {
                
            while ((sCurrentLine = b.readLine()) != null)
            {
                stoplist.put(sCurrentLine, sCurrentLine);               

            }
            } catch (Exception e)
                
            {
                
            }
            }
            this.stoplist = stoplist;                        
        }
    }

    public boolean in_stoplist(String term) {
        boolean status = false;
        String element = stoplist.get(term);
        if(element != null)
        {
            status = true;
        }
        return status;
    }
}
