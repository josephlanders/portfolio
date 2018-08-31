package code;

import code.configuration;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.RandomAccessFile;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

public class lexicon_file_manager {

    private configuration configuration = null;
    private boolean verbose = false;

    public lexicon_file_manager(String lexicon_filename, configuration configuration) {
        this.lexicon_filename = lexicon_filename;
        this.configuration = configuration;
        lexicon_array = new HashMap<String, lexicon_item>();
        verbose = configuration.get_boolean("verbose");

    }

    public String stoplist_filename = "";
    public HashMap<String, lexicon_item> lexicon_array = null;

    private String lexicon_filename = "map";
    private boolean immediate_lexicon_rewrites = false;
    private RandomAccessFile r = null;

    public boolean load_lexicon() {

        lexicon_array = read_lexicon_to_memory();
        return true;
    }

    public lexicon_item get_lexicon_item_from_lexicon(String search_term) {
        lexicon_item lexicon_item = null;

        if (lexicon_array.containsKey(search_term) == true) {
            lexicon_item = lexicon_array.get(search_term);
        }

        return lexicon_item;
    }

    public HashMap<String, lexicon_item> read_lexicon_to_memory() {
        HashMap<String, lexicon_item> lexicon_array = new HashMap<String, lexicon_item>();
        if (lexicon_filename != null) {
            FileReader f = null;
            try {
                f = new FileReader(lexicon_filename);
            } catch (FileNotFoundException fnfe) {
                System.out.println(fnfe.getMessage());
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
            BufferedReader b = new BufferedReader(f);

            String sCurrentLine;

            try {

                while ((sCurrentLine = b.readLine()) != null) {
                    String[] split_string = sCurrentLine.split(",");
                    String term = split_string[0];
                    //Integer.parse
                    int file_offset = Integer.parseInt(split_string[1]);
                    int disk_block_size = Integer.parseInt(split_string[2]);
                    int content_size = Integer.parseInt(split_string[3]);
                    lexicon_item lexicon_item = new lexicon_item(term, file_offset, disk_block_size, content_size);
                    //System.out.println("From string " + sCurrentLine);
                    //System.out.println("Created lex item with " + term + " " + file_offset + " " + disk_block_size + " " + content_size);
                    lexicon_array.put(term, lexicon_item);

                }
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }

            this.lexicon_array = lexicon_array;
        }

        return lexicon_array;
    }

    public lexicon_item get_lexicon_item(String term) {
        lexicon_item lexicon_item = null;

        if (lexicon_array.containsKey(term)) {
            lexicon_item = lexicon_array.get(term);
        }
        return lexicon_item;
    }

    public void update_lexicon_item(lexicon_item lexicon_item) {
        lexicon_array.put(lexicon_item.term, lexicon_item);
    }

    public void zero_lexicon_file() {
        try {
            RandomAccessFile r2 = new RandomAccessFile(lexicon_filename, "rw");
            r2.setLength(0);
            r2.close();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }

    public void initialise_lexicon_file() {
        try {
            RandomAccessFile r2 = new RandomAccessFile(lexicon_filename, "rw");
            r2.setLength(0);
            r2.close();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }

    public void add_lexicon_item(lexicon_item lexicon_item) {
        String term = lexicon_item.term;
        if (lexicon_array.containsKey(term) == false) {
            lexicon_array.put(term, lexicon_item);
            //System.out.println("added lexicon item");
        } else {
            //System.out.println("already exists in lexicon");
        }

        //Writes lexicon every time we update the lex very bad :P
        if (immediate_lexicon_rewrites
                == true) {
            write_lexicon();
        }

    }

    public HashMap<String, lexicon_item> get_lexicon() {
        return lexicon_array;
    }

    public void write_lexicon() {
        RandomAccessFile r = null;
        try {
            r = new RandomAccessFile(lexicon_filename, "rw");
        } catch (FileNotFoundException e) {
            System.out.println(e.getMessage());
        }

        String lex_text = "";
        int lex_size = lexicon_array.size();

        StringBuilder sb = new StringBuilder();
        int i = 0;
        Iterator<Map.Entry<String, lexicon_item>> it = lexicon_array.entrySet().iterator();
        String inner_lex_text = "";
        lexicon_item lexicon_item = null;
        while (it.hasNext()) {
            Map.Entry<String, lexicon_item> pair = it.next();
            lexicon_item = pair.getValue();
            //lex_text += create_lexicon_item_text(lexicon_item);
            inner_lex_text = create_lexicon_item_text(lexicon_item);
            sb.append(inner_lex_text);
            if (i % 1000 == 0) {
                if (verbose == true)
                {
                System.out.println("collating lexicon text for write: " + i + " of " + lex_size);
                }
            }

            i++;
        }
        
        lex_text = sb.toString();

        if (verbose == true)
        {
        System.out.println("writing lexicon text to disk");
        }
        try {
            //r.write
            r.writeBytes(lex_text);
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }

    public String create_lexicon_item_text(lexicon_item lexicon_item) {
        String text = "";

        text += lexicon_item.term + "," + lexicon_item.file_offset + ","
                + lexicon_item.disk_block_size + "," + lexicon_item.content_size + "\n";

        return text;
    }

    public RandomAccessFile open_lexicon_file() {

        try {
            RandomAccessFile r = new RandomAccessFile(lexicon_filename, "rw");
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
        this.r = r;
        return r;
    }

    public void close_lexicon_file() {
        //
    }

    public void close_lexicon_file_real() {
        if (this.r != null) {
            try {
                this.r.close();
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
            this.r = null;
        }
    }

}
