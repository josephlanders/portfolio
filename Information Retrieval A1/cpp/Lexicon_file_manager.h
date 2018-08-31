/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Lexicon_file_manager.h
 * Author: z
 *
 * Created on March 22, 2017, 10:28 PM
 */

#ifndef LEXICON_FILE_MANAGER_H
#define LEXICON_FILE_MANAGER_H
#include "Lexicon_item.h"
#include "Configuration.h"
#include <map>
#include <string>
#include <vector>
using namespace std;
class Lexicon_file_manager {
public:
    Lexicon_file_manager();
    Lexicon_file_manager(const Lexicon_file_manager& orig);
    Lexicon_file_manager(string lexicon_filename, Configuration configuration);
    virtual ~Lexicon_file_manager();
    void write_lexicon();
    bool  load_lexicon();
    Lexicon_item get_lexicon_item_from_lexicon(string search_term);
    Lexicon_item get_lexicon_item(string term);
    void update_lexicon_item(Lexicon_item lexicon_item);
    void add_lexicon_item(Lexicon_item lexicon_item);
    map<string, Lexicon_item> read_lexicon_to_memory();
    map<string, Lexicon_item> get_lexicon();
private:
    
    Configuration configuration;// = NULL;
    bool verbose = false;

    string stoplist_filename = "";
    map<string, Lexicon_item> lexicon_array;// = NULL;

    string lexicon_filename = "lexicon";
    bool immediate_lexicon_rewrites = false;
    //private RandomAccessFile r = null;
    FILE *fp = NULL;
    void zero_lexicon_file();
    void initialise_lexicon_file();
    string create_lexicon_item_text(Lexicon_item lexicon_item);
    FILE* open_lexicon_file();
    void close_lexicon_file();
    void close_lexicon_file_real();
    const vector<string> explode(const string& s, const char& c);
};

#endif /* LEXICON_FILE_MANAGER_H */

