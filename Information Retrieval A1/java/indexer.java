package code;

import code.configuration;
import java.util.HashMap;
import java.util.*;
import java.io.*;
import java.text.NumberFormat;

public class indexer {

    // Our unique reference to documents in memory, used as an incrementer
    private int all_doc_number = 0;
    private boolean print_content_terms = false;
    private String stoplist_file = "";
    private boolean stoplist_file_specified = false;
    // an array of class term_statistics
    // the key to this array is the "term" name since this is unique 
    // and storable as the key
    //private HashMap inverted_list = null;
    private boolean compression = false;
    private boolean clean_immediately = false;
    private boolean clean_individual = false;
    private boolean parse_docs_individually = false;
    private boolean debug = false;
    private int file_blob_read_length = 10000000;
    private boolean use_buffering = false;
    private boolean use_variable_length_disk_blocks = true;
    public String lexicon_filename = "lexicon";
    public String map_filename = "map";
    public String invlists_filename = "invlists";
    public String stoplist_filename = "";
    public map_file_manager map_file_manager = null;
    public invlists_manager invlists_manager = null;
    public lexicon_file_manager lexicon_file_manager = null;
    public stoplist_file_manager stoplist_file_manager = null;
    public boolean measure_times = false;
    public boolean verbose = false;
    //public $padded_block_size = 3000;
    public int maximum_fixed_block_size = 1000;
    public int maximum_fixed_block_size_postings = 3000;
    public int maximum_fixed_block_size_inverted_list_length = 12000;
    public int block_cache_memory_buffer_size = 10000;
    public boolean write_memory_buffer_during_processing = true;
    public boolean use_memory_buffer_cache_eviction = false;
    private configuration configuration = new configuration();
    public int unset_time = 0;
    public int garbage_time = 0;
    public String collection_to_index = "testdoc.txt";
    public String integer_keyword = "L";
    public int integer_length = 4;
    public boolean eof = false;

    public indexer(configuration configuration) {
    }

    public indexer() {
    }

    public void initialise() {
        HashMap<String, Object> config_array = new HashMap<String, Object>();

        config_array.put("use_buffering", use_buffering);
        config_array.put("use_variable_length_disk_blocks", use_variable_length_disk_blocks);
        config_array.put("measure_times", measure_times);
        config_array.put("verbose", verbose);
        config_array.put("write_memory_buffer_during_processing", write_memory_buffer_during_processing);
        config_array.put("block_cache_memory_buffer_size", block_cache_memory_buffer_size);
        config_array.put("use_memory_buffer_cache_eviction", use_memory_buffer_cache_eviction);
        //configuration configuration = new configuration();
        //this.configuration = configuration;

        lexicon_file_manager = new lexicon_file_manager(lexicon_filename,
                configuration);
        map_file_manager = new map_file_manager(map_filename,
                configuration);
        invlists_manager = new invlists_manager(invlists_filename, lexicon_filename,
                lexicon_file_manager, configuration);
        stoplist_file_manager = new stoplist_file_manager(stoplist_filename,
                configuration);
        stoplist_file_manager.load_stoplist();
    }

    public void clear_files() {
        lexicon_file_manager.initialise_lexicon_file();
        map_file_manager.initialise_map_file();
        invlists_manager.initialise_invlists_file();
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

        if (flags.contains("-p")) {
            print_content_terms = true;
        }

        if (flags.contains("-s")) {
            stoplist_file_specified = true;
            stoplist_filename = options.get(0);
        }

        if (flags.contains("-x")) {
            verbose = true;
        }

        int last_element = options.size();
        if (last_element > 0) {
            collection_to_index = options.get(last_element - 1);
        }

        configuration.set_boolean("verbose", verbose);

        if (verbose == true) {
            if (stoplist_file_specified == true) {
                System.out.println("Stoplist filename: " + stoplist_filename);
            }
        }
    }

    public void start_processing() {
        parse_data_one_doc_at_a_time(collection_to_index);

        if (verbose == true) {
            System.out.println("writing lex");
        }
        lexicon_file_manager.write_lexicon();

        if (verbose == true) {
            System.out.println("writing map");
        }
        map_file_manager.write_map();
    }

    // The main program code to parse the data file and process it
    public void parse_data_one_doc_at_a_time(String collection_to_index) {
        RandomAccessFile r = null;
        try {
            r = new RandomAccessFile(collection_to_index, "r");
        } catch (FileNotFoundException e) {
            System.out.println(e.getMessage());
        }

        // Relative read position in the blob
        int blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        int file_start_pos = 0;

        long filesize = 0;
        try {
            filesize = r.length();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
        String blob = "";
        HashMap<Integer, doc_class> blob_doc_array = new HashMap<Integer, doc_class>();
        boolean stitch_blob = false;
        int length_to_read = file_blob_read_length;

        HashMap<String, term_statistics> inverted_list = new HashMap<String, term_statistics>();

        int i = 0;
        int docs_processed = 0;
        int docs_processed_last = 0;

        HashMap<String, term_statistics> small_inverted_list = null;
        while (eof == false) {

            if (verbose == true) {
                System.out.println("Memory usage before blob processed: " + get_memory_stats());
            }

            blob_start_pos = 0;
            blob_doc_array = new HashMap<Integer, doc_class>();

            HashMap<String, Object> ret = get_next_blob(r, filesize, file_start_pos, length_to_read, stitch_blob);
            blob = (String) ret.get("blob");
            length_to_read = (int) ret.get("length_to_read");

            HashMap<String, Object> ret2 = get_docs_from_blob(blob, blob_start_pos, blob_doc_array);
            blob_start_pos = (int) ret2.get("blob_end_pos");
            boolean found_doc = (boolean) ret2.get("found_doc");
            stitch_blob = (boolean) ret2.get("stitch_blob");
            blob_doc_array = (HashMap<Integer, doc_class>) ret2.get("blob_doc_array");

            docs_processed_last = docs_processed;
            docs_processed += blob_doc_array.size();

            file_start_pos = get_next_file_pointer(file_start_pos, blob_start_pos, stitch_blob);

            boolean success = increment_file_pointer(r, file_start_pos);

            if (found_doc == true) {
                if (verbose == true) {
                    System.out.println("Memory usage before blob processed: " + get_memory_stats());
                }
                blob = null;
                //unset($blob);
                blob_doc_array = extract_doc_text_from_doc_array(blob_doc_array);

                map_file_manager.add_mappings(blob_doc_array);

                int docs_processed_inner_loop = 0;
                Iterator<Map.Entry<Integer, doc_class>> it = blob_doc_array.entrySet().iterator();
                int key = 0;
                doc_class blob_doc = null;
                while (it.hasNext()) {
                    Map.Entry<Integer, doc_class> pair = it.next();
                    key = pair.getKey();
                    blob_doc = pair.getValue();

                    small_inverted_list = new HashMap<String, term_statistics>();

                    blob_doc = get_content_terms_as_tokens_one_doc(blob_doc);

                    HashMap<String, Object> ret3 = get_term_frequency_per_document_one_doc_array(small_inverted_list, blob_doc);

                    blob_doc = (doc_class) ret3.get("doc_class");

                    small_inverted_list = (HashMap<String, term_statistics>) ret3.get("inverted_list");

                    // Note we could remove this step 
                    // by including the iterating in
                    // get_term_frequency_per_document_one_doc_array 
                    // but for readability I prefer this
                    small_inverted_list = get_term_frequency_by_unique_document_count(small_inverted_list);

                    // Most likely this function isn't required - but don't want to modify the assignment at the last minute
                    inverted_list = merge_inverted_lists_array_with_array(inverted_list, small_inverted_list);

                    //small_inverted_list.clear();
                    //small_inverted_list = null;
                    docs_processed_inner_loop++;

                    if (verbose == true) {
                        if (docs_processed_inner_loop % 50 == 0) {
                            System.out.println("Proccessing from doc: " + docs_processed_last + " docs processed inner loop: " + docs_processed_inner_loop);
                            System.out.println("Memory usage after blob docs processed: " + get_memory_stats());
                            //System.out.println("Memory usage after blob docs processed: " +  memory_get_usage());
                            //echo "\ncycles collected: " . $this->gc_doc_collected . "\n";
                        }
                    }

                }
            }

            blob_doc_array.clear();
            blob_doc_array = null;

            if (verbose == true) {
                System.out.println("There are inverted list: " + inverted_list.size());
                System.out.println("Memory usage before blob processed: " + get_memory_stats());
            }
            //System.out.println("There are small inverted list: " + small_inverted_list.size());
        }

        write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(inverted_list);
    }

    public HashMap<String, term_statistics> merge_inverted_lists_array_with_array(HashMap<String, term_statistics> inverted_list, HashMap<String, term_statistics> small_inverted_list) {
        Iterator<Map.Entry<String, term_statistics>> it = small_inverted_list.entrySet().iterator();
        String small_key_term = "";
        term_statistics small_term_statistics = null;

        while (it.hasNext()) {
            Map.Entry<String, term_statistics> pair = it.next();
            small_key_term = pair.getKey();
            small_term_statistics = pair.getValue();

            if (inverted_list.containsKey(small_key_term) == false) {
                inverted_list.put(small_key_term, small_term_statistics);
            } else {
                term_statistics term_statistics = inverted_list.get(small_key_term);

                int old_occurances = term_statistics.number_of_unique_documents_occurs_in;
                HashMap<Integer, Integer> old_occurances_per_document_array = term_statistics.occurances_per_document_array;
                int small_occurances = small_term_statistics.number_of_unique_documents_occurs_in;
                HashMap<Integer, Integer> small_occurances_per_document_array = small_term_statistics.occurances_per_document_array;

                int occurances = old_occurances + small_occurances;
                HashMap<Integer, Integer> occurances_per_document_array = old_occurances_per_document_array;

                occurances_per_document_array.putAll(small_occurances_per_document_array);

                int id2 = 0;
                int count = 0;
                Iterator<Map.Entry<Integer, Integer>> it2 = small_occurances_per_document_array.entrySet().iterator();
                while (it2.hasNext()) {
                    Map.Entry<Integer, Integer> pair2 = it2.next();
                    id2 = pair2.getKey();
                    count = pair2.getValue();

                    occurances_per_document_array.put(id2, count);
                }

                term_statistics.number_of_unique_documents_occurs_in = occurances;
                term_statistics.occurances_per_document_array = occurances_per_document_array;
                inverted_list.put(small_key_term, term_statistics);
            }
        }

        return inverted_list;
    }

    public HashMap<String, term_statistics> get_term_frequency_by_unique_document_count(HashMap<String, term_statistics> inverted_list) {
        Iterator<Map.Entry<String, term_statistics>> it = inverted_list.entrySet().iterator();

        String key = "";
        term_statistics term_statistics = null;
        int number_of_unique_documents_occurs_in = 0;
        while (it.hasNext()) {
            Map.Entry<String, term_statistics> pair = it.next();
            key = pair.getKey();
            term_statistics = pair.getValue();

            number_of_unique_documents_occurs_in = term_statistics.occurances_per_document_array.size();
            term_statistics.number_of_unique_documents_occurs_in = number_of_unique_documents_occurs_in;
            inverted_list.put(key, term_statistics);
        }

        return inverted_list;
    }

    // Count term frequency per document and store in array
    // store in term_statistics class -> occurances_per_document array
    //   the key is the ordinal_number and the value is the number of occurances
    public HashMap<String, Object> get_term_frequency_per_document(HashMap<String, term_statistics> inverted_list, HashMap<Integer, doc_class> blob_doc_array) {

        HashMap<String, term_statistics> updated_inverted_list = inverted_list;

        Iterator<Map.Entry<Integer, doc_class>> it = blob_doc_array.entrySet().iterator();
        int key = 0;
        doc_class doc_class = null;
        while (it.hasNext()) {
            Map.Entry<Integer, doc_class> pair = it.next();
            key = pair.getKey();
            doc_class = pair.getValue();
            HashMap<String, Object> ret = get_term_frequency_per_document_one_doc_array(updated_inverted_list, doc_class);
            doc_class ret_doc = (doc_class) ret.get("doc_class");
            updated_inverted_list = (HashMap<String, term_statistics>) ret.get("inverted_list");
        }

        HashMap<String, Object> ret2 = new HashMap<String, Object>();
        ret2.put("blob_doc_array", blob_doc_array);
        ret2.put("inverted_list", updated_inverted_list);

        return ret2;
    }

    public HashMap<String, Object> get_term_frequency_per_document_one_doc_array(HashMap<String, term_statistics> inverted_list, doc_class doc_class) {

        ArrayList<String> cleaned_tokens = doc_class.cleaned_tokens;

        Iterator<String> it = cleaned_tokens.iterator();
        term_statistics term_statistics = null;
        String cleaned_token = "";
        while (it.hasNext()) {
            cleaned_token = it.next();

            // Could use the md5 of the cleaned token 
            // but since it's all in English etc, may as well do things the easy way
            if (inverted_list.containsKey(cleaned_token) == true) {
                term_statistics = inverted_list.get(cleaned_token);
            } else {
                term_statistics = new term_statistics(cleaned_token);
            }

            //number_of_unique_documents_occurs_in = term_statistics.number_of_unique_documents_occurs_in;
            HashMap<Integer, Integer> occurances_per_document_array = term_statistics.occurances_per_document_array;
            int id = doc_class.id;
            if (occurances_per_document_array.containsKey(id)) {
                int occurance = occurances_per_document_array.get(id);
                occurances_per_document_array.put(id, occurance + 1);
            } else {
                occurances_per_document_array.put(id, 1);
                //number_of_unique_documents_occurs_in += 1;
            }

            term_statistics.occurances_per_document_array = occurances_per_document_array;

            inverted_list.put(cleaned_token, term_statistics);

        }

        if (doc_class.cleaned_tokens instanceof ArrayList) {
            doc_class.cleaned_tokens.clear();
            doc_class.cleaned_tokens = null;
        }

        HashMap<String, Object> ret = new HashMap<String, Object>();
        ret.put("doc_class", doc_class);
        ret.put("inverted_list", inverted_list);

        return ret;
    }

    // From the extracted text stored in the doc_class
    //  Filter it and return as tokenised list
    public HashMap<Integer, doc_class> get_content_terms_as_tokens(HashMap<Integer, doc_class> blob_doc_array) {

        int i = 0;

        Iterator<Map.Entry<Integer, doc_class>> it = blob_doc_array.entrySet().iterator();
        int key = 0;
        doc_class doc_class = null;
        doc_class ret_doc = null;
        while (it.hasNext()) {
            Map.Entry<Integer, doc_class> pair = it.next();
            key = pair.getKey();
            doc_class = pair.getValue();
            ret_doc = get_content_terms_as_tokens_one_doc(doc_class);
            blob_doc_array.put(key, ret_doc);
        }

        return blob_doc_array;
    }

    public doc_class get_content_terms_as_tokens_one_doc(doc_class doc_class) {
        String doc_headline = doc_class.headline;
        String doc_text = doc_class.text;
        ArrayList<String> tokens = null;
        String doc_complete = doc_text + " " + doc_headline;

        String[] stringTokens = doc_complete.split("\\s");

        // Probably unnecessary extra step
        ArrayList<String> arrayListStringTokens = new ArrayList<String>();
        ArrayList<String> arrayListStringTokens2 = new ArrayList<String>();
        for (int i = 0; i < stringTokens.length; i++) {

            arrayListStringTokens.add(stringTokens[i]);
        }

        doc_class.text = null;
        ArrayList<String> cleaned_tokens = new ArrayList<String>();
        String token = "";
        String start_token = "";
        String end_token = "";
        boolean addToken = true;

        /*
        Iterator<String> it = cleaned_tokens.iterator();
        while (it.hasNext()) {
             token = it.next();             */
        for (int i = 0; i < arrayListStringTokens.size(); i++) {
            addToken = true;
            token = arrayListStringTokens.get(i);

            token = token.trim();

            if (token.compareTo("") == 0 || token.compareTo("\r") == 0 || token.compareTo("\n") == 0
                    || token.compareTo("\r\n") == 0 || token.length() == 0) {
                addToken = false;
            }

            start_token = token;

            token = token.replaceAll("[\n\r]", "");

            // Remove excess markup tags
            token = token.replaceAll("<[^>]*>", "");

            //Remove  punctuation (any symbols that are not letters or numbers)
            // We keep hyphens
            token = token.replaceAll("[^a-zA-Z0-9-]+", "");

            token = token.trim();

            // When words are joined by punctuation (){}, . ; but not -
            // split them and discard the original token as it has no meaning
            /*
            String[] sTokens = token.split("\\s");
            if (sTokens.length > 1) {

                for (int j = 0; j < sTokens.length; j++) {
                    sTokens[j] = sTokens[j].trim();
                    arrayListStringTokens2.add(sTokens[j]);
                }
                addToken = false;
            } */

            if (addToken == true) {
                // When words are hyphenated split them 
                // and keep the original as it has meaning
                String[] subTokens = token.split("-");
                if (subTokens.length > 1) {

                    for (int j = 0; j < subTokens.length; j++) {

                        arrayListStringTokens2.add(subTokens[j]);
                    }
                }
            }

            end_token = token;

            if (token.compareTo("") == 0 || (token.length() == 0)) {
                addToken = false;
            }

            //arrayListStringTokens.set(i, token);
            if (addToken == true) {
                arrayListStringTokens2.add(token);
            }

        }

        for (int i = 0; i < arrayListStringTokens2.size(); i++) {
            addToken = true;
            token = arrayListStringTokens2.get(i);

            token = token.replaceAll("-", " ");

            token = token.trim();

            if (token.compareTo("") == 0 || (token.length() == 0)) {
                addToken = false;
            }

            // IMHO it's better to fold the entire text to lower rather than individual calls.
            // in terms of performance but I suppose we might lose information doing this before normalising
            // Case fold to lower case
            token = token.toLowerCase();

            if (addToken == true) {
                arrayListStringTokens2.set(i, token);
            } else {
                // TODO: fix this by encapuslating the loop in an iterator for assignment 2               
                arrayListStringTokens2.set(i, "this is not a token");
                //arrayListStringTokens2.set(i, "");
                
                // Weird behaviour withe remove method, it's causing 
                // the next loop to evaluate this element as "" or probably null
                //arrayListStringTokens2.remove(i);
            }
            //arrayListStringTokens2.
            // Starts from 0 so safe to do this
            //arrayListStringTokens2.add(i, token);
        }

        Iterator<String> it2 = arrayListStringTokens2.iterator();
        while (it2.hasNext()) {
            boolean add_token = true;
            token = it2.next();
            if (token == null)
            {
                continue;
            }
            
            if (token.compareTo("this is not a token") == 0)
            {
                add_token = false;
            }
            
            if (token.compareTo("") == 0 || (token.length() == 0)) {
                add_token = false;
            }

            
            //echo "start token was: " . $token . " end token was " . $token . " length " . strlen($token) .  "\n";
            if (stoplist_file_specified == true) {
                // Exclude stopped words
                boolean excluded = stoplist_file_manager.in_stoplist(token);
                if (excluded == true) {
                    //System.out.println("Keyword " + token + " is excluded");
                    //echo "Keyword " . $token . " is excluded\n";
                    add_token = false;
                }
            }

            if (add_token == true) {
                cleaned_tokens.add(token);
            }
            // Early cleanup

        }
        doc_class.cleaned_tokens = cleaned_tokens;

        String docid = doc_class.docid;
        int id = doc_class.id;

        if (print_content_terms == true) {
            System.out.println("\nDOCID: " + docid + "\n");
            System.out.println("ID: " + id + "\n");
            System.out.println("Content terms: \n");
            String cleaned_token = "";
            for (int i = 0; i < cleaned_tokens.size(); i++) {
                cleaned_token = cleaned_tokens.get(i);
                System.out.println(cleaned_token);
                //System.out.println(cleaned_token + " " + cleaned_token.length());
            }
        }

        // Probably unnecessary extra step
        // Try to Free memory?
        arrayListStringTokens.clear();
        arrayListStringTokens = null;
        return doc_class;
    }

    // Parse the doc text and extract content into the doc_class object
    public HashMap<Integer, doc_class> extract_doc_text_from_doc_array(HashMap<Integer, doc_class> blob_doc_array) {
        Iterator<Map.Entry<Integer, doc_class>> it = blob_doc_array.entrySet().iterator();
        int key = 0;
        doc_class doc_class = null;
        while (it.hasNext()) {
            Map.Entry<Integer, doc_class> pair = it.next();
            key = pair.getKey();
            doc_class = pair.getValue();

            String docid_in_doc = get_data_in_tag_multiline(doc_class, "<DOCID>", "</DOCID>");
            docid_in_doc = docid_in_doc.trim();
            doc_class.docid = docid_in_doc;
            //echo "docid is " . $docid_in_doc;
            String headline_in_doc = get_data_in_tag_multiline(doc_class, "<HEADLINE>", "</HEADLINE>");
            headline_in_doc = headline_in_doc.trim();
            doc_class.headline = headline_in_doc;
            //echo "headline is " . $headline_in_doc;
            String text_in_doc = get_data_in_tag_multiline(doc_class, "<TEXT>", "</TEXT>");
            //text_in_doc = text_in_doc.trim();
            doc_class.text = text_in_doc;
            //echo "text is " . $text_in_doc;

            // Might be unnecessary
            blob_doc_array.put(key, doc_class);
        }
        //gc_collect_cycles();
        return blob_doc_array;
    }

    // Extract data from within a set of tags
    public String get_data_in_tag_multiline(doc_class doc_class, String start_tag, String end_tag) {
        boolean found_whole_tag_status = false;
        String text = "";
        boolean start_tag_found = false;
        boolean end_tag_found = false;
        boolean relative_start_pos = false;
        boolean relative_end_pos = false;
        int start_tag_start_position = 0;
        int end_tag_start_position = 0;
        int start_tag_end_position = 0;
        int end_tag_end_position = 0;

        String doc_raw_text = doc_class.raw_text;

        start_tag_start_position = doc_raw_text.indexOf(start_tag, 0);

        if (start_tag_start_position != -1) {
            start_tag_found = true;
        } else {
            //echo "No start tag\n";
        }

        if (start_tag_found == true) {
            start_tag_end_position = start_tag_start_position + start_tag.length();
        }

        end_tag_start_position = doc_raw_text.indexOf(end_tag, start_tag_start_position);

        // -1 not null as per PHP
        if (end_tag_start_position != -1) {
            end_tag_found = true;
        } else {
            //echo "No end tag\n";
        }

        end_tag_end_position = end_tag_start_position + end_tag.length();

        if (start_tag_found == true && end_tag_found == true) {
            // DID WE FIND <DOC> </DOC> not </DOC> <DOC>
            if (start_tag_end_position < end_tag_start_position) {
                found_whole_tag_status = true;
            }
        }

        if (found_whole_tag_status == true) {
            //echo "found a doc in find_doc\n";
            // Next read pointer should be after the closing tag <DOC>  CONTENT </DOC> 
            ///$blob_end_pos = $blob_start_pos + $end_tag_end_position;

            // Read between the <DOC> </DOC>
            int length = end_tag_start_position - start_tag_end_position;

            // Call is different to PHP
            text = doc_raw_text.substring(start_tag_end_position, end_tag_start_position);

            found_whole_tag_status = true;
        } else {
            //echo "Didn't find start and end tag\n";
        }

        /*
        HashMap<String, Object> ret = new HashMap<String, Object>();
        ret.put("doc_class", doc_class);
        ret.put("found_whole_doc_status", found_whole_doc_status);
        ret.put("blob_end_pos", blob_end_pos);
        ret.put("start_doc_found", start_doc_found);
        ret.put("end_doc_found", end_doc_found);

        return ret;
         */
        return text;
    }

    // Search for the <DOC> start and </DOC> end tag
    //// There are other ways to find the end of a DOC such as looking for the next
    //// <DOC> tag where no </DOC> is found but we ignore this for simplicities sake
    //// and assume our data file is consistent
    public HashMap<String, Object> get_docs_from_blob(String blob, int blob_start_pos, HashMap<Integer, doc_class> blob_doc_array) {

        blob_doc_array = new HashMap<Integer, doc_class>();
        int docs_found = 0;

        boolean stitch_blob = false;
        boolean found_doc = false;
        int blob_end_pos = 0;

        boolean found_whole_doc_status = false;

        // Find all the docs within our blob            
        do {
            // Get one document from file            

            HashMap<String, Object> ret = find_doc(blob, blob_start_pos, "<DOC>", "</DOC>");
            doc_class doc_class = (doc_class) ret.get("doc_class");
            found_whole_doc_status = (boolean) ret.get("found_whole_doc_status");
            blob_end_pos = (int) ret.get("blob_end_pos");
            boolean end_doc_found = (boolean) ret.get("end_doc_found");
            boolean start_doc_found = (boolean) ret.get("start_doc_found");
            if (doc_class != null) {
                int ordinal_number = doc_class.id;
                blob_doc_array.put(ordinal_number, doc_class);
                docs_found++;
                found_doc = true;
            }

            // Move blob pointer forward
            blob_start_pos = blob_end_pos;

            // No doc in blob?
            if ((found_whole_doc_status == false) && (docs_found == 0)) {
                //echo "no complete doc found in blob\n";
                // If blob is empty - we want to stitch with another blob
                stitch_blob = true;
                break;
            }

            // no more docs in blob
            if ((found_whole_doc_status == false) && (docs_found > 0)) {
                break;
            }

        } while (found_whole_doc_status == true);

        HashMap<String, Object> ret = new HashMap<String, Object>();

        ret.put("blob_end_pos", blob_end_pos);
        ret.put("found_doc", found_doc);
        ret.put("stitch_blob", stitch_blob);
        ret.put("blob_doc_array", blob_doc_array);
        return ret;
    }

    // Search for <DOC> tag start and end 
    public HashMap<String, Object> find_doc(String blob, int blob_start_pos, String start_tag, String end_tag) {
        int blob_end_pos = blob_start_pos;
        boolean found_whole_doc_status = false;
        String doc_text = "";
        boolean start_doc_found = false;
        boolean end_doc_found = false;
        boolean relative_start_pos = false;
        boolean relative_end_pos = false;
        doc_class doc_class = null;
        int start_tag_start_position = 0;
        int end_tag_start_position = 0;
        int start_tag_end_position = 0;
        int end_tag_end_position = 0;

        start_tag_start_position = blob.indexOf(start_tag, blob_start_pos);

        if (start_tag_start_position != -1) {
            start_doc_found = true;
        } else {
            //echo "No start tag\n";
        }

        if (start_doc_found == true) {
            start_tag_end_position = start_tag_start_position + start_tag.length();
        }

        end_tag_start_position = blob.indexOf(end_tag, start_tag_start_position);

        // -1 not null as per PHP
        if (end_tag_start_position != -1) {
            end_doc_found = true;
        } else {
            //echo "No end tag\n";
        }

        end_tag_end_position = end_tag_start_position + end_tag.length();

        if (start_doc_found == true && end_doc_found == true) {
            // DID WE FIND <DOC> </DOC> not </DOC> <DOC>
            if (start_tag_end_position < end_tag_start_position) {
                found_whole_doc_status = true;
            }
        }

        if (found_whole_doc_status == true) {
            //echo "found a doc in find_doc\n";
            // Next read pointer should be after the closing tag <DOC>  CONTENT </DOC> 
            ///$blob_end_pos = $blob_start_pos + $end_tag_end_position;
            blob_end_pos = end_tag_end_position;

            // Read between the <DOC> </DOC>
            int length = end_tag_start_position - start_tag_end_position;

            // Call is different to PHP
            //doc_text = blob.substring(start_tag_end_position, length);
            doc_text = blob.substring(start_tag_end_position, end_tag_start_position);

            int ordinal_number = this.all_doc_number;

            doc_class = new doc_class();
            doc_class.id = ordinal_number;
            doc_class.raw_text = doc_text;
            found_whole_doc_status = true;
            this.all_doc_number = this.all_doc_number + 1;
        } else {
            //echo "Didn't find start and end tag\n";
        }

        HashMap<String, Object> ret = new HashMap<String, Object>();
        ret.put("doc_class", doc_class);
        ret.put("found_whole_doc_status", found_whole_doc_status);
        ret.put("blob_end_pos", blob_end_pos);
        ret.put("start_doc_found", start_doc_found);
        ret.put("end_doc_found", end_doc_found);

        return ret;
    }

    public HashMap<String, Object> get_next_blob(RandomAccessFile r, long filesize, int file_start_pos, int length_to_read, boolean stitch_blob) {
        String new_blob = "";
        String blob = "";

        length_to_read = get_length_to_read(length_to_read, filesize, file_start_pos, stitch_blob);

        new_blob = read_blob(r, length_to_read);

        if (stitch_blob == true) {
            blob = new_blob;
        } else {
            // Reset blob read position as we have a new blob
            blob = new_blob;
        }

        HashMap<String, Object> ret = new HashMap<String, Object>();
        ret.put("blob", blob);
        ret.put("length_to_read", length_to_read);
        return ret;
    }

    public int get_length_to_read(int length_to_read, long filesize, int file_start_pos, boolean stitch_blob) {

        if (stitch_blob == true) {
            length_to_read = length_to_read + file_blob_read_length;
        } else {
            length_to_read = file_blob_read_length;
        }
        // Only read to end of file.
        if (file_start_pos + length_to_read >= filesize) {

            int size = (int) filesize;
            // Read Past EOF so that FEOF is triggered
            // Don't read past EOF - it's not PHP - reading past EOF may throw an exception
            //length_to_read = size - file_start_pos + 1;
            length_to_read = size - file_start_pos;
            eof = true;
            //System.out.println("found EOF " + filesize + " " + file_start_pos + " " + length_to_read);
        }

        return length_to_read;
    }

    public boolean increment_file_pointer(RandomAccessFile r, int new_start_position) {
        boolean seek_status = false;
        //#echo ftell($handle) . " is current pos \n";
        if (eof == false) {

            if (verbose == true) {
                //echo "Moving file seek position: " . $new_start_position . "\n";
            }

            try {
                r.seek(new_start_position);
                seek_status = true;
            } catch (IOException e) {
                seek_status = false;
                System.out.println(e.getMessage());
            }

            //echo "new_start_position" . $new_start_position;
        }

        return seek_status;
    }

    public String read_blob(RandomAccessFile r, int length_to_read) {
        String s = "";

        byte[] buffer = new byte[length_to_read];
        try {
            // Returns length or -1 if EOF
            long length = r.read(buffer);

            // We can't reach this condition as we prevent reads past EOF
            // which may return a different sort of error?
            if (length == -1) {
                eof = true;
                throw new Exception("End of file due to read past EOF!");
            }

            s = new String(buffer);
        } catch (EOFException eof) {
            this.eof = true;
            System.out.println(eof.getMessage());
        } catch (IOException f) {
            System.out.println(f.getMessage());
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }

        return s;
    }

    public int get_next_file_pointer(int file_start_pos, int blob_start_pos, boolean stitch_blob) {

        //echo "file position was " . $file_start_pos . "\n";
        if (stitch_blob == true) {
        } else {
            file_start_pos += blob_start_pos;
        }

        //echo "file position is now " . $file_start_pos . "\n";
        return file_start_pos;
    }

    public void write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(HashMap<String, term_statistics> inverted_list) {

        invlists_manager.open_invlists_file();

        int inverted_list_count = inverted_list.size();
        int inverted_list_processed = 0;

        Iterator<Map.Entry<String, term_statistics>> it = inverted_list.entrySet().iterator();
        String term = "";
        term_statistics term_statistics = null;
        while (it.hasNext()) {
            Map.Entry<String, term_statistics> pair = it.next();
            term = pair.getKey();
            term_statistics = pair.getValue();
            //System.out.println("Processing term " + term);

            if (term == "") {
                continue;
            }

            if (term_statistics == null) {
                // continue;
            }

            lexicon_item lexicon_item = lexicon_file_manager.get_lexicon_item(term);

            if (lexicon_item == null) {
                boolean padding = false;
                int padded_fixed_block_size = 0;

                HashMap<String, Object> ret = invlists_manager.create_invlist_block_in_memory_cache_simple_array(term, padding, padded_fixed_block_size);
                int created_block_size = (int) ret.get("size_of_block");
                int created_content_size = (int) ret.get("content_size");

                lexicon_item = new lexicon_item(term, null,
                        created_block_size,
                        created_content_size);
                lexicon_item.term = term;
                lexicon_item.file_offset = null;
                lexicon_item.disk_block_size = 0;
                lexicon_item.content_size = 0;
                lexicon_file_manager.add_lexicon_item(lexicon_item);
            } else {

            }

            int updated_content_size = invlists_manager.update_invlist_block_in_memory_cache_simple_array(lexicon_item, term_statistics);

            int updated_block_size = updated_content_size;

            lexicon_item.disk_block_size = updated_block_size;
            lexicon_item.content_size = updated_content_size;
            // Update FAT
            lexicon_file_manager.update_lexicon_item(lexicon_item);
            inverted_list_processed++;

            if (inverted_list_processed % 50 == 0) {
                if (verbose == true) {
                    System.out.println(" processed inverted list: " + inverted_list_processed + " of " + inverted_list_count);
                }
            }
        }
        write_buffer_simple_array();

        invlists_manager.close_invlists_file();

        if (measure_times == true) {
            /*
            echo "\nInverted List Array Processing times\n";
            echo "Create (on disk or in cache) time was " . $this -> create_time . " seconds\n";
            echo "Create (on disk or in cache) time was " . $this -> create_time . " seconds\n";
            echo "Update (on disk or in cache) time was " . $this -> update_time . " seconds\n";
            echo "Write (on disk or delayed until post-processing) time was " . $this -> write_time . " seconds\n";
            echo "Read Lexicon time was " . $this -> read_lexicon_time . " seconds\n";
            echo "Write Lexicon time was " . $this -> write_lexicon_time . " seconds\n";
            echo "Inverted list processing time was " . $this -> total_time . 
    

    " seconds\n";
             */
        }
    }

    public void write_buffer_simple_array() {
        HashMap<String, lexicon_item> lexicon_array = lexicon_file_manager.get_lexicon();
        invlists_manager.open_invlists_file();

        int current_offset = 4;
        if (verbose == true) {
            System.out.println("re-indexing buffer\n");
        }

        int lexicons_processed = 0;
        int lexicon_size = lexicon_array.size();
        if (verbose == true) {
            System.out.println("Lexicon size is " + lexicon_size);
        }

        int new_filesize = 0;
        // Correct lexicon/invlists offsets
        Iterator<Map.Entry<String, lexicon_item>> it = lexicon_array.entrySet().iterator();
        String term = "";
        lexicon_item lexicon_item = null;
        while (it.hasNext()) {
            Map.Entry<String, lexicon_item> pair = it.next();
            term = pair.getKey();
            lexicon_item = pair.getValue();

            lexicon_item.file_offset = current_offset;
            // Increment offset
            int disk_block_size = lexicon_item.disk_block_size;
            current_offset = current_offset + disk_block_size;
            lexicon_file_manager.update_lexicon_item(lexicon_item);

            new_filesize += lexicon_item.content_size;

            lexicons_processed++;
            if (lexicons_processed % 1000 == 0) {
                if (verbose == true) {
                    System.out.println("re-index lexicons processed " + lexicons_processed + " of " + lexicon_size);
                }
            }
        }

        //1296240    1296240
        if (verbose == true) {
            System.out.println("file size estimate: " + new_filesize);
        }
        byte[] data2 = new byte[new_filesize];

        int file_pointer = 0;
        int lex_total = 0;

        if (verbose == true) {
            System.out.println("collating inverted list data\n");
        }

        int blocks_processed = 0;
        lexicon_size = lexicon_array.size();
        Iterator<Map.Entry<String, lexicon_item>> it2 = lexicon_array.entrySet().iterator();
        //String term2 = "";
        //lexicon_item lexicon_item2 = null;
        while (it2.hasNext()) {
            Map.Entry<String, lexicon_item> pair = it2.next();
            term = pair.getKey();
            lexicon_item = pair.getValue();
            HashMap<String, data_block> data_blocks_array = invlists_manager.invlists_block_memory_cache.get_data_blocks_array();

            //System.out.println("\nWriting term: " + term);
            term = lexicon_item.term;
            if (data_blocks_array.containsKey(term) == false) {
                //throw new Exception(" No data block exists for term: " + term);
            } else {
                data_block data_block_class = data_blocks_array.get(term);

                //System.out.println(data_block_class.toString());
                //System.exit(0);
                byte[] dd = data_block_class.get_block_data();
                //int md5 = md5(dd);
                // WRITE TO DISK
                int size_to_write = lexicon_item.disk_block_size;

                int file_offset_of_block = lexicon_item.file_offset;

                //data2 = data2 + dd;                
                //byte[] datacopy = data2;
                // Copy data2 into datacopy
                //System.out.println(file_pointer);
                // Append dd to data2 - starting from the end of datacopy/old data2
                System.arraycopy(dd, 0, data2, file_pointer, dd.length);
                //System.out.println(file_pointer);
                file_pointer += dd.length;
                //System.out.println(file_pointer);
                //data2 = dd;

                // Insert a marker into the document file so we can see what's going on
                //byte b = (byte) Integer.parseInt("11111111", 2);
                //data2[data2.length - 2] = b;
                //System.out.println("term: " + term);
            }

            blocks_processed++;
            if (blocks_processed % 200 == 0) {
                if (verbose == true) {
                    System.out.println("collating inverted list data processed " + blocks_processed + " of " + lexicon_size);
                }
            }
        }

        if (verbose == true) {
            System.out.println("writing out inverted lists\n");
            System.out.println("Size to write " + data2.length);
            System.out.println("Writing to " + invlists_filename);
        }

        try {
            RandomAccessFile r = new RandomAccessFile(invlists_filename, "rw");
            r.seek(integer_length);
            r.write(data2);
            r.close();
        } catch (IOException e) {
            System.out.println(e.getMessage());
        }

        if (measure_times == true) {
            //System.out.println("\n\nWrite buffer time (flush cache to disk): " + write_buffer_time + "\n");   
        }
    }

    public void write_buffer_simple_array_old() {
        HashMap<String, lexicon_item> lexicon_array = lexicon_file_manager.get_lexicon();
        invlists_manager.open_invlists_file();

        int current_offset = 4;
        if (verbose == true) {
            System.out.println("re-indexing buffer\n");
        }

        int lexicons_processed = 0;
        int lexicon_size = lexicon_array.size();
        if (verbose == true) {
            System.out.println("Lexicon size is " + lexicon_size);
        }
        // Correct lexicon/invlists offsets
        Iterator<Map.Entry<String, lexicon_item>> it = lexicon_array.entrySet().iterator();
        while (it.hasNext()) {
            Map.Entry<String, lexicon_item> pair = it.next();
            String term = pair.getKey();
            lexicon_item lexicon_item = pair.getValue();

            lexicon_item.file_offset = current_offset;
            // Increment offset
            int disk_block_size = lexicon_item.disk_block_size;
            current_offset = current_offset + disk_block_size;
            lexicon_file_manager.update_lexicon_item(lexicon_item);

            lexicons_processed++;
            if (lexicons_processed % 50 == 0) {
                if (verbose == true) {
                    System.out.println("lexicons processed " + lexicons_processed + " of " + lexicon_size);
                }
            }
        }

        byte[] data2 = new byte[0];
        int lex_total = 0;

        if (verbose == true) {
            System.out.println("collating text from blocks\n");
        }

        int blocks_processed = 0;
        lexicon_size = lexicon_array.size();
        Iterator<Map.Entry<String, lexicon_item>> it2 = lexicon_array.entrySet().iterator();
        while (it2.hasNext()) {
            Map.Entry<String, lexicon_item> pair = it2.next();
            String term = pair.getKey();
            lexicon_item lexicon_item = pair.getValue();
            HashMap<String, data_block> data_blocks_array = invlists_manager.invlists_block_memory_cache.get_data_blocks_array();

            //System.out.println("\nWriting term: " + term);
            term = lexicon_item.term;
            if (data_blocks_array.containsKey(term) == false) {
                //throw new Exception(" No data block exists for term: " + term);
            } else {
                data_block data_block_class = data_blocks_array.get(term);

                //System.out.println(data_block_class.toString());
                //System.exit(0);
                byte[] dd = data_block_class.get_block_data();
                //int md5 = md5(dd);
                // WRITE TO DISK
                int size_to_write = lexicon_item.disk_block_size;

                int file_offset_of_block = lexicon_item.file_offset;

                //data2 = data2 + dd;                
                //byte[] datacopy = data2;
                // Copy data2 into datacopy
                byte[] datacopy = new byte[data2.length];
                System.arraycopy(data2, 0, datacopy, 0, data2.length);

                // Expand data2 size
                data2 = new byte[data2.length + dd.length];

                // Copy datacopy back into new data2
                System.arraycopy(datacopy, 0, data2, 0, datacopy.length);

                // Append dd to data2 - starting from the end of datacopy/old data2
                System.arraycopy(dd, 0, data2, datacopy.length, dd.length);
                //data2 = dd;

                // Insert a marker into the document file so we can see what's going on
                //byte b = (byte) Integer.parseInt("11111111", 2);
                //data2[data2.length - 2] = b;
                //System.out.println("term: " + term);
            }

            blocks_processed++;
            if (blocks_processed % 50 == 0) {
                if (verbose == true) {
                    System.out.println("blocks processed " + blocks_processed + " of " + lexicon_size + "\n");
                }
            }
        }

        if (verbose == true) {
            System.out.println("writing out blocks\n");
            System.out.println("Size to write " + data2.length);
            System.out.println("Writing to " + invlists_filename);
        }

        try {
            RandomAccessFile r = new RandomAccessFile(invlists_filename, "rw");
            r.seek(integer_length);
            r.write(data2);
            r.close();
        } catch (IOException e) {
            System.out.println(e.getMessage());
        }

        if (measure_times == true) {
            //System.out.println("\n\nWrite buffer time (flush cache to disk): " + write_buffer_time + "\n");   
        }
    }

    // The main program code to parse the data file and process it
    public void parse_data_one_doc_at_a_time_wrong(String collection_to_index) {
        RandomAccessFile r = null;
        try {
            r = new RandomAccessFile(collection_to_index, "r");
        } catch (FileNotFoundException e) {
            System.out.println(e.getMessage());
        }

        // Relative read position in the blob
        int blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        int file_start_pos = 0;

        long filesize = 0;
        try {
            filesize = r.length();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
        String blob = "";
        HashMap<Integer, doc_class> blob_doc_array = new HashMap<Integer, doc_class>();
        boolean stitch_blob = false;
        int length_to_read = file_blob_read_length;

        HashMap<String, term_statistics> inverted_list = new HashMap<String, term_statistics>();

        int i = 0;
        //HashMap<String, term_statistics>  small_inverted_list = null;
        while (eof == false) {
            //small_inverted_list = new HashMap<String, term_statistics>();           

            blob_start_pos = 0;
            blob_doc_array = new HashMap<Integer, doc_class>();

            HashMap<String, Object> ret = get_next_blob(r, filesize, file_start_pos, length_to_read, stitch_blob);
            blob = (String) ret.get("blob");
            length_to_read = (int) ret.get("length_to_read");

            HashMap<String, Object> ret2 = get_docs_from_blob(blob, blob_start_pos, blob_doc_array);
            blob_start_pos = (int) ret2.get("blob_end_pos");
            boolean found_doc = (boolean) ret2.get("found_doc");
            stitch_blob = (boolean) ret2.get("stitch_blob");
            blob_doc_array = (HashMap<Integer, doc_class>) ret2.get("blob_doc_array");

            file_start_pos = get_next_file_pointer(file_start_pos, blob_start_pos, stitch_blob);

            boolean success = increment_file_pointer(r, file_start_pos);

            if (found_doc == true) {
                blob = null;
                //unset($blob);
                blob_doc_array = extract_doc_text_from_doc_array(blob_doc_array);

                blob_doc_array = get_content_terms_as_tokens(blob_doc_array);
                map_file_manager.add_mappings(blob_doc_array);
                HashMap<String, Object> ret3 = get_term_frequency_per_document(inverted_list, blob_doc_array);

                inverted_list = (HashMap<String, term_statistics>) ret3.get("inverted_list");

                blob_doc_array = (HashMap<Integer, doc_class>) ret3.get("blob_doc_array");

                inverted_list = get_term_frequency_by_unique_document_count(inverted_list);

                //inverted_list = merge_inverted_lists_array_with_array(inverted_list, small_inverted_list);
            }

            if (verbose == true) {
                System.out.println("There are inverted list: " + inverted_list.size());
            }
            //System.out.println("There are small inverted list: " + small_inverted_list.size());
        }

        write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(inverted_list);
    }

    public long get_memory_stats() {
        Runtime runtime = Runtime.getRuntime();

        NumberFormat format = NumberFormat.getInstance();

        //StringBuilder sb = new StringBuilder();
        long maxMemory = runtime.maxMemory();
        long allocatedMemory = runtime.totalMemory();
        long freeMemory = runtime.freeMemory();

        //sb.append("free memory: " + format.format(freeMemory / 1024));
        //sb.append("allocated memory: " + format.format(allocatedMemory / 1024));
        //sb.append("max memory: " + format.format(maxMemory / 1024));
        //sb.append("total free memory: " + format.format((freeMemory + (maxMemory - allocatedMemory)) / 1024));
        //sb.append(format.fotmat)
        return allocatedMemory;
    }
}
