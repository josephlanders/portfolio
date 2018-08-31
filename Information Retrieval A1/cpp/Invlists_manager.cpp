/* 
package code;

import code.Configuration;
import java.util.*;
import java.io.*;
import java.nio.ByteBuffer;
import java.nio.*;

 */

#include "Invlists_manager.h"
#include <string>
#include <vector>
#include <map>
#include "Data_block.h"
#include "Invlists_file_manager.h"
#include "Configuration.h"
#include <iostream>
using namespace std;
/*
string invlists_filename = "invlists";
string lexicon_filename = "lexicon";
int integer_length = 4; // 2 4 8
string integer_keyword = "L"; // S = 2, L = 4, Q = 8
int maximum_fixed_block_size = 0;
//public $padded_block_size = 3000;
bool use_buffering = false;
Lexicon_file_manager lexicon_file_manager;// = NULL;
bool use_variable_length_disk_blocks = false;
Invlists_block_memory_cache_simple_array invlists_block_memory_cache;// = NULL;
Configuration configuration;// = NULL;
//RandomAccessFile r = null;
Invlists_file_manager invlists_file_manager;// = NULL;
*/
Invlists_manager::Invlists_manager() {
}

Invlists_manager::Invlists_manager(const Invlists_manager& orig) {
}

Invlists_manager::~Invlists_manager() {
}

Invlists_manager::Invlists_manager(string invlists_filename, string lexicon_filename,
        Lexicon_file_manager lexicon_file_manager, Configuration configuration) {

    this->lexicon_file_manager = lexicon_file_manager;
    this->lexicon_filename = lexicon_filename;
    this->invlists_filename = invlists_filename;
    this->configuration = configuration;

    Invlists_block_memory_cache_simple_array invlists_block_memory_cache(configuration); // = new Invlists_block_memory_cache_simple_array(configuration);
//    invlists_block_memory_cache = new Invlists_block_memory_cache_simple_array(configuration);
    Invlists_file_manager invlists_file_manager(invlists_filename, lexicon_filename,
            lexicon_file_manager, configuration);
}

void Invlists_manager::initialise_invlists_file() {
    invlists_file_manager.initialise_invlists_file();
}


Invlists_manager::Ret_create_blank_data_block Invlists_manager::create_blank_data_block_struct(bool pad_block, int padded_block_size) {
    int num_occurances_in_all_docs = 0; // 32 bit pack?
    int length_of_inverted_list = 0; // 32 bit pack?

    string padding = "";
    if (pad_block == true) {
        // Ints are always 32 bits in Java
        //int len = padded_block_size - (num_occurances_in_all_docs + length_of_inverted_list);
        // TODO: Padding in java?
        int len = padded_block_size - 4 - 4;
        //padding = str_pad(string, len, chr(0));
    }

    // Assume Binary Safe
    //String blank_Data_block = num_occurances_in_all_docs + length_of_inverted_list + padding;
    //string blank_data_block = "";
    string blank_data_block(8, 0);
    //byte[] blank_Data_block = new byte[8];

    //ByteBuffer bb = ByteBuffer.wrap(blank_Data_block);
    //bb.order(ByteOrder.LITTLE_ENDIAN);
    //bb.putInt(num_occurances_in_all_docs);
    //bb.putInt(length_of_inverted_list);

//    blank_data_block += num_occurances_in_all_docs;
//    blank_data_block += length_of_inverted_list;

    int size_of_block = blank_data_block.length();

    //map<string, void *> ret = new map<string, void *>();

    //ret.insert("size_of_block", size_of_block);
    //ret.insert("blank_data_block", blank_data_block);

    //return ret;

    Ret_create_blank_data_block ret; // = new v;
    ret.size_of_block = size_of_block;
    ret.blank_data_block = blank_data_block;

    return ret;
}

int Invlists_manager::update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer) {

    num_occurances_in_invlist += incrementer;

    return num_occurances_in_invlist;
}

/*
struct Ret_create_invlist_block
{
    int size_of_block = 0;
    int content_size =0;
}; */
//struct Ret_create_invlist_block;
//extern Ret_create_invlist_block ret_create_invlist_block

struct Ret_create_blank_data_block
{
    int size_of_block = 0;
    string blank_data_block = "";
};

Invlists_manager::Ret_create_invlist_block Invlists_manager::create_invlist_block_in_memory_cache_simple_array(string term, bool padding, int padded_fixed_block_size) {

    Ret_create_blank_data_block ret = create_blank_data_block_struct(padding, padded_fixed_block_size);
    int size_of_block = (int) ret.size_of_block;
    string blank_data_block = (string) ret.blank_data_block;

    //Data_block data_block_class = invlists_block_memory_cache.allocate_new_cache_entry(term, size_of_block, size_of_block);
    int disk_block_size = size_of_block;
    int memory_block_size = size_of_block;
    Data_block data_block_class = Data_block(term, 0, disk_block_size, memory_block_size, 0);
    data_block_class = invlists_block_memory_cache.allocate_new_cache_entry(term, data_block_class);
    //int disk_block_size = size_of_block;

    //Data_block_class -> header_bytes = blank_Data_block;
    // Block is already packed
    data_block_class.header_bytes = blank_data_block;
    data_block_class.disk_block_size = disk_block_size;
    data_block_class.content_size = disk_block_size;

    invlists_block_memory_cache.update_cache(term, data_block_class);

    int content_size = size_of_block;
    if (use_variable_length_disk_blocks == true) {
        data_block_class.content_size = content_size;
        data_block_class.disk_block_size = content_size;
        data_block_class.memory_block_size = content_size;
    } else {
        data_block_class.content_size = content_size;
        //$data_block_class -> disk_block_size  = ; Remains unchanged since fixed length blocks
        //$data_block_class -> memory_block_size = $content_size; ; TODO?
    }

    Ret_create_invlist_block ret2; // = new map<string, void *>();
    ret2.size_of_block = size_of_block;
    ret2.content_size = content_size;
    
    return ret2;
}

vector<int> Invlists_manager::extract_header_efficient(Data_block Data_block_class) {
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
        int value = 0;
        /*
            for (int i = 0; i < 3; ++i) {
                value |= header_text[i];
                value <<= 8;
            }*/
        
for (int i = 0; i < 4; ++i) {
    value += header_text[i] << (24 - i * 8);    // |= could have also been used
    cout << +value << endl;
}

        
        //header[0] = value;
        header.push_back(value);

        string header_text2 = header_bytes.substr(4, 4);
        int value2 = 0;
        /*
            for (int i = 0; i < 3; ++i) {
                value2 |= header_text2[i];
                value2 <<= 8;
            }
         * */
        
for (int i = 0; i < 4; ++i) {
    value2 += header_text2[i] << (24 - i * 8);    // |= could have also been used
    cout << +value2 << endl;
}
        //header[1] = value2;
        header.push_back(value2);
    }
    
    return header;
}

Data_block Invlists_manager::write_header_efficient(Data_block data_block_class, vector<int> header) {

    string header_bytes = data_block_class.header_bytes;

    string new_header_bytes(8, 255); // = new byte[8];
    //ByteBuffer bb = ByteBuffer.wrap(new_header_bytes);
    //bb.order( ByteOrder.LITTLE_ENDIAN);
    //bb.putInt(header[0]);
    //bb.putInt(header[1]);
    
    int somelong = header[0];
    
new_header_bytes[0] = (somelong >> 24) & 0xFF;
new_header_bytes[1] = (somelong >> 16) & 0xFF;
new_header_bytes[2] = (somelong >> 8) & 0xFF;
new_header_bytes[3] = somelong & 0xFF;

    //new_header_bytes += header[0];

    int somelong2 = header[1];
    
new_header_bytes[4] = (somelong2 >> 24) & 0xFF;
new_header_bytes[5] = (somelong2 >> 16) & 0xFF;
new_header_bytes[6] = (somelong2 >> 8) & 0xFF;
new_header_bytes[7] = somelong2 & 0xFF;

    //new_header_bytes += header[1];
    
    if (header_bytes.length() < 8) {
        //new_header_bytes = new byte[8];
        //System.out.println("this condition should never be reached - header broken?");
        cout << "this condition should never be reached - header broken?" << endl;
        //System.exit(0);
    } else {

    }

    //Data_block_class.header_bytes = new_header_bytes;
    data_block_class.header_bytes = new_header_bytes;
    
    //cout << data_block_class.header_bytes.length() << endl;

    return data_block_class;
}

int Invlists_manager::update_invlist_block_in_memory_cache_simple_array(Lexicon_item lexicon_item, Term_statistics term_statistics) {

    int content_size = 0;
    string term = lexicon_item.term;
    
    Data_block data_block_class = invlists_block_memory_cache.get_data_block_from_cache(term);
    
    if (data_block_class.null == false)
    {
    
    int incrementer = term_statistics.number_of_unique_documents_occurs_in;
    vector<int> header = this -> extract_header_efficient(data_block_class);
    
    
    int num_unique_occurances = header[0];
    int num_unique_occurances_updated = num_unique_occurances + incrementer;

    header[0] = num_unique_occurances_updated;

    int length_of_inv_list = header[1];

    map<int, int> occurances_per_document_array = term_statistics.occurances_per_document_array;
    Ret_updated_inverted_index ret = this -> update_inverted_index_occurances_per_document_array_as_append(data_block_class, length_of_inv_list, occurances_per_document_array);

    //TODO insert Length of inv list into class
    int write_length_occurances_per_document_appended_array = ret.write_length;
    int new_length_of_inv_list = (int) ret.new_length_of_inv_list;
    data_block_class = (Data_block) ret.data_block_class;
    string inverted_list_bytes = (string) ret.inverted_list_bytes;
    
    if (data_block_class.null == true)
    {
        cout << "data block is null" << endl;
    }

    header[1] = new_length_of_inv_list;
    
    data_block_class = this -> write_header_efficient(data_block_class, header);

    //int content_size = 4 + 4 + length_of_inv_list;
    content_size = 4 + 4 + new_length_of_inv_list;

    if (use_variable_length_disk_blocks == true) {
        data_block_class.content_size = content_size;
        data_block_class.disk_block_size = content_size;
        data_block_class.memory_block_size = content_size;
    } else {
        data_block_class.content_size = content_size;
        data_block_class.memory_block_size = content_size;
    }

    invlists_block_memory_cache.update_cache(lexicon_item.term, data_block_class);
    } else {
        cout << "can't update, no data block in cache with that term: " << term << endl;
        cout << "data_block_class term is: " << data_block_class.term << endl;
    }
    
    return content_size;
}


Invlists_manager::Ret_updated_inverted_index Invlists_manager::update_inverted_index_occurances_per_document_array_as_append(Data_block data_block_class, int length_of_inv_list, map<int, int> occurances_per_document_array) {
    string inverted_list_bytes = data_block_class.inverted_list_bytes;

    string additional_inverted_list_bytes;
    // 8 bytes 
    // Jump to the end of the inverted list where we can safely append
    //int end_of_inv_list = 0 + 4 + 4 + length_of_inv_list;
    int end_of_inv_list = length_of_inv_list;

    int expected_size_in_bytes = occurances_per_document_array.size() * 2 * 4;
    //byte[] b = new byte[expected_size_in_bytes];
    //ByteBuffer bb = ByteBuffer.wrap(b);
    //bb.order(ByteOrder.LITTLE_ENDIAN);
    

    int id = 0;
    int value = 0;
    //Iterator < Map.Entry<Integer, Integer>> it = occurances_per_document_array.entrySet().iterator();
    map<int, int>::iterator it = occurances_per_document_array.begin();
    //while (it.hasNext()) {
    while (it != occurances_per_document_array.end()) {


        //Map.Entry<Integer, Integer> pair = it.next();
        //id = pair.getKey();
        //value = pair.getValue();

        id = it -> first;
        value = it -> second;
        
        string stringid(4, 0);
        string stringvalue(4, 0);
           
stringid[0] = (id >> 24) & 0xFF;
stringid[1] = (id >> 16) & 0xFF;
stringid[2] = (id >> 8) & 0xFF;
stringid[3] = id & 0xFF;

stringvalue[0] = (value >> 24) & 0xFF;
stringvalue[1] = (value >> 16) & 0xFF;
stringvalue[2] = (value >> 8) & 0xFF;
stringvalue[3] = value & 0xFF;

        additional_inverted_list_bytes += stringid;
        additional_inverted_list_bytes += stringvalue;

        //bb.putInt(id);
        //bb.putInt(value);
        it++;
    }

    
    int write_length = expected_size_in_bytes;
    
    string combined = inverted_list_bytes + additional_inverted_list_bytes;
    int new_length_of_inv_list = combined.length();
    
    
    //System.arraycopy(inverted_list_bytes, 0, combined, 0, inverted_list_bytes.length);
    //System.arraycopy(b, 0, combined, inverted_list_bytes.length, b.length);

    //Data_block_class.inverted_list_bytes = null;
    data_block_class.inverted_list_bytes = combined;
    
    Ret_updated_inverted_index ret; // = new v;
    ret.write_length = write_length;
    ret.new_length_of_inv_list = new_length_of_inv_list;
    ret.data_block_class = data_block_class;
    ret.inverted_list_bytes = inverted_list_bytes;
    return ret;
}

FILE* Invlists_manager::open_invlists_file() {
    return invlists_file_manager.open_invlists_file();
}

void Invlists_manager::close_invlists_file() {
    invlists_file_manager.close_invlists_file();
}

void Invlists_manager::close_invlists_file_real() {
    invlists_file_manager.close_invlists_file_real();
}

Term_statistics Invlists_manager::get_inverted_index_from_disk(Lexicon_item lexicon_item) {
    Term_statistics term_statistics = invlists_file_manager.get_inverted_index_from_disk(lexicon_item);

    return term_statistics;
}
