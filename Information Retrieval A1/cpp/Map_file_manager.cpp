/* 
 * File:   Map_file_manager.cpp
 * Author: z
 * 
 * Created on March 22, 2017, 10:28 PM
 */

/*
package code;

import code.Configuration;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.RandomAccessFile;
import java.util.HashMap;
import java.io.*;
import java.util.*;
 */
#include "Map_file_manager.h"
#include "Map_item.h"
#include "Configuration.h"
//include import java.io.*;
//import java.util.*;
#include <cstdlib>
#include <iostream>
#include <string>
#include <map>
#include <fstream>
#include <iterator>
#include <stdio.h> // fopen
using namespace std;

/*
    Configuration configuration;// = NULL;
    bool verbose = false;
    string map_filename = "";
    //map<int, Map_item> map_array = NULL;
    map<int, Map_item> map_array;
    //string map_filename = "map";
    bool immediate_map_rewrites = false;
    //RandomAccessFile r = null;
    FILE *fp = NULL;
 */

Map_file_manager::Map_file_manager() {
}

Map_file_manager::Map_file_manager(const Map_file_manager& orig) {
}

Map_file_manager::~Map_file_manager() {
}

Map_file_manager::Map_file_manager(string map_filename, Configuration configuration) {
    this->map_filename = map_filename;
    this->configuration = configuration;
    this -> map_array = map<int, Map_item>(); // = new map<int, Map_item>();

    this -> verbose = configuration.get_boolean("verbose");
}
void Map_file_manager::load_map() {
    map<int, Map_item> map_array = this -> map_array;
    if (map_filename != "") {

        //map_array.clear();
        //map<int, Map_item> map_array = new map<int, Map_item>();
        //FILE *fp = fopen(stoplist_filename, "r");
        //FileReader f = null;

        /*
        std::ifstream fin(map_filename);
        std::stringstream buffer;
        buffer << fin.rdbuf();
        std::string result = buffer.str();
         */
            
            FILE *fp = fopen(map_filename.c_str(), "r");
            /*
            std::string contents((std::istreambuf_iterator<char>(in)), 
            std::istreambuf_iterator<char>()); */

            char buffer[100];
            string sCurrentLine = "";

            while(fgets(buffer, 100, fp) != NULL)
            {
                sCurrentLine = buffer;
            char token = ',';
            vector<string> split_string = this -> explode(sCurrentLine, token);
            string docid = split_string[0];
            int id = atoi(split_string[1].c_str());
            Map_item map_item(id, docid); // = new Map_item(id, docid);
            map_array[id] = map_item;
            //cout << "map array item count: " << map_array.size() << endl;
            //stoplist.insert(sCurrentLine, sCurrentLine);
        }
        //this -> stoplist = stoplist;
    }
    this -> map_array = map_array;
    //map_array = this->map_array;
}


/*
void Map_file_manager::load_map() {
    map<int, Map_item> map_array = this -> map_array;
    if (map_filename != "") {

        //map_array.clear();
        //map<int, Map_item> map_array = new map<int, Map_item>();
        //FILE *fp = fopen(stoplist_filename, "r");
        //FileReader f = null;

        //std::ifstream fin(map_filename);
        //std::stringstream buffer;
        //buffer << fin.rdbuf();
        //std::string result = buffer.str();
        std::ifstream in(map_filename);

        string sCurrentLine;
        

        while (std::getline(in, sCurrentLine, '\n')) {
            char token = ',';
            vector<string> split_string = this -> explode(sCurrentLine, token);
            string docid = split_string[0];
            int id = atoi(split_string[1].c_str());
            Map_item map_item(id, docid); // = new Map_item(id, docid);
            map_array[id] = map_item;
            //cout << "map array item count: " << map_array.size() << endl;
            //stoplist.insert(sCurrentLine, sCurrentLine);
        }
        //this -> stoplist = stoplist;
    }
    this -> map_array = map_array;
    //map_array = this->map_array;
}

*/

const vector<string> Map_file_manager::explode(const string& s, const char& c) {
    //string buff{""};
    string buff("");
    vector<string> v;
    for (int i = 0; i < s.length(); i++)
    {
        if (s[i] != c)
        {
            buff += s[i];
        } else {
            if (s[i] == c && buff != "")
            {
                v.push_back(buff);
                buff = "";
            }
        }
    }

    if (buff != "") v.push_back(buff);

    return v;
}


// from: http://www.cplusplus.com/articles/2wA0RXSz/

/*
const vector<string> Map_file_manager::explode_c17(const string& s, const char& c) {
    string buff{""};
    vector<string> v;

    for (auto n : s) {
        if (n != c) buff += n;
        else
            if (n == c && buff != "") {
            v.push_back(buff);
            buff = "";
        }
    }
    if (buff != "") v.push_back(buff);

    return v;
}*/

Map_item Map_file_manager::get_mapping(int ordinal_number) {
    Map_item map_item; // = NULL;

    map<int, Map_item>::iterator it;
    
    it = map_array.find(ordinal_number);
    if (it != map_array.end()) {
        map_item = it -> second;
    }
    
    return map_item;
}

void Map_file_manager::zero_map_file() {
    FILE *fp = fopen(map_filename.c_str(), "wb");
    string s = "";
    fwrite(s.c_str(), 1, 1, fp);
    fclose(fp);
}

void Map_file_manager::initialise_map_file() {
    FILE *fp = fopen(map_filename.c_str(), "wb");
    string s = "";
    fwrite(s.c_str(), 1, 1, fp);
    fclose(fp);
}

void Map_file_manager::add_mappings(map<int, Doc_class> blob_doc_array) {
    //Iterator < Map.Entry<int, Doc_class>> it = blob_doc_array.entrySet().iterator();
    map<int, Doc_class>::iterator it;
    int key = 0;
    int id = 0;
    string docid = "";
    Doc_class blob_doc; // = NULL;

    it = blob_doc_array.begin();

    //while (it.hasNext()) {
    while (it != blob_doc_array.end()) {
        //Map.Entry<Integer, Doc_class> pair = it.next();
        //key = pair.getKey();
        //blob_doc = pair.getValue();
        key = it -> first;
        blob_doc = it -> second;

        id = blob_doc.id;
        docid = blob_doc.docid;
        
        map<int, Map_item>::iterator it_map;

        it_map = map_array.find(id);
        // if we don't find it
        if (it_map == map_array.end()) {
            Map_item new_map_item(id, docid); // = new Map_item(id, docid);
            map_array[id] = new_map_item;
            //map_array.put(id, new_Map_item);
            //echo "added map item";
        } else {
            //echo "already exists in map";
        }

        it++;
    }
    
    //Writes lexicon every time we update the lex very bad :P
    if (immediate_map_rewrites == true) {
        write_map();
    }
}

void Map_file_manager::write_map() {
    FILE *fp = NULL;
    fp = fopen(map_filename.c_str(), "w");

    string map_text = "";
    //map_array.
    //map_array.size();
    int map_size = map_array.size();
    //stringstream ss;
    //StringBuilder sb = new StringBuilder();
    //Iterator < Map.Entry<Integer, Map_item>> it = map_array.entrySet().iterator();
    map<int, Map_item>::iterator it_map;

    it_map = map_array.begin();
    //it_map = map_array.find();

    string inner_map_text = "";
    int i = 0;

    //while (it.hasNext()) {
    while (it_map != map_array.end()) {
        //Map.Entry<Integer, Map_item> pair = it.next();
        //int key = pair.getKey();
        //Map_item Map_item = pair.getValue();

        int key = it_map -> first;
        Map_item map_item = it_map -> second;

        inner_map_text = this -> create_map_item_text(map_item);
        //ss.
        //map_text.append(inner_map_text);

        map_text += inner_map_text;
        i++;
        if (i % 200 == 0) {
            if (verbose == true) {
                //System.out.println("collating map text for write: " + i + " of " + map_size);
                cout << "collating map text for write:" << i << " of " << map_size << endl;
            }
        }

        it_map++;
    }

    //map_text = sb.toString();

    if (verbose == true) {
        //  System.out.println("writing map text to disk");
        cout << "writing map text to disk" << endl;
    }
    /*        try {
                r.writeBytes(map_text);
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
     * */
    //fwrite(map_text.c_str(), 1, map_text.length(), fp);
    fwrite(map_text.data(), 1, map_text.length(), fp);
    fclose(fp);
}

string Map_file_manager::create_map_item_text(Map_item map_item) {
    string text = "";

    string docid = map_item.docid;
    char ordinal_number_string[10];
    int ordinal_number = map_item.id;
    sprintf(ordinal_number_string, "%u", ordinal_number);

    string map_text = docid + "," + ordinal_number_string + "\n";
    
    /*
    cout << "docid: " << docid << endl;
    cout << "ordinal_number: " << ordinal_number << endl;
    cout << "ordinal_number_string: " << ordinal_number_string << endl;
    cout << "map_text: " << map_text << endl;
     * */

    return map_text;
}

FILE* Map_file_manager::open_map_file() {
    FILE *fp = NULL;
    fp = fopen(map_filename.c_str(), "wb");
    //this->fp = fp;
    return fp;
}

void Map_file_manager::close_map_file() {
    //
}

void Map_file_manager::close_map_file_real() {
    if (this -> fp != NULL) {
        fclose(this -> fp);
        this -> fp = NULL;
    }
}