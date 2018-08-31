/* 
 * File:   Lexicon_item.cpp
 * Author: z
 * 
 * Created on April 3, 2017, 8:45 PM
 */
//package code;

#include <string>
#include "Lexicon_item.h"
using namespace std;
/*
    string term = "";
    int file_offset = 0;
    int disk_block_size = 0;
    int content_size = 0;
    bool null = true;
*/
    Lexicon_item::Lexicon_item() {
    }

    Lexicon_item::Lexicon_item(const Lexicon_item& orig) {
        this->term = orig.term;
        this->file_offset = orig.file_offset;
        this->disk_block_size = orig.disk_block_size;
        this->content_size = orig.content_size;
        this->null = orig.null;
    }

    Lexicon_item::~Lexicon_item() {
    }

    Lexicon_item::Lexicon_item(string term, int file_offset,
            int disk_block_size,
            int content_size) {
        this->term = term;
        this->file_offset = file_offset;
        this->disk_block_size = disk_block_size;
        this->content_size = content_size;
        this->null = false;
    }

    string Lexicon_item::toString() {
        string str = "term: " + term;
        str += "\n   file offset: " + file_offset;
        str += "\n   disk_block_size: " + disk_block_size;
        str += "\n   content_size: " + content_size;
        return str;
    }