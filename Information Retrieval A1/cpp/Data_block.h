/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Data_block.h
 * Author: z
 *
 * Created on March 22, 2017, 10:29 PM
 */

#ifndef DATA_BLOCK_H
#define DATA_BLOCK_H
#include <string>
#include <map>
#include <vector>
using namespace std;
class Data_block {
public:
    int access_frequency = 0;
    int file_offset = 0;
    string term = "DATA_BLOCK_UNINITIALISED";
    int disk_block_size = 0;
    int memory_block_size = 0;
    int content_size = 0;
    bool null = true;
    string data = "";
    string data_bytes = "";// = new byte[0];        ;
    //string header_bytes;// = new byte[8];
    string header_bytes = "DATA_BLOCK_HEADER_BYTES_UNINITIALISED";
    string inverted_list_bytes = "DATA_BLOCK_INVERTED_LIST_BYTES_UNINITIALISED";// = new byte[0];
    
    Data_block();
    Data_block(string term);
    Data_block(const Data_block& orig);
    virtual ~Data_block();
    string get_block_data();
    Data_block(string term,
            int file_offset,
            int disk_block_size,
            int memory_block_size,
            int content_size);
    /*
    void Data_block(string term,
            int file_offset,
            int disk_block_size,
            int memory_block_size,
            int content_size);
*/
    string toString();
private:
   vector<int> extract_header_efficient(Data_block data_block_class);
   map<int, int> get_block_inverted_list_from_data(Data_block data_block_class, int num_unique_occurances, int length_of_inv_list);
};

#endif /* DATA_BLOCK_H */

