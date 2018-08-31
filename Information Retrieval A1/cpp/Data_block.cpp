/*
 * File:   Data_block.cpp
 * Author: z
 * 
 * Created on March 22, 2017, 10:29 PM
 */

#include "Data_block.h"
#include <map>
#include <string>
#include <vector>
#include <iostream>
using namespace std;

/*
    int access_frequency = 0;
    int file_offset = NULL;
    string term = "";
    int disk_block_size = 0;
    int memory_block_size = 0;
    int content_size = 0;
    string data = "";
    string data_bytes;// = new byte[0];        ;
    string header_bytes;// = new byte[8];
    string inverted_list_bytes;// = new byte[0];
 */

Data_block::Data_block() {
    this->header_bytes = "DATA_BLOCK_HEADER_BYTES_CONSTRUCTOR_A";
    this->inverted_list_bytes = "DATA_BLOCK_INVERTED_LIST_BYTES_CONSTRUCTOR_A";
    //this->null = false;
  //  cout << "A DEFAULT CONSTRUCTOR" << endl;
}

Data_block::Data_block(string term) {
    this->term = term;
    this->header_bytes = string(8, 0);
    this->inverted_list_bytes = "";
    this->null = false;
//    cout << "B SMALL CONSTRUCTOR" << endl;
}

Data_block::Data_block(const Data_block& orig) {
    
    this->term = orig.term;
    this->file_offset = orig.file_offset;
    this->disk_block_size = orig.disk_block_size;
    this->memory_block_size = orig.memory_block_size;
    this->content_size = orig.content_size;    
    this->header_bytes = orig.header_bytes;
    this->inverted_list_bytes = orig.inverted_list_bytes;
    this->data_bytes = orig.data_bytes;
    this->null = orig.null;
//cout << "C COPY CONSTRUCTOR" << endl;
}

Data_block::~Data_block() {
}

Data_block::Data_block(string term,
        int file_offset,
        int disk_block_size,
        int memory_block_size,
        int content_size) {
    this->term = term;
    this->file_offset = file_offset;
    this->disk_block_size = disk_block_size;
    this->memory_block_size = memory_block_size;
    this->content_size = content_size;
    
    this->header_bytes = string(8, 0);
    this->inverted_list_bytes = "";
    this->null = false;
    
    //cout << "D LONG CONSTRUCTOR" << endl;
}

string Data_block::toString() {
    string str = "";
    str = "\nterm: " + term;
    str += "\n file_offset: " + file_offset;
    str += "\n disk_block_size: " + disk_block_size;
    str += "\n memory_block_size: " + memory_block_size;
    str += "\n content_size: " + content_size;
    str += "\n data size: " + data.length();

    vector<int> header = this -> extract_header_efficient(*this);
    str += "\n header num unique occurances: " + header[0];
    str += "\n header inv_list_size: " + header[1];

    int num_unique_occurances = header[0];
    int length_of_inv_list = header[1];

    map<int, int> occurances_per_document_array = this -> get_block_inverted_list_from_data(*this, num_unique_occurances, length_of_inv_list);

    str += "\n occurances per document array size: " + occurances_per_document_array.size();
    str += "\n occurances per document array contents: ";

    map<int, int>::iterator it;
    it = occurances_per_document_array.begin();
    while (it != occurances_per_document_array.end()) {
        int id = it -> first;
        int value = it -> second;

        str += "\n id: " + id;
        str += " occurances: " + value;
    }
    
    str += "\n header bytes size " + this->header_bytes.size();
    str += "\n inverted list bytes size " + this->inverted_list_bytes.size();

    return str;
}

string Data_block::get_block_data() {
    string combined = ""; // = new string[header_bytes.length + inverted_list_bytes.length];

    combined += header_bytes;
    combined += inverted_list_bytes;
    return combined;
}

vector<int> Data_block::extract_header_efficient(Data_block data_block_class) {
    string header_bytes = data_block_class.header_bytes;
    int length_of_header = 8;

    vector<int> header = vector<int>();

    if (header_bytes.length() < 8) {
        header[0] = 0;
        header[1] = 0;

    } else {
        string header_text = header_bytes.substr(0, 4);
        int value = 0;
            for (int i = 0; i < 3; ++i) {
                value |= header_text[i];
                value <<= 8;
            }
        
        //header[0] = value;
        header.push_back(value);

        string header_text2 = header_bytes.substr(4, 4);
        int value2 = 0;
            for (int i = 0; i < 3; ++i) {
                value2 |= header_text2[i];
                value2 <<= 8;
            }
        
        //header[1] = value2;
        header.push_back(value2);
    }

    return header;
}

map<int, int> Data_block::get_block_inverted_list_from_data(Data_block data_block_class, int num_unique_occurances, int length_of_inv_list) {    
    if (data_block_class.null == true)
    {
        //cout << "uninitialised data block" << endl;
    } else {
        //cout << "data block is initialised " << endl;
    }
    map<int, int> occurances_per_document_array; // = new HashMap<Integer, Integer>();
    string inverted_list_bytes = data_block_class.inverted_list_bytes;
    if (inverted_list_bytes.length() >= 8) {

        string b = inverted_list_bytes;

        int array_elements = num_unique_occurances;

        for (int i = 0; i < array_elements * 4; i = i + 4) {
            string key_text = b.substr(i, 4);
            //int header_offset = 0;
            int key = 0;
            for (int i = 0; i < 3; i++) {
                key |= key_text[i];
                key <<= 8;
            }

            string value_text = b.substr(i + 4, 4);
            int value = 0;
            for (int i = 0; i < 3; i++) {
                value |= value_text[i];
                value <<= 8;
            }
            //occurances_per_document_array.put(key, value);
            occurances_per_document_array[key] = value;
        }
    } else {
        //System.out.println("Inverted list length too small for term: " + Data_block_class.term);
        cout << "Inverted list bytes length too small for term: " + data_block_class.term << endl;

    }
    return occurances_per_document_array;
}
