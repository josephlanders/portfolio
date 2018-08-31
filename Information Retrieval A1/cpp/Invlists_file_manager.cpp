/*
package code;

import code.Configuration;
import java.util.*;
import java.nio.ByteBuffer;
import java.nio.*;
import java.io.*;
 */

#include "Invlists_file_manager.h"
#include "Lexicon_file_manager.h"
#include "Data_block.h"
#include <string>
#include <map>
#include <stdio.h> // fopen
#include <iostream>
using namespace std;
/*
string invlists_filename = "invlists";
string lexicon_filename = "lexicon";
int integer_length = 4; // 2 4 8
string integer_keyword = "L"; // S = 2, L = 4, Q = 8
int maximum_fixed_block_size;// = NULL;
//public $padded_block_size = 3000;
bool use_buffering = false;
Lexicon_file_manager lexicon_file_manager;// = NULL;
bool use_variable_length_disk_blocks = false;
Configuration configuration;// = NULL;
FILE *fp = NULL;
bool file_open = false;
//RandomAccessFile r = null;
*/


Invlists_file_manager::Invlists_file_manager() {
}

Invlists_file_manager::Invlists_file_manager(const Invlists_file_manager& orig) {
}

Invlists_file_manager::~Invlists_file_manager() {
}

Invlists_file_manager::Invlists_file_manager(string invlists_filename, string lexicon_filename,
        Lexicon_file_manager lexicon_file_manager, Configuration configuration) {
    this->lexicon_file_manager = lexicon_file_manager;
    this->lexicon_filename = lexicon_filename;
    this->invlists_filename = invlists_filename;
    this->configuration = configuration;
}

void Invlists_file_manager::initialise_invlists_file() {
    FILE *fp = fopen(invlists_filename.c_str(), "rw");
    // Fwrite is what to write,  number of bytes to write, byte size, pointer
    int a = 4;
    fwrite(&a, sizeof(int), 4, fp);
    fclose(fp);
}

int Invlists_file_manager::update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer) {

    num_occurances_in_invlist += incrementer;

    return num_occurances_in_invlist;
}


vector<int> Invlists_file_manager::extract_header_efficient(Data_block Data_block_class) {
    string header_bytes = Data_block_class.header_bytes;
    int length_of_header = 8;

    vector<int> header = vector<int>();

    if (header_bytes.length() < 8) {
        header[0] = 0;
        header[1] = 0;

    } else {
        //ByteBuffer bb = ByteBuffer.wrap(header_bytes, 0, 8);
        //bb.order(ByteOrder.LITTLE_ENDIAN);
        string header_text = header_bytes.substr(0, 4);
        cout << "header text " << header_text << endl;
        cout << "header text length: " << header_text.length() << endl;
        int value = 0;
        /**/
        /*
            for (int i = 0; i < 4; i++) {
                value |= header_text[i];
                //value <<= 8;
                value <<= 8;
                cout << +value << endl;
            }*/
for (int i = 0; i < 4; ++i) {
    value += header_text[i] << (24 - i * 8);    // |= could have also been used
    cout << +value << endl;
}

        //header[0] = value;
        header.push_back(value);
        
        cout << " header 0 " << value << endl; 

        string header_text2 = header_bytes.substr(4, 4);
        cout << "header text2 length: " << header_text.length() << endl;
        int value2 = 0;
        /*
            for (int i = 0; i < 4; i++) {
                value2 |= header_text2[i];
                //value2 <<= 8;
                value2 <<= 8;
                cout << +value2 << endl;
            }
         * */
        
for (int i = 0; i < 4; ++i) {
    value2 += header_text2[i] << (24 - i * 8);    // |= could have also been used
    cout << +value2 << endl;
}
        //header[1] = value2;
        header.push_back(value2);
        
        cout << " header 01 " << value << endl;
    }

    return header;
}

FILE* Invlists_file_manager::open_invlists_file() {
    FILE *fp = this->fp;
    if (fp == NULL) {
        fp = fopen(invlists_filename.c_str(), "rw");
        //file_open = true;
    }

    this->fp = fp;
    return fp;
}

void Invlists_file_manager::close_invlists_file() {

}

void Invlists_file_manager::close_invlists_file_real() {
    if (fp != NULL) {
        fclose(fp);
        fp = NULL;
        //file_open = false;
    }
}

Data_block Invlists_file_manager::get_header_and_invlist_bytes_from_data(Data_block data_block_class) {
    string data_bytes = data_block_class.data_bytes;

    if (data_bytes.length() >= 8) {

        string header_bytes(8, 0); // = new byte[8];

        header_bytes = data_bytes.substr(0, 8);

        //System.arraycopy(data_bytes, 0, header_bytes, 0, 8);

        data_block_class.header_bytes = header_bytes;

        int inv_list_length = data_bytes.length() - 8;
        if (inv_list_length > 0) {
            //byte[] inv_list_bytes = new byte[inv_list_length];

            //System.arraycopy(data_bytes, 8, inv_list_bytes, 0, inv_list_length);
            string inv_list_bytes;
            inv_list_bytes = data_bytes.substr(8);
            data_block_class.inverted_list_bytes = inv_list_bytes;

        }
    } else {
        cout << " can't split - data_bytes too short " << endl;
        cout << " can't split - data_bytes too short " << data_bytes.length() << endl;        
    }
    
    cout << data_block_class.header_bytes.length() << endl;
    cout << data_block_class.inverted_list_bytes.length() << endl;

    return data_block_class;
}

Term_statistics Invlists_file_manager::get_inverted_index_from_disk(Lexicon_item lexicon_item) {
    string term = lexicon_item.term;

    Term_statistics term_statistics(term); // = new Term_statistics(term);

    int file_offset_of_block = lexicon_item.file_offset;

    int content_size = lexicon_item.content_size;

    open_invlists_file();   

    Data_block data_block_class = get_block_from_disk(file_offset_of_block, content_size);      
    
    data_block_class = get_header_and_invlist_bytes_from_data(data_block_class);
    
    /* 
    cout << "data bytes length: " << data_block_class.data_bytes << endl;
    cout << "header_bytes length: " << data_block_class.header_bytes << endl;
    cout << "inverted_list_bytess length: " << data_block_class.inverted_list_bytes << endl;
    */

    vector<int> header = this -> extract_header_efficient(data_block_class);

    int num_unique_occurances = header[0];
    
    //cout << "num_unique_occurances: " << num_unique_occurances << endl;

    term_statistics.number_of_unique_documents_occurs_in = num_unique_occurances;

    int length_of_inv_list = header[1];
    
    //cout << "length_of_inv_list: " << length_of_inv_list << endl;

    map<int, int> occurances_per_document_array = this -> get_block_inverted_list_from_data(data_block_class, num_unique_occurances, length_of_inv_list);

    //cout << "occurances per document array size: " << occurances_per_document_array.size() << endl;
    term_statistics.occurances_per_document_array = occurances_per_document_array;

    close_invlists_file();

    return term_statistics;
}

map<int, int> Invlists_file_manager::get_block_inverted_list_from_data(Data_block data_block_class, int num_unique_occurances, int length_of_inv_list) {
    map<int, int> occurances_per_document_array = map<int, int>();// = new map<int, int>();

    string inverted_list_bytes = data_block_class.inverted_list_bytes;
    if (inverted_list_bytes.length() >= 8) {

        //string b = *inverted_list_bytes;

        //ByteBuffer bb = ByteBuffer.wrap(b);
        //bb.order(ByteOrder.LITTLE_ENDIAN);

        int array_elements = num_unique_occurances;

        for (int i = 0; i < array_elements * 4 * 2; i = i + 8) {
            //int key = bb.getInt();
            //int value = bb.getInt();
            string key_string = inverted_list_bytes.substr(i, 4);
       
            int key = 0;
for (int i = 0; i < 4; ++i) {
    key += key_string[i] << (24 - i * 8);    // |= could have also been used
    cout << +key << endl;
}
 
            /*
int key = int((unsigned char)(key_string[0]) << 24 |
            (unsigned char)(key_string[1]) << 16 |
            (unsigned char)(key_string[2]) << 8 |
            (unsigned char)(key_string[3]));
             * */

            string value_string = inverted_list_bytes.substr(i + 4, 4);
            /*
int value = int((unsigned char)(value_string[0]) << 24 |
            (unsigned char)(value_string[1]) << 16 |
            (unsigned char)(value_string[2]) << 8 |
            (unsigned char)(value_string[3]));*/

            int value = 0;
for (int i = 0; i < 4; ++i) {
    value += value_string[i] << (24 - i * 8);    // |= could have also been used
    cout << +value << endl;
    

}

/* 
    cout << "key is: " << key << endl;
    cout << "value is: " << value << endl;
 * */            

            occurances_per_document_array[key] = value;
        }
    }
    return occurances_per_document_array;
}

Data_block Invlists_file_manager::get_block_from_disk(int file_offset_of_block, int length_of_block) {

    fp = open_invlists_file();

    string b(length_of_block, 0); // = new byte[length_of_block];
 // size of bytes  = 1

    fseek(fp, file_offset_of_block, 1);
    int size_read = fread(&b[0], 1, length_of_block, fp);

    Data_block data_block_class = Data_block("no term");// = new Data_block();   
    
    //cout << "length of B: " << b.length() << endl
    
    data_block_class.data_bytes = b;
    
    close_invlists_file();

    return data_block_class;
}