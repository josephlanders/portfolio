//package code;

#include "Stoplist_file_manager.h"

#include "Configuration.h"
//include import java.io.*;
//import java.util.*;
#include <cstdlib>
#include <iostream>
#include <string>
#include <map>
#include <fstream>
#include <sstream>
#include <iterator>
using namespace std;
/*
    string stoplist_filename = "";
    map<string, string> stoplist;// = NULL;
*/
    Stoplist_file_manager::Stoplist_file_manager() {
    }

    Stoplist_file_manager::Stoplist_file_manager(const Stoplist_file_manager& orig) {
    }

    Stoplist_file_manager::~Stoplist_file_manager() {
    }

    Stoplist_file_manager::Stoplist_file_manager(string stoplist_filename, Configuration configuration) {
        this->stoplist_filename = stoplist_filename;
    }
    
    void Stoplist_file_manager::load_stoplist() {
        map<string, string> stoplist; // = new map<String, String>();

        if (stoplist_filename != "") {
            //FILE *fp = fopen(stoplist_filename, "r");
            //FileReader f = null;

            /*
            std::ifstream fin(stoplist_filename);
            std::stringstream buffer;
            buffer << fin.rdbuf();
            std::string result = buffer.str(); 
             */
            
            FILE *fp = fopen(stoplist_filename.c_str(), "r");
            /*
            std::string contents((std::istreambuf_iterator<char>(in)), 
            std::istreambuf_iterator<char>()); */

            char buffer[100];
            string sCurrentLine = "";

            while(fgets(buffer, 100, fp) != NULL)
            {
                stoplist[sCurrentLine] = sCurrentLine;
            }
            this -> stoplist = stoplist;
        }
    }

/*
    void Stoplist_file_manager::load_stoplist() {
        map<string, string> stoplist; // = new map<String, String>();

        if (stoplist_filename != "") {
            //FILE *fp = fopen(stoplist_filename, "r");
            //FileReader f = null;

            std::ifstream fin(stoplist_filename);
            std::stringstream buffer;
            buffer << fin.rdbuf();
            std::string result = buffer.str(); 
            std::ifstream in(stoplist_filename);
            string sCurrentLine;

            while (std::getline(in, sCurrentLine, '\n')) {
                stoplist[sCurrentLine] = sCurrentLine;
            }
            this -> stoplist = stoplist;
        }
    }*/

    bool Stoplist_file_manager::in_stoplist(string term) {
        map<string, string>::iterator it;
        bool status = false;
        it = stoplist.find(term);

        if (it != stoplist.end()) {
            status = true;
        }
        //string element = stoplist.get(term);
        //if(element != NULL)
        //{
        //status = true;
        //}
        return status;
    }