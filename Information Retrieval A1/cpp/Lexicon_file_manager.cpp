/*
import code.Configuration;
import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.RandomAccessFile;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
*/

#include "Configuration.h"
#include "Lexicon_file_manager.h"
#include <string>
#include <vector>
#include <map>
#include <fstream>
#include <iostream>
#include <sstream>
#include <stdio.h> // fopen
//#include <iostream> // atoi
#include <cstring> // atoi
#include <cstdlib> // atoi
#include <stdlib.h>     /* atoi */
//#include <stdlib> // atoi
using namespace std;
/*
    Configuration configuration;// = NULL;
    bool verbose = false;

    string stoplist_filename = "";
    map<string, Lexicon_item> lexicon_array;// = NULL;

    string lexicon_filename = "map";
    bool immediate_lexicon_rewrites = false;
    //private RandomAccessFile r = null;
    FILE *fp = NULL;
*/    
    
Lexicon_file_manager::Lexicon_file_manager() {
}

Lexicon_file_manager::Lexicon_file_manager(const Lexicon_file_manager& orig) {
}

Lexicon_file_manager::~Lexicon_file_manager() {
}

Lexicon_file_manager::Lexicon_file_manager(string lexicon_filename, Configuration configuration) {
        this->lexicon_filename = lexicon_filename;
        this -> configuration = configuration;
        //lexicon_array = new map<string, Lexicon_item>();
        map<string, Lexicon_item> lexicon_array();
        verbose = configuration.get_boolean("verbose");
}

    bool  Lexicon_file_manager::load_lexicon() {

        lexicon_array = read_lexicon_to_memory();
        return true;
    }

    Lexicon_item Lexicon_file_manager::get_lexicon_item_from_lexicon(string search_term) {
        Lexicon_item lexicon_item;// = NULL;
        
         map<string, Lexicon_item>::iterator map_it;
         
         map_it = lexicon_array.find(search_term);
         
         if (map_it != lexicon_array.end())
         {
             lexicon_item = map_it -> second;
         }
         
        return lexicon_item;
    }
    
    map<string, Lexicon_item> Lexicon_file_manager::read_lexicon_to_memory() {
        map<string, Lexicon_item> lexicon_array = map<string, Lexicon_item>();// = new HashMap<String, Lexicon_item>();
        if (lexicon_filename != "") {
            
            //std::ifstream fin(lexicon_filename);
            //std::stringstream buffer;
            //buffer << fin.rdbuf();
            //std::string result = buffer.str();
            
            //std::ifstream in(lexicon_filename, std::ifstream::in);
            //std::ifstream in(lexicon_filename);
            
            FILE *fp = fopen(lexicon_filename.c_str(), "r");
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
                string term = split_string[0];                
                    int file_offset = atoi(split_string[1].c_str());
                    int disk_block_size = atoi(split_string[2].c_str());
                    int content_size = atoi(split_string[3].c_str());
                    
                    //Lexicon_item lexicon_item = new Lexicon_item(term, file_offset, disk_block_size, content_size);
                    Lexicon_item lexicon_item(term, file_offset, disk_block_size, content_size);
                    //System.out.println("From string " + sCurrentLine);
                    //System.out.println("Created lex item with " + term + " " + file_offset + " " + disk_block_size + " " + content_size);
                    //lexicon_array.insert(term, lexicon_item);
                    lexicon_array[term] = lexicon_item;
                //stoplist.insert(sCurrentLine, sCurrentLine);
            };
            
            
               


            this->lexicon_array = lexicon_array;
        }

        return lexicon_array;
    }

/*
    map<string, Lexicon_item> Lexicon_file_manager::read_lexicon_to_memory() {
        map<string, Lexicon_item> lexicon_array = map<string, Lexicon_item>();// = new HashMap<String, Lexicon_item>();
        if (lexicon_filename != "") {
            
            //std::ifstream fin(lexicon_filename);
            //std::stringstream buffer;
            //buffer << fin.rdbuf();
            //std::string result = buffer.str();
            
            //std::ifstream in(lexicon_filename, std::ifstream::in);
            std::ifstream in(lexicon_filename);
                  //std::string contents((std::istreambuf_iterator<char>(in)), 
            //std::istreambuf_iterator<char>()); 

            string sCurrentLine;

            while (std::getline(in, sCurrentLine, '\n')) {
                char token = ',';
                vector<string> split_string = this -> explode(sCurrentLine, token);                  
                string term = split_string[0];                
                    int file_offset = atoi(split_string[1].c_str());
                    int disk_block_size = atoi(split_string[2].c_str());
                    int content_size = atoi(split_string[3].c_str());
                    
                    //Lexicon_item lexicon_item = new Lexicon_item(term, file_offset, disk_block_size, content_size);
                    Lexicon_item lexicon_item(term, file_offset, disk_block_size, content_size);
                    //System.out.println("From string " + sCurrentLine);
                    //System.out.println("Created lex item with " + term + " " + file_offset + " " + disk_block_size + " " + content_size);
                    //lexicon_array.insert(term, lexicon_item);
                    lexicon_array[term] = lexicon_item;
                //stoplist.insert(sCurrentLine, sCurrentLine);
            };
            
            
               


            this->lexicon_array = lexicon_array;
        }

        return lexicon_array;
    }
    */
    
