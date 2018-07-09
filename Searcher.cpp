/* 
 * File:   Indexer.cpp
 * Author: z
 * 
 * Created on March 22, 2017, 10:25 PM
 */

#include "Configuration.h"
#include "Searcher.h"
#include "Lexicon_item.h"
#include "Term_statistics.h"
#include "Invlists_manager.h"
#include "Lexicon_file_manager.h"
#include "Map_file_manager.h"
//#include <cstdlib>
//#include <iostream>
#include <map>
#include <vector>
#include <string>
#include <list>
#include <vector>
#include <unordered_map>
#include <regex> // C++11
#include <iostream>

using namespace std;
/*
    // an array of class Term_statistics
    // the key to this array is the "term" name since this is unique 
    // and storable as the key
    string lexicon_filename = "";
    string map_filename = "";
    string invlists_filename = "";
    map<string, Term_statistics> inverted_list;// = NULL;
    bool compression = false;
    bool measure_time = false;
    vector<string> query_terms_array; // = NULL;
    Lexicon_file_manager lexicon_file_manager;// = NULL;
    Invlists_manager invlists_manager;// = NULL;
    Map_file_manager map_file_manager;// = NULL;
    bool verbose = false;
    Configuration configuration;// = new Configuration();
*/
    //vector<string> query_terms_array = new vector<string>();

    Searcher::Searcher() {
    }

    Searcher::Searcher(const Searcher& orig) {
    }

    Searcher::~Searcher() {
    }
    
    Searcher::Searcher(Configuration configuration) {

    }
   


    Searcher::Ret_arguments Searcher::arguments(int argc, char** argv) {
        vector<string> options;
        list<string> flags;
        map<string, string> flags_map;
        //list<string> arguments;
        vector<string> arguments;
        map<string, string>::iterator it;
        cout << "Parsing arguments" << endl;

        for (int i = 0; i < argc; i++) {
            string argument = argv[i];
            cout << argument.length() << endl;

            // Flag
            if (argument.substr(0, 1) == "-") {
                flags.push_back(argument);
                flags_map[argument] = argument;
                continue;
            }

            // Otherwise argument
            arguments.push_back(argument);
        }       

        it = flags_map.find("-x");
        if (it != flags_map.end()) {
            verbose = true;
        }

        //Configuration.set_boolean("verbose", verbose);

        if (arguments.size() >= 4)
        {
            //lexicon_filename = arguments[1];
            lexicon_filename = arguments.at(1);

            invlists_filename = arguments.at(2);
            //invlists_filename = arguments[2];

        //map_filename = options[3];        
        map_filename = arguments.at(3);        
            //map_filename = arguments[3];

        //vector<string> query_terms_array();// = new vector<string>();
        int last_element = arguments.size();
        
        for (int i = 4; i < last_element; i++) {
            //query_terms_array.insert(options[i]);
            query_terms_array.push_back(arguments.at(i));
        }
        

        }

        configuration.set_boolean("verbose", verbose);

        cout << "lexicon filename:" << lexicon_filename << endl;
        cout << "invlists filename: " << invlists_filename << endl;
        cout << "map filename: " << map_filename << endl;
        
        Ret_arguments ret;
        ret.options = options;
        ret.flags = flags;
        ret.arguments = arguments;
        


        return ret;
    }

    void Searcher::parse_arguments(int argc, char** argv) {
        Ret_arguments ret = this -> arguments(argc, argv);
        cout << "parsing arguments";

        vector<string> options = ret.options;

    }

    void Searcher::initialise() {        
        
        map<string, bool> config_array = map<string, bool>();// = new map<string, void *>();

        config_array["measure_time"] = measure_time;

        //Configuration = new Configuration();    

        Lexicon_file_manager lexicon_file_manager(lexicon_filename,
                configuration);
        lexicon_file_manager.load_lexicon();

        Map_file_manager map_file_manager(map_filename, configuration);

        map_file_manager.load_map();
        Invlists_manager invlists_manager(invlists_filename,
                lexicon_filename,
                lexicon_file_manager,
                configuration);
        
        this -> map_file_manager = map_file_manager;
        this -> lexicon_file_manager = lexicon_file_manager;
        this -> invlists_manager = invlists_manager;

    }

    void Searcher::start_processing() {      
        
        //query_terms_array
        for (int i = 0; i < query_terms_array.size(); i++) {
            string query_term = query_terms_array[i];

            Term_statistics term_statistics = search(query_term);

            if (term_statistics.null == false)
            {
                cout << query_term << endl;
                cout << term_statistics.number_of_unique_documents_occurs_in << endl;               

                map<int, int> occurances_per_document_array = term_statistics.occurances_per_document_array;
               
                map<int, int>::iterator it;
                it = occurances_per_document_array.begin();
                while (it != occurances_per_document_array.end()) {
                    int id = it -> first;
                    int occurances_per_document = it -> second;

                    Map_item doc = map_file_manager.get_mapping(id);

                    if (doc.null == false)
                    {
                        cout << doc.docid << " " << occurances_per_document << endl;
                    } else {
                        cout << "error retrieving doc from map" << endl;
                    }
                    it++;
                }

            } else {
                cout << "Search term not found: " << query_term << endl;
            }
        }

    }

    Term_statistics Searcher::search(string query_term) {
        Term_statistics term_statistics;// = NULL;

        regex e("-");
        query_term = regex_replace(query_term, e, "");
        
        cout << "query term: " << query_term << endl;

        lexicon_file_manager.read_lexicon_to_memory();
        cout << "getting lexicon item " << endl;
        Lexicon_item lexicon_item = lexicon_file_manager.get_lexicon_item_from_lexicon(query_term);
        
        cout << "got lex item " << endl;
        cout << "lex item .null is" << lexicon_item.null << endl;

        if (lexicon_item.null == false) {
            cout << "Trying to get inverted list from disk " << endl;
            term_statistics = invlists_manager.get_inverted_index_from_disk(lexicon_item);
            cout << "got inverted list from disk " << endl;
            if (verbose == true) {
                cout << lexicon_item.toString() << endl;
            }

            if (verbose == true) {
            }

        } else {
            cout << "not in lexicon: " << query_term << endl;
        }

        return term_statistics;
    }
