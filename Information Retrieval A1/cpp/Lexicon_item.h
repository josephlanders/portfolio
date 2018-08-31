/* 
 * File:   Lexicon_item.h
 * Author: z
 *
 * Created on April 3, 2017, 8:45 PM
 */

#ifndef LEXICON_ITEM_H
#define LEXICON_ITEM_H
#include <string>
using namespace std;
class Lexicon_item {
public:
    bool null = true;
    string term = "LEXICON_ITEM_UNINITIALISED";
    int file_offset = 0;
    int disk_block_size = 0;
    int content_size = 0;
    Lexicon_item();
    Lexicon_item(const Lexicon_item& orig);
    virtual ~Lexicon_item();
        Lexicon_item(string term, int file_offset,
            int disk_block_size,
            int content_size);
    string toString();
private:

};

#endif /* LEXICON_ITEM_H */

