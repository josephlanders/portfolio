#include "Indexer.h"
#include "Doc_class.h"
#include "Term_statistics.h"
#include "Invlists_manager.h"
#include "Invlists_file_manager.h"
#include "Map_file_manager.h"
#include "Lexicon_file_manager.h"
#include "Stoplist_file_manager.h"
#include "Configuration.h"
#include "Map_item.h"
//#include <cstdlib>
//#include <cstdio>
//#include <cstring>
#include <cstdlib>
#include <iostream>
#include <string>
#include <vector>
#include <unordered_map>
#include <map>
#include <list>
#include <regex>
#include <iterator>
#include <sys/stat.h>
#include <cstring>  // strlen
using namespace std;
    /*
    // Our unique reference to documents in memory, used as an incrementer
    long all_doc_number = 0;
    bool print_content_terms = false;
    string stoplist_filename = "";
    bool stoplist_file_specified = false;
    //std::vector<Term_statistics> inverted_list;
    bool clean_immediately = false;
    bool clean_individual = false;
    bool parse_docs_individually = false;
    bool debug = false;
    long file_blob_read_length = 10000000;
    bool use_buffering = false;
    bool use_variable_length_disk_blocks = true;
    string lexicon_filename = "lexicon";
    string map_filename = "map";
    string invlists_filename = "invlists";
    Map_file_manager map_file_manager;
    Invlists_manager invlists_manager;
    Lexicon_file_manager lexicon_file_manager;
    Stoplist_file_manager stoplist_file_manager;
    bool measure_times = false;
    bool verbose = false;
    long block_cache_memory_buffer_size = 10000;
    bool write_memory_buffer_during_processing = true;
    bool use_memory_buffer_cache_eviction = false;
    Configuration configuration;
    string collection_to_index = "";
    int integer_length = 4;
    bool eof = false;
*/

    long file_blob_read_length = 10000000;

    Indexer::Indexer() {
    }

    Indexer::Indexer(const Indexer& orig) {
    }

    Indexer::~Indexer() {
    }

    map<string, list<string>> Indexer::arguments(int argc, char** argv) {
        list<string> options;
        list<string> flags;
        map<string, string> flags_map;
        list<string> arguments;
        map<string, string>::iterator it;
                
        for (int i = 0; i < argc; i++) {            
            string argument(argv[i]);

            // Flag
            if (argument.substr(0, 1) == "-") {
                flags.push_back(argument);
                flags_map[argument] = argument;
                continue;
            }

            // Otherwise argument
            arguments.push_back(argument);
        }
        
        it = flags_map.find("-p");
        if (it != flags_map.end()) {
            //String element = it -> second;
            print_content_terms = true;
        }

        it = flags_map.find("-s");
        if (it != flags_map.end()) {
            //String element = it -> second;
            stoplist_file_specified = true;
            stoplist_filename = options.front();
        }

        it = flags_map.find("-x");
        if (it != flags_map.end()) {
            verbose = true;
        }

        //Configuration.set_boolean("verbose", verbose);

        int last_element = arguments.size();
        if (last_element > 0) {
            collection_to_index = arguments.back();
            //collection_to_index = options.get(last_element - 1);
        }

        if (verbose == true) {
            if (stoplist_file_specified == true) {
                cout << "Stoplist filename: " << stoplist_filename << endl;
            }
        }

        map<string, list < string>> ret;
        ret["options"] = options;
        ret["flags"] = flags;
        ret["arguments"] = arguments;

        return ret;
    }

    void Indexer::parse_arguments(int argc, char** argv) {
        map<string, list < string>> ret = this -> arguments(argc, argv);

        list<string> options = ret["options"];
        
    }

    void Indexer::initialise() {
        Lexicon_file_manager lexicon_file_manager(lexicon_filename,configuration);
        Map_file_manager map_file_manager(map_filename,configuration);
        Invlists_manager invlists_manager(invlists_filename, lexicon_filename,lexicon_file_manager, configuration);
        Stoplist_file_manager stoplist_file_manager(stoplist_filename,configuration);
        stoplist_file_manager.load_stoplist();
    }

    void Indexer::start_processing() {
        if (verbose == true) {
            cout << "Parsing document " << endl;
        }
        parse_data_one_doc_at_a_time(collection_to_index);

        if (verbose == true) {
            cout << "writing lex" << endl;
        }
        lexicon_file_manager.write_lexicon();

        if (verbose == true) {
            cout << "writing map" << endl;
        }
        map_file_manager.write_map();

    }

    // The main program code to parse the data file and process it

    void Indexer::parse_data_one_doc_at_a_time(string collection_to_index) {        
        long filesize = 0;
        
        struct stat st;
        stat(collection_to_index.c_str(), &st);
        filesize = st.st_size;
                
        FILE *fp;
        fp = fopen(collection_to_index.c_str(), "r");
        
        if (fp == NULL)
        {
            cout << "Failed to open file: " << collection_to_index.c_str() << endl;
        } else {
            //cout << "Successfully opened file: " << collection_to_index.c_str() << endl;
        }

        // Relative read position in the blob
        int blob_start_pos = 0;

        // Relative read position in file (start of next blob to be loaded)
        int file_start_pos = 0;
        
        string blob = "";
        map<int, Doc_class> blob_doc_array = map<int, Doc_class>();
        bool stitch_blob = false;
        int length_to_read = file_blob_read_length;

        map<string, Term_statistics> inverted_list = map<string, Term_statistics>();

        int docs_processed = 0;
        int docs_processed_last = 0;

        map<string, Term_statistics> small_inverted_list = map<string, Term_statistics>(); // = null;
        while (eof == false) {

            if (verbose == true) {
                cout << "Memory usage before blob processed: " << get_memory_stats() << endl;
            }

            blob_start_pos = 0;
            map<int, Doc_class> blob_doc_array;
            
            Ret_next_blob ret = get_next_blob(fp, filesize, file_start_pos, length_to_read, stitch_blob);            
            blob = ret.blob;
            length_to_read = ret.length_to_read;                        

            Ret_docs_from_blob ret2 = get_docs_from_blob(blob, blob_start_pos, blob_doc_array);
            blob_start_pos = ret2.blob_end_pos;
            bool found_doc = ret2.found_doc;
            stitch_blob = ret2.stitch_doc;            
            blob_doc_array = ret2.blob_doc_array;
            
            docs_processed_last = docs_processed;
            docs_processed += blob_doc_array.size();

            file_start_pos = get_next_file_pointer(file_start_pos, blob_start_pos, stitch_blob);

            bool success = increment_file_pointer(fp, file_start_pos);

            if (found_doc == true) {
                if (verbose == true) {
                    cout << "Memory usage before blob processed: " << get_memory_stats() << endl;
                }
                blob = "";
                                
                blob_doc_array = extract_doc_text_from_doc_array(blob_doc_array);

                map_file_manager.add_mappings(blob_doc_array);
                                
                int docs_processed_inner_loop = 0;
                                
                map<int, Doc_class>::iterator it_blob;
                it_blob = blob_doc_array.begin();    
                
                while (it_blob != blob_doc_array.end())
                {
                    int key = it_blob -> first;
                    
                    Doc_class blob_doc(it_blob -> second);

                    small_inverted_list;// = new map<string, Term_statistics>();

                    blob_doc = get_content_terms_as_tokens_one_doc(blob_doc);

                    Ret_term_frequency_per_document_one_doc ret3 = get_term_frequency_per_document_one_doc_array(inverted_list, blob_doc);
                    blob_doc = ret3.doc_class;
                    inverted_list = ret3.inverted_list;

                    // Note we could remove this step 
                    // by including the iterating in
                    // get_term_frequency_per_document_one_doc_array 
                    // but for readability I prefer this
                    inverted_list = get_term_frequency_by_unique_document_count(inverted_list);
                    // Most likely this function isn't required - but don't want to modify the assignment at the last minute
                    //inverted_list = merge_inverted_lists_array_with_array(inverted_list, small_inverted_list);

                    docs_processed_inner_loop++;

                    if (verbose == true) {
                        if (docs_processed_inner_loop % 50 == 0) {
                            cout << "Proccessing from doc: " << docs_processed_last << " docs processed inner loop: " << docs_processed_inner_loop << endl;
                            cout << "Memory usage after blob docs processed: " << get_memory_stats() << endl;
                        }
                    }

                    it_blob++;
                }
            }

            blob_doc_array.clear();

            if (verbose == true) {
                cout << "There are inverted list: " << inverted_list.size() << endl;
                cout << "Memory usage before blob processed: " << get_memory_stats() << endl;
            }
        }

        write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(inverted_list);
    }

    map<string, Term_statistics> Indexer::get_term_frequency_by_unique_document_count(map<string, Term_statistics> inverted_list) {
        map<string, Term_statistics>::iterator it;

        string key = "";
        Term_statistics term_statistics;// = NULL;
        int number_of_unique_documents_occurs_in = 0;
        it = inverted_list.begin();
        while (it != inverted_list.end())
        {
            key = it -> first;
            term_statistics = it -> second;
            number_of_unique_documents_occurs_in = term_statistics.occurances_per_document_array.size();
            term_statistics.number_of_unique_documents_occurs_in = number_of_unique_documents_occurs_in;
            inverted_list[key] = term_statistics;
            it++;
        }

        return inverted_list;
    }

    
    Indexer::Ret_term_frequency_per_document Indexer::get_term_frequency_per_document(map<string, Term_statistics> inverted_list, map<int, Doc_class> blob_doc_array) {

        map<string, Term_statistics> updated_inverted_list = inverted_list;

        map<int, Doc_class>::iterator it;

        int key = 0;
        Doc_class doc_class;// = NULL;
        it = blob_doc_array.begin();
        while (it != blob_doc_array.end()) {
            key = it -> first;
            doc_class = it -> second;
            Ret_term_frequency_per_document_one_doc ret = get_term_frequency_per_document_one_doc_array(updated_inverted_list, doc_class);
            Doc_class ret_doc = ret.doc_class;
            updated_inverted_list = ret.inverted_list;

            it++;
        }

        Ret_term_frequency_per_document ret2;
        ret2.blob_doc_array = blob_doc_array;
        ret2.inverted_list = updated_inverted_list;
        
        return ret2;
    }
   
    Indexer::Ret_term_frequency_per_document_one_doc Indexer::get_term_frequency_per_document_one_doc_array(map<string, Term_statistics> inverted_list, Doc_class doc_class) {

        vector<string> cleaned_tokens = doc_class.cleaned_tokens;

        vector<string>::iterator it;
        it = cleaned_tokens.begin();
        Term_statistics term_statistics;// = NULL;
        string cleaned_token = "";
        while (it != cleaned_tokens.end()) {
            cleaned_token = *it;
            //cleaned_token = it.next();
            
            // Could use the md5 of the cleaned token 
            // but since it's all in English etc, may as well do things the easy way
            map<string, Term_statistics>::iterator inv_it;
            inv_it = inverted_list.find(cleaned_token);
            if (inv_it != inverted_list.end()) {
                //cout << "A" << endl;
                term_statistics = inv_it -> second;
            } else {
                //cout << "B" << endl;
                // if we can't find it, we instantiate a new term_statistics class
                //Term_statistics term_statistics(cleaned_token);
                
               // Term_statistics term_statistics = Term_statistics(cleaned_token);
                term_statistics = Term_statistics(cleaned_token);
            }
            
            map<int, int> occurances_per_document_array = term_statistics.occurances_per_document_array;
            

            
            int id = doc_class.id;

            map<int, int>::iterator occ_it;
            occ_it = occurances_per_document_array.find(id);
            if (occ_it != occurances_per_document_array.end()) {
                int occurance_count = occ_it -> second;
                occurances_per_document_array[id] = occurance_count + 1;
            } else {
                occurances_per_document_array[id] = 1;
            }
            
            term_statistics.occurances_per_document_array = occurances_per_document_array;

            // Check if exists or insert
            if (inv_it != inverted_list.end()) {
                inverted_list[cleaned_token] = term_statistics;
            } else {
                inverted_list[cleaned_token] = term_statistics;
            }
            it++;

        };

            doc_class.cleaned_tokens.clear();
        
        Ret_term_frequency_per_document_one_doc ret;
        ret.doc_class = doc_class;
        ret.inverted_list = inverted_list;

        return ret;
    }
    


    // From the extracted text stored in the Doc_class
    //  Filter it and return as tokenised list

    map<int, Doc_class> Indexer::get_content_terms_as_tokens(map<int, Doc_class> blob_doc_array) {

        int i = 0;

        int key = 0;
        Doc_class doc_class;// = NULL;
        Doc_class ret_doc;// = NULL;
        map<int, Doc_class>::iterator it;
        it = blob_doc_array.begin();
        while (it != blob_doc_array.end()) {
            key = it -> first;
            doc_class = it -> second;
            ret_doc = get_content_terms_as_tokens_one_doc(doc_class);
            blob_doc_array[key] = ret_doc;
            it++;
        };

        return blob_doc_array;
    }

    Doc_class Indexer::get_content_terms_as_tokens_one_doc(Doc_class doc_class) {
        string doc_headline = doc_class.headline;
        string doc_text = doc_class.text;
        vector<string> tokens;// = NULL;
        string doc_complete = doc_text + " " + doc_headline;

        //string[] stringTokens = doc_complete.split("\\s");
        vector<string> stringTokens = this -> split(doc_complete, "\\s");
        

        // Probably unnecessary extra step
        vector<string> arrayListStringTokens; // = new ArrayList<String>();
        vector<string> arrayListStringTokens2; // = new ArrayList<String>();
        
        for (int i = 0; i < stringTokens.size(); i++) {

            arrayListStringTokens.push_back(stringTokens[i]);
        }
        
        doc_class.text = ""; // = NULL;
        vector<string> cleaned_tokens;// = new vector<string>();
        string token = "";
        string start_token = "";
        string end_token = "";
        bool addToken = true;

        for (int i = 0; i < arrayListStringTokens.size(); i++) {
            addToken = true;
            token = arrayListStringTokens[i];

            token = this -> trim(token);


            if (token == "" || token == "\r" || token == "\n"
                    || token == "\r\n" || token.length() == 0) {
                addToken = false;
            }

            start_token = token;

            //regex e("[\n\r]");
            //token = regex_replace(token, e, "");
            
            //token = token.replaceAll("[\n\r]", "");

            //token = token.replaceAll("[\n\r]", "");

            // Remove excess markup tags
            //token = token.replaceAll("<[^>]*>", "");
            
            // TODO: waste time fixing this
            //regex f("<[^>*>");
            //token = regex_replace(token, f, "");
            //cout << "regex f OK" << endl;

            //Remove  punctuation (any symbols that are not letters or numbers)
            // We keep hyphens
            //regex g("[^a-zA-Z0-9-]+");
            //token = regex_replace(token, g, "");
            
            // No trim function in C++11 .. only boost.
            //token = token.trim();


            token = this -> trim(token);

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
                /*
                // When words are hyphenated split them 
                // and keep the original as it has meaning
                vector<string> subTokens = this -> split(token, "-");
                //vector<string> subTokens;
                //String[] subTokens = token.split("-");
                if (subTokens.size() > 1) {
                    for (int j = 0; j < subTokens.size(); j++) {
                        arrayListStringTokens2.push_back(subTokens[j]);
                        //arrayListStringTokens2.insert();
                    }
                }
                */
            }

            end_token = token;

            if (token == "" || (token.length() == 0)) {
                addToken = false;
            }

            //arrayListStringTokens.set(i, token);
            if (addToken == true) {
                arrayListStringTokens2.push_back(token);
            }

        }
        
        for (int i = 0; i < arrayListStringTokens2.size(); i++) {
            addToken = true;
            token = arrayListStringTokens2[i]; // .get(i);

            //token = token.replaceAll("-", " ");

            regex h("-");
            //token = regex_replace(token, h, regex("\\s"));
            token = regex_replace(token, h, string(" "));


            //token = token.trim();
            token = this -> trim(token);

            if (token == "" || (token.length() == 0)) {
                addToken = false;
            }

            // IMHO it's better to fold the entire text to lower rather than individual calls.
            // in terms of performance but I suppose we might lose information doing this before normalising
            // Case fold to lower case
            //token = token.toLowerCase();
            token = this -> toLower(token);

            if (addToken == true) {
                arrayListStringTokens2[i] = token;
                //arrayListStringTokens2.set(i, token);
            } else {
                // TODO: fix this by encapuslating the loop in an iterator for assignment 2               
                //arrayListStringTokens2.set(i, "this is not a token");
                arrayListStringTokens2[i] = "this is not a token";
                //arrayListStringTokens2.set(i, "");

                // Weird behaviour withe remove method, it's causing 
                // the next loop to evaluate this element as "" or probably null
                //arrayListStringTokens2.remove(i);
            }
            //arrayListStringTokens2.
            // Starts from 0 so safe to do this
            //arrayListStringTokens2.add(i, token);
        }
        
        vector<string>::iterator it2;
        it2 = arrayListStringTokens2.begin();
        while (it2 != arrayListStringTokens2.end()) {
            bool add_token = true;
            token = *it2;// -> first;

            if (token == "this is not a token") {
                add_token = false;
            }

            if (token == "" || (token.length() == 0)) {
                add_token = false;
            }


            if (stoplist_file_specified == true) {
                // Exclude stopped words
                bool excluded = stoplist_file_manager.in_stoplist(token);
                if (excluded == true) {
                    add_token = false;
                }
            }

            if (add_token == true) {
                cleaned_tokens.push_back(token);
            }
            // Early cleanup
            it2++;
        };
        doc_class.cleaned_tokens = cleaned_tokens;
        
        string docid = doc_class.docid;
        int id = doc_class.id;

        if (print_content_terms == true) {
            cout << "\nDOCID: " + docid << endl;
            cout << "ID: " + id << endl;
            cout << "Content terms: " << endl;
            string cleaned_token = "";
            for (int i = 0; i < cleaned_tokens.size(); i++) {
                cleaned_token = cleaned_tokens[i];
                cout << cleaned_token << endl;
            }
        }

        // Probably unnecessary extra step
        // Try to Free memory?
        arrayListStringTokens.clear();
        return doc_class;
    }

    // Parse the doc text and extract content into the Doc_class object
    map<int, Doc_class> Indexer::extract_doc_text_from_doc_array(map<int, Doc_class> blob_doc_array) {
        map<int, Doc_class>::iterator it;
        int key = 0;
        it = blob_doc_array.begin();
        
        while (it != blob_doc_array.end()) {
            key = it -> first;
            Doc_class doc_class;// = NULL;
            doc_class = it -> second;

            string docid_in_doc = get_data_in_tag_multiline(doc_class, "<DOCID>", "</DOCID>");
            docid_in_doc = this -> trim(docid_in_doc);
            doc_class.docid = docid_in_doc;
            string headline_in_doc = get_data_in_tag_multiline(doc_class, "<HEADLINE>", "</HEADLINE>");
            headline_in_doc = this -> trim(headline_in_doc);
            doc_class.headline = headline_in_doc;
            string text_in_doc = get_data_in_tag_multiline(doc_class, "<TEXT>", "</TEXT>");
            doc_class.text = text_in_doc;

            // Might be unnecessary
            blob_doc_array[key] = doc_class;
            it++;
        };
                
        //gc_collect_cycles();
        return blob_doc_array;
    }

    // Extract data from within a set of tags

    string Indexer::get_data_in_tag_multiline(Doc_class doc_class, string start_tag, string end_tag) {
        bool found_whole_tag_status = false;
        string text = "";
        bool start_tag_found = false;
        bool end_tag_found = false;
        int start_tag_start_position = 0;
        int end_tag_start_position = 0;
        int start_tag_end_position = 0;
        int end_tag_end_position = 0;

        string doc_raw_text = doc_class.raw_text;
        
        start_tag_start_position = doc_raw_text.find(start_tag, 0);

        //std::string::npos is probably -1
        if (start_tag_start_position != std::string::npos) {
            start_tag_found = true;
        } else {
            //echo "No start tag\n";
        }

        if (start_tag_found == true) {
            start_tag_end_position = start_tag_start_position + start_tag.length();
        }


        end_tag_start_position = doc_raw_text.find(end_tag, start_tag_end_position);

        // -1 not null as per PHP
        if (end_tag_start_position != std::string::npos) {
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

            // Read between the <DOC> </DOC>
            int length = end_tag_start_position - start_tag_end_position;

            // Call is different to PHP

            text = doc_raw_text.substr(start_tag_end_position, length);
            found_whole_tag_status = true;
        } else {
            //echo "Didn't find start and end tag\n";
        }
        
        return text;
    }



    
    // Search for the <DOC> start and </DOC> end tag
    //// There are other ways to find the end of a DOC such as looking for the next
    //// <DOC> tag where no </DOC> is found but we ignore this for simplicities sake
    //// and assume our data file is consistent

    Indexer::Ret_docs_from_blob Indexer::get_docs_from_blob(string blob, int blob_start_pos, map<int, Doc_class> blob_doc_array) {
        int docs_found = 0;

        bool stitch_blob = false;
        bool found_doc = false;
        int blob_end_pos = 0;

        bool found_whole_doc_status = false;

        // Find all the docs within our blob            
        do {
            // Get one document from file            

            Ret_find_doc ret = find_doc(blob, blob_start_pos, "<DOC>", "</DOC>");
            Doc_class doc_class;// = ret.doc_class;
            doc_class = ret.doc_class;
            found_whole_doc_status = ret.found_whole_doc_status;
            blob_end_pos = ret.blob_end_pos;
            bool end_doc_found = ret.end_doc_found;
            bool start_doc_found = ret.start_doc_found;
                        
            int ordinal_number = doc_class.id;
            if (doc_class.null == false)
            {
                int ordinal_number = doc_class.id;
                blob_doc_array[ordinal_number] = doc_class;
                docs_found++;
                found_doc = true;
            } else {
                cout << "Doc class is null!?" << endl;
            }

            // Move blob pointer forward
            blob_start_pos = blob_end_pos;

            // No doc in blob?
            if ((found_whole_doc_status == false) && (docs_found == 0)) {
                // If blob is empty - we want to stitch with another blob
                stitch_blob = true;
                break;
            }

            // no more docs in blob
            if ((found_whole_doc_status == false) && (docs_found > 0)) {
                break;
            }

        } while (found_whole_doc_status == true);
                
        Ret_docs_from_blob ret;       
        ret.blob_end_pos = blob_end_pos;
        ret.found_doc = found_doc;
        ret.stitch_doc = stitch_blob;
        ret.blob_doc_array = blob_doc_array;
        return ret;
    }
    

    
    // Search for <DOC> tag start and end 

    Indexer::Ret_find_doc Indexer::find_doc(string blob, int blob_start_pos, string start_tag, string end_tag) {
        int blob_end_pos = blob_start_pos;
        bool found_whole_doc_status = false;
        string doc_text = "";
        bool start_doc_found = false;
        bool end_doc_found = false;
        Doc_class doc_class;// = NULL;
        doc_class.null = true;
        int start_tag_start_position = 0;
        int end_tag_start_position = 0;
        int start_tag_end_position = 0;
        int end_tag_end_position = 0;
        
        //start_tag_start_position = blob.indexOf(start_tag, blob_start_pos);
        start_tag_start_position = blob.find(start_tag, blob_start_pos);

        if (start_tag_start_position != -1) {
            start_doc_found = true;
        } else {
            //echo "No start tag\n";
        }

        if (start_doc_found == true) {
            start_tag_end_position = start_tag_start_position + start_tag.length();
        }

        end_tag_start_position = blob.find(end_tag, start_tag_start_position);

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
            // Next read pointer should be after the closing tag <DOC>  CONTENT </DOC> 
            blob_end_pos = end_tag_end_position;

            // Read between the <DOC> </DOC>
            int length = end_tag_start_position - start_tag_end_position;

            // Call is different to PHP
            doc_text = blob.substr(start_tag_end_position, length);

            int ordinal_number = this -> all_doc_number;

            doc_class = Doc_class(ordinal_number);
            doc_class.id = ordinal_number;
            doc_class.raw_text = doc_text;
            found_whole_doc_status = true;
            this -> all_doc_number = this -> all_doc_number + 1;
        } else {
            //echo "Didn't find start and end tag\n";
        }
                
        Ret_find_doc ret;
        ret.doc_class = doc_class;
        ret.found_whole_doc_status = found_whole_doc_status;
        ret.blob_end_pos = blob_end_pos;
        ret.start_doc_found = start_doc_found;
        ret.end_doc_found = end_doc_found;
        
        return ret;               
    }
    

    

    Indexer::Ret_next_blob Indexer::get_next_blob(FILE *fp, long filesize, int file_start_pos, int length_to_read, bool stitch_blob) 
    {
        string new_blob = "";
        string blob = "";
               
        length_to_read = get_length_to_read(length_to_read, filesize, file_start_pos, stitch_blob);

        new_blob = read_blob(fp, length_to_read);
        

        if (stitch_blob == true) {
            blob = new_blob;
        } else {
            // Reset blob read position as we have a new blob
            blob = new_blob;
        }

        Ret_next_blob ret;
        ret.blob = blob;
        ret.length_to_read = length_to_read;
        
        return ret;        
    }

    int Indexer::get_length_to_read(int length_to_read, long filesize, int file_start_pos, bool stitch_blob) {

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
            length_to_read = size - file_start_pos;
            eof = true;
        }
               
        return length_to_read;
    }

    bool Indexer::increment_file_pointer(FILE *fp, int new_start_position) {
        bool seek_status = false;
        if (eof == false) {

            if (verbose == true) {
                //echo "Moving file seek position: " . $new_start_position . "\n";
            }

            fseek(fp, new_start_position, SEEK_SET);
            seek_status = true;
        }

        return seek_status;
    }

    string Indexer::read_blob(FILE *fp, int length_to_read) {
        string s(length_to_read, '\0');
        
        // Returns length or -1 if EOF
        //ptr, size of element, number of elements to read, fp)
        int bytes_read = fread(&s[0], 1, length_to_read, fp);
        return s;
    }

    int Indexer::get_next_file_pointer(int file_start_pos, int blob_start_pos, bool stitch_blob) {

        if (stitch_blob == true) {
        } else {
            file_start_pos += blob_start_pos;
        }

        return file_start_pos;
    }

    void Indexer::write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(map<string, Term_statistics> inverted_list) {

        invlists_manager.open_invlists_file();

        int inverted_list_count = inverted_list.size();
        int inverted_list_processed = 0;
        
        map<string, Term_statistics>::iterator it;
        string term = "";
        it = inverted_list.begin();
        while (it != inverted_list.end()) {
            term = it -> first;
            Term_statistics term_statistics = it -> second;
            
            if (term == "") {
                it++;
                continue;
            }

            Lexicon_item lexicon_item = lexicon_file_manager.get_lexicon_item(term);
            if (lexicon_item.null == true)
            {
                bool padding = false;
                int padded_fixed_block_size = 0;

                Invlists_manager::Ret_create_invlist_block ret = invlists_manager.create_invlist_block_in_memory_cache_simple_array(term, padding, padded_fixed_block_size);
                int created_block_size = ret.size_of_block;
                int created_content_size = ret.content_size;

                lexicon_item = Lexicon_item(term, 0,
                        created_block_size,
                        created_content_size);
                lexicon_item.term = term;
                lexicon_item.file_offset = 0;
                lexicon_item.disk_block_size = 0;
                lexicon_item.content_size = 0;
                lexicon_file_manager.add_lexicon_item(lexicon_item);
                
            } else {
                cout << "lexicon item wasn't null for write_out function " << endl;
            }           
            if (lexicon_item.null == true) 
            {
                cout << "write_out_inverted_list lexicon item is null for update" << endl;
                cout << "lexicon_item term is: " << lexicon_item.term << endl;
            }
            if (term_statistics.null == true)
            {
                cout << "write_out_inverted_list term statistics is null for update" << endl;
                cout << "term_statistics term is: " << term_statistics.term << endl;
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
                    cout << "write_out_inverted_list() processed inverted list: " << inverted_list_processed << " of " << inverted_list_count << endl;
                }
            }
            it++;
        };
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

    void Indexer::write_buffer_simple_array() {
        map<string, Lexicon_item> lexicon_array = lexicon_file_manager.get_lexicon();
        invlists_manager.open_invlists_file();

        int current_offset = 4;
        if (verbose == true) {
            cout << "write_buffer() re-indexing buffer" << endl;
        }

        int lexicons_processed = 0;
        int lexicon_size = lexicon_array.size();
        if (verbose == true) {
            cout << "write_buffer() Lexicon size is " << lexicon_size << endl;
        }

        int new_filesize = 0;
        // Correct lexicon/invlists offsets
        map<string, Lexicon_item>::iterator it;
        it = lexicon_array.begin();
        string term = "";
        Lexicon_item lexicon_item;// = NULL;
        while (it != lexicon_array.end()) {
            term = it -> first;
            lexicon_item = it -> second;

            lexicon_item.file_offset = current_offset;
            // Increment offset
            int disk_block_size = lexicon_item.disk_block_size;
            current_offset = current_offset + disk_block_size;
            lexicon_file_manager.update_lexicon_item(lexicon_item);

            new_filesize += lexicon_item.content_size;

            lexicons_processed++;
            if (lexicons_processed % 1000 == 0) {
                if (verbose == true) {
                    cout << "write_buffer() re-index lexicons processed " << lexicons_processed << " of " << lexicon_size << endl;
                }
            }
            it++;
        }

        //1296240    1296240
        if (verbose == true) {
            cout << "write_buffer() file size estimate: " << new_filesize << endl;
        }

        string data2 = "";

        int file_pointer = 0;
        int lex_total = 0;

        if (verbose == true) {
            cout << "write_buffer() collating inverted list data" << endl;
        }

        int blocks_processed = 0;
        lexicon_size = lexicon_array.size();
        map<string, Lexicon_item>::iterator it2;
        


        it2 = lexicon_array.begin();
        while (it2 != lexicon_array.end()) {
            term = it2 -> first;
            lexicon_item = it2 -> second;
            map<string, Data_block> data_blocks_array = invlists_manager.invlists_block_memory_cache.get_data_blocks_array();

            term = lexicon_item.term;

            map<string, Data_block>::iterator it3;
            it3 = data_blocks_array.find(term);
            if (it3 == data_blocks_array.end()) {
                cout << "No data block for term: " << term << endl;
            } else {
                Data_block data_block_class = it3 -> second;
                
                if (data_block_class.null == true)
                {
                    cout << "Data block null for write" << endl;
                    cout << data_block_class.term << endl;
                }
                cout << data_block_class.term << endl;

                string dd = data_block_class.get_block_data();
  
                // WRITE TO DISK
                int size_to_write = lexicon_item.disk_block_size;

                int file_offset_of_block = lexicon_item.file_offset;

                data2 = data2 + dd;
            }

            blocks_processed++;
            if (blocks_processed % 200 == 0) {
                if (verbose == true) {
                    cout << "write_buffer() collating inverted list data processed " << blocks_processed << " of " << lexicon_size << endl;
                    //System.out.println("collating inverted list data processed " + blocks_processed + " of " + lexicon_size);
                }
            }
            
            it2++;
        }

        if (verbose == true) {
            cout << "write_buffer() writing out inverted lists" << endl;
            cout << "write_buffer() size to write " << data2.length() << endl;
            cout << "write_buffer() Writing to " << invlists_filename << endl;
        }

        FILE *fp = fopen(invlists_filename.c_str(), "wb");
        fseek(fp, integer_length, SEEK_SET);
        
        fwrite(data2.c_str(), 1, data2.length(), fp);
        //fwrite(data2.data(), 1, data2.length(), fp);
        fclose(fp);

        if (measure_times == true) {
            //System.out.println("\n\nWrite buffer time (flush cache to disk): " + write_buffer_time + "\n");   
        }
    }

    string Indexer::trim(string token) {
        regex z("^\\s+");
        token = regex_replace(token, z, string(""));

        regex x("\\s+$");
        token = regex_replace(token, x, string(""));
        return token;
    }

    // Does not work with unicode

    string Indexer::toLower(string token) {
        for (auto& c : token) {
            c = tolower(c);
        }
        return token;
    }

    vector<string> Indexer::split(string token, string split_regex_string) {
        
        //cout << "input token:" << token << endl;
        vector<string> strings;
        regex split_regex(split_regex_string);
        //regex split_regex("\\s+");
        std::regex_token_iterator<std::string::iterator> i(token.begin(), token.end(), split_regex, -1);
        regex_token_iterator<string::iterator> end; // Default constructor is end of sequence
        while (i != end) {
            strings.push_back(*i);
            //strings.insert(i);
            *i++;
        }
        if (strings.size() > 1)
        {
        //cout << "output token size:" << strings.size() << endl;
        }
        return strings;
    }

    int Indexer::parseLine(char* line) {
        // This assumes that a digit will be found and the line ends in " Kb".
        int i = strlen(line);
        const char* p = line;
        while (*p < '0' || *p > '9') p++;
        line[i - 3] = '\0';
        i = atoi(p);
        return i;
    }

    int Indexer::get_memory_stats() { //Note: this value is in KB!
        int result = -1;
        /*
        FILE* file = fopen("/proc/self/status", "r");
        
        char line[128];

        while (fgets(line, 128, file) != NULL) {
            if (strncmp(line, "VmRSS:", 6) == 0) {
                result = parseLine(line);
                break;
            }
        }
        fclose(file);
         * */
        return result;
    }