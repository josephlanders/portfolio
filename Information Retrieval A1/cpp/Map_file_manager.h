/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Map_file_manager.h
 * Author: z
 *
 * Created on March 22, 2017, 10:28 PM
 */

#ifndef Map_file_manager_H
#define Map_file_manager_H
#include "Map_item.h"
#include "Doc_class.h"
#include "Configuration.h"
#include <map>
#include <string>
using namespace std;
class Map_file_manager {
public:
    Map_file_manager();
    Map_file_manager(const Map_file_manager& orig);
    Map_file_manager(string map_filename, Configuration configuration);
    virtual ~Map_file_manager();
    void write_map();
    void load_map();
    void add_mappings(map<int, Doc_class> blob_doc_array);
    Map_item get_mapping(int ordinal_number);
    //map<int, Map_item> map_array = map<int, Map_item>();
    map<int, Map_item> map_array; // = map<int, Map_item>();
private:
    Configuration configuration;// = NULL;
    bool verbose = false;
    string map_filename = "map";
    //map<int, Map_item> map_array = NULL;
    //map<int, Map_item> map_array = map<int, Map_item>();
    //string map_filename = "map";
    bool immediate_map_rewrites = false;
    //RandomAccessFile r = null;
    FILE *fp = NULL;
const vector<string> explode(const string& s, const char& c);
void zero_map_file();
void initialise_map_file();
string create_map_item_text(Map_item map_item);
FILE* open_map_file();
void close_map_file();
void close_map_file_real();
};

#endif /* Map_file_manager_H */

