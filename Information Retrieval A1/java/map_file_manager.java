package code;

import code.configuration;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.RandomAccessFile;
import java.util.HashMap;
import java.io.*;
import java.util.*;

public class map_file_manager {

    private configuration configuration = null;
    private boolean verbose = false;

    public map_file_manager(String map_filename, configuration configuration) {
        this.map_filename = map_filename;
        this.configuration = configuration;
        map_array = new HashMap<Integer, map_item>();
        
        this.verbose = configuration.get_boolean("verbose");
    }

    public String stoplist_filename = "";
    public HashMap<Integer, map_item> map_array = null;

    private String map_filename = "map";
    private boolean immediate_map_rewrites = false;
    private RandomAccessFile r = null;

    public void load_map() {
        if (map_filename != null) {
            FileReader f = null;
            try {
                f = new FileReader(map_filename);
            } catch (FileNotFoundException fnfe) {
                System.out.println(fnfe.getMessage());
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
            BufferedReader b = new BufferedReader(f);

            String sCurrentLine;

            HashMap<Integer, map_item> map_array = new HashMap<Integer, map_item>();

            try {

                while ((sCurrentLine = b.readLine()) != null) {
                    String[] split_string = sCurrentLine.split(",");
                    String docid = split_string[0];
                    int id = Integer.parseInt(split_string[1]);
                    map_item map_item = new map_item(id, docid);
                    map_array.put(id, map_item);

                }
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }

            this.map_array = map_array;
        }
    }

    public map_item get_mapping(int ordinal_number) {
        map_item map_item = null;

        if (map_array.containsKey(ordinal_number)) {
            map_item = map_array.get(ordinal_number);
        }
        return map_item;
    }

    public void zero_map_file() {
        try {
            RandomAccessFile r2 = new RandomAccessFile(map_filename, "rw");
            r2.setLength(0);
            r2.close();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }

    }

    public void initialise_map_file() {
        try {
            RandomAccessFile r2 = new RandomAccessFile(map_filename, "rw");
            r2.setLength(0);
            r2.close();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }

    public void add_mappings(HashMap<Integer, doc_class> blob_doc_array) {

        Iterator<Map.Entry<Integer, doc_class>> it = blob_doc_array.entrySet().iterator();
        int key = 0;
        int id = 0;
        String docid = "";
        doc_class blob_doc = null;
        while (it.hasNext()) {
            Map.Entry<Integer, doc_class> pair = it.next();
            key = pair.getKey();
            blob_doc = pair.getValue();

            id = blob_doc.id;
            docid = blob_doc.docid;

            if (map_array.containsKey(id) == false) {
                map_item new_map_item = new map_item(id, docid);
                map_array.put(id, new_map_item);
                //echo "added map item";
            } else {
                //echo "already exists in map";
            }
        }

        //Writes lexicon every time we update the lex very bad :P
        if (immediate_map_rewrites == true) {
            write_map();
        }
    }

    public void write_map() {
        RandomAccessFile r = null;
        try {
            r = new RandomAccessFile(map_filename, "rw");
        } catch (FileNotFoundException e) {
            System.out.println(e.getMessage());
        }

        String map_text = "";
        int map_size = map_array.size();
        StringBuilder sb = new StringBuilder();
        Iterator<Map.Entry<Integer, map_item>> it = map_array.entrySet().iterator();
        
        String inner_map_text = "";
        int i = 0;
        while (it.hasNext()) {
            Map.Entry<Integer, map_item> pair = it.next();
            int key = pair.getKey();
            map_item map_item = pair.getValue();
            inner_map_text = create_map_item_text(map_item);
            sb.append(inner_map_text);
            i++;
            if (i % 200 == 0) {
                if (verbose == true)
                {
                   System.out.println("collating map text for write: " + i + " of " + map_size);
                }
            }
        }
        
        map_text = sb.toString();

        if (verbose == true)
        {
           System.out.println("writing map text to disk");
        }
        try {
            r.writeBytes(map_text);
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }

    public String create_map_item_text(map_item map_item) {
        String text = "";

        String docid = map_item.docid;
        int ordinal_number = map_item.id;
        String map_text = docid + "," + ordinal_number + "\n";

        return map_text;
    }

    public RandomAccessFile open_map_file() {
        RandomAccessFile r = null;
        try {

            r = new RandomAccessFile(map_filename, "rw");

        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
        this.r = r;
        return this.r;
    }

    public void close_map_file() {
        //
    }

    public void close_map_file_real() {
        if (this.r != null) {
            try {
                this.r.close();
            } catch (IOException e) {
                System.out.println(e.getMessage());
            }
            this.r = null;
        }
    }

}