const vector<string> Lexicon_file_manager::explode(const string& s, const char& c) {
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


/*
    // from: http://www.cplusplus.com/articles/2wA0RXSz/
    const vector<string> Lexicon_file_manager::explode(const string& s, const char& c) {
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
    }
*/    

    Lexicon_item Lexicon_file_manager::get_lexicon_item(string term) {
        Lexicon_item lexicon_item;// = NULL;

        map<string, Lexicon_item>::iterator it;

        it = lexicon_array.find(term);
        if (it != lexicon_array.end()) {
            lexicon_item = it -> second;
        }
        return lexicon_item;
    }

    void Lexicon_file_manager::update_lexicon_item(Lexicon_item lexicon_item) {
        string term = lexicon_item.term;
        // Don't use insert
        lexicon_array[term] = lexicon_item;
    }

    void Lexicon_file_manager::zero_lexicon_file() {
        FILE *fp = fopen(lexicon_filename.c_str(), "w");
        string s = " ";
        fwrite(s.c_str(), 1, 1, fp);
        fclose(fp);
    }

    void Lexicon_file_manager::initialise_lexicon_file() {
        FILE *fp = fopen(lexicon_filename.c_str(), "w");
        string s = " ";
        fwrite(s.c_str(), 1, 1, fp);
        fclose(fp);
    }

    void Lexicon_file_manager::add_lexicon_item(Lexicon_item lexicon_item) {
        string term = lexicon_item.term;
        
        map<string, Lexicon_item>::iterator lex_it;
        
        lex_it = lexicon_array.find(term);
        if (lex_it == lexicon_array.end())
        {
            lexicon_array[term] = lexicon_item;
        } else {
        }

        //Writes lexicon every time we update the lex very bad :P
        if (immediate_lexicon_rewrites
                == true) {
            write_lexicon();
        }

    }

    map<string, Lexicon_item> Lexicon_file_manager::get_lexicon() {
        return lexicon_array;
    }

    void Lexicon_file_manager::write_lexicon() {
        FILE *fp = NULL;

        if (verbose == true)
        {
           cout << "opened " << lexicon_filename << " for write" << endl;
        }
        //RandomAccessFile r = null;
        fp = fopen(lexicon_filename.c_str(), "w");

        string lex_text = "";
        int lex_size = lexicon_array.size();
        int i = 0;
        map<string, Lexicon_item>::iterator it;
        
        it = lexicon_array.begin();
        string inner_lex_text = "";
        Lexicon_item lexicon_item;// = NULL;
        while(it != lexicon_array.end())
        {
            lexicon_item = it -> second;
            inner_lex_text = this -> create_lexicon_item_text(lexicon_item);
            lex_text += inner_lex_text;
            if (i % 1000 == 0) {
                if (verbose == true)
                {                
                    cout << "collating lexicon text for write: " << i << " of " << lex_size << endl;
                }
            }

            i++;
            it++;
        }
        

        if (verbose == true)
        {
          cout << "writing lexicon text to disk";
        }
        
        if (verbose == true)
        {
           cout << "lex text length is: " << lex_text.length() << endl;
        }
        
        fwrite(lex_text.c_str(), 1, lex_text.length(), fp);
        
        
        //fwrite(lex_text.data(), 1, lex_text.length(), fp);
        
        fclose(fp);
    }

    string Lexicon_file_manager::create_lexicon_item_text(Lexicon_item lexicon_item) {
        string text = "";
                
        char str[50];
        sprintf(str, "%u,%u,%u", lexicon_item.file_offset, lexicon_item.disk_block_size, lexicon_item.content_size);
        text += lexicon_item.term + "," + str + "\n";
       
        return text;
    }

    FILE* Lexicon_file_manager::open_lexicon_file() {

        FILE *fp = fopen(lexicon_filename.c_str(), "rw");
        this->fp = fp;
        return fp;
    }

    void Lexicon_file_manager::close_lexicon_file() {
        //
    }

    void Lexicon_file_manager::close_lexicon_file_real() {
        if (this->fp != NULL) {
            fclose(fp);
            this->fp = NULL;
        }
    }
