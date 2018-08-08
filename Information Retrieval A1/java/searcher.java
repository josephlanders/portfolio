package code;

import java.util.*;
import java.io.*;
import java.nio.ByteBuffer;

public class searcher {

    // an array of class term_statistics
    // the key to this array is the "term" name since this is unique 
    // and storable as the key
    public String lexicon_filename = "";
    public String map_filename = "";
    public String invlists_filename = "";
    private HashMap<String, term_statistics> inverted_list = null;
    private boolean compression = false;
    private boolean measure_time = false;
    public ArrayList<String> query_terms_array = null;
    public lexicon_file_manager lexicon_file_manager = null;
    public invlists_manager invlists_manager = null;
    public map_file_manager map_file_manager = null;
    public boolean verbose = false;
    public configuration configuration = new configuration();
    
    public searcher(configuration configuration)
    {
        
    }

    public searcher() {
    }

    public void parse_arguments(String args[]) {

        ArrayList<String> options = new ArrayList<String>();
        ArrayList<String> flags = new ArrayList<String>();

        for (int i = 0; i < args.length; i++) {
            if (args[i].charAt(0) == '-') {

                flags.add(args[i]);
            } else {

                options.add(args[i]);
            }
        }

        if (flags.contains("-x")) {
            verbose = true;
        }

        if (flags.contains("-t")) {
            measure_time = true;
        }

        lexicon_filename = options.get(0);

        invlists_filename = options.get(1);

        map_filename = options.get(2);

        query_terms_array = new ArrayList<String>();

        int last_element = options.size();

        for (int i = 3; i < last_element; i++) {
            query_terms_array.add(options.get(i));
        }
        
        configuration.set_boolean("verbose", verbose);

    }

    public void initialise() {
        HashMap<String, Object> config_array = new HashMap<String, Object>();

        config_array.put("measure_time", measure_time);

        //configuration = new configuration();    

        lexicon_file_manager = new lexicon_file_manager(lexicon_filename,
                configuration);
        lexicon_file_manager.load_lexicon();

        map_file_manager = new map_file_manager(map_filename, configuration);

        map_file_manager.load_map();

        invlists_manager = new invlists_manager(invlists_filename,
                lexicon_filename,
                lexicon_file_manager,
                configuration);

    }

    public void start_processing() {
        for (int i = 0; i < query_terms_array.size(); i++) {
            String query_term = query_terms_array.get(i);
            term_statistics term_statistics = search(query_term);

            if (term_statistics != null) {
                //System.out.println(term_statistics.toString());

                System.out.println(query_term);
                System.out.println(term_statistics.number_of_unique_documents_occurs_in);

                HashMap<Integer, Integer> occurances_per_document_array = term_statistics.occurances_per_document_array;

                Iterator<Map.Entry<Integer, Integer>> it = occurances_per_document_array.entrySet().iterator();
                while (it.hasNext()) {
                    Map.Entry<Integer, Integer> pair = it.next();
                    int id = pair.getKey();
                    int occurances_per_document = pair.getValue();

                    map_item doc = map_file_manager.get_mapping(id);

                    if (doc != null) {
                        //$this->utility->all_doc_array["$ordinal_number"];
                        System.out.println(doc.docid + " " + occurances_per_document);
                    } else {
                        System.out.println("error retrieving doc from map");
                    }

                }

            } else {
                System.out.println("Search term not found: " + query_term);

            }
        }

    }

    public term_statistics search(String query_term) {
        term_statistics term_statistics = null;
        
        query_term = query_term.replaceAll("-", " ");

        lexicon_file_manager.read_lexicon_to_memory();
        lexicon_item lexicon_item = lexicon_file_manager.get_lexicon_item_from_lexicon(query_term);
        if (lexicon_item != null) {
            term_statistics = invlists_manager.get_inverted_index_from_disk(lexicon_item);

            if (verbose == true) {
                System.out.println(lexicon_item.toString());
            }

            if (verbose == true) {
            }

        } else {
            System.out.println("not in lexicon: " + query_term);
        }

        return term_statistics;
    }

}
