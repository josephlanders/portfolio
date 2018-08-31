/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Searcher.h
 * Author: z
 *
 * Created on April 4, 2017, 6:29 PM
 */

#ifndef SEARCHER_H
#define SEARCHER_H

#include "Term_statistics.h"
#include <string>
#include <map>
#include <list>
#include <vector>
#include "Configuration.h"
#include "Lexicon_file_manager.h"
#include "Map_file_manager.h"
#include "Invlists_manager.h"
using namespace std;

class Searcher {
public:
    Searcher();
    Searcher(const Searcher& orig);
    virtual ~Searcher();
    Searcher(Configuration configuration);
        
    struct Ret_arguments
    {
        vector<string> options;
        list<string> flags;
        map<string, string> flags_map;
        vector<string> arguments;        
    };
    Ret_arguments arguments(int argc, char** argv);
    void parse_arguments(int argc, char** argv);
    void initialise();
    void start_processing();
private:
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
    
    Term_statistics search(string query_term);
};

#endif /* SEARCHER_H */

