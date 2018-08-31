/* 
 * File:   Map_item.cpp
 * Author: z
 * 
 * Created on April 3, 2017, 7:30 PM
 */

#include "Map_item.h"
#include <string>
using namespace std;
/*
    int id = 0;
    string docid = "";
    bool null = true;
*/
    Map_item::Map_item() {
    }

    Map_item::Map_item(const Map_item& orig) {
        this->id = orig.id;
        this->docid = orig.docid;
        this->null = orig.null;
    }

    Map_item::~Map_item() {
    }

    Map_item::Map_item(int id, string docid) {
        this->id = id;
        this->docid = docid;
        null = false;
    }