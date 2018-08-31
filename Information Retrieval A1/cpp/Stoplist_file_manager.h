/* 
 * File:   Stoplist_file_manager.h
 * Author: z
 *
 * Created on March 22, 2017, 10:29 PM
 */

#ifndef STOPLIST_FILE_MANAGER_H
#define STOPLIST_FILE_MANAGER_H

#include "Configuration.h"
#include <string>
#include <map>
using namespace std;
class Stoplist_file_manager {


public:    
    Stoplist_file_manager();
    Stoplist_file_manager(const Stoplist_file_manager& orig);
    Stoplist_file_manager(string stoplist_filename, Configuration configuration);
    virtual ~Stoplist_file_manager();
    void load_stoplist();
    bool in_stoplist(string term);
private:
    string stoplist_filename = "";
    map<string, string> stoplist;// = NULL;

};

#endif /* STOPLIST_FILE_MANAGER_H */

