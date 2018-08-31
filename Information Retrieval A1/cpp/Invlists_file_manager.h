/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Invlists_file_manager.h
 * Author: z
 *
 * Created on March 22, 2017, 10:28 PM
 */

#ifndef INVLISTS_FILE_MANAGER_H
#define INVLISTS_FILE_MANAGER_H
#include "Lexicon_item.h"
#include "Term_statistics.h"
#include "data_block.h"
#include "Lexicon_file_manager.h"
#include "Configuration.h"
#include <vector>
using namespace std;
class Invlists_file_manager {
public:
    Invlists_file_manager();
    Invlists_file_manager(const Invlists_file_manager& orig);
    Invlists_file_manager(string invlists_filename, string lexicon_filename,
        Lexicon_file_manager lexicon_file_manager, Configuration configuration);
    virtual ~Invlists_file_manager();
    void initialise_invlists_file();
    Term_statistics get_inverted_index_from_disk(Lexicon_item Lexicon_item);
    FILE* open_invlists_file();
    void close_invlists_file();
    void close_invlists_file_real();
private:

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
//RandomAccessFile r = null;

    Data_block get_header_and_invlist_bytes_from_data(Data_block data_block_class);
int update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer);
vector<int> extract_header_efficient(Data_block data_block_class);
map<int, int> get_block_inverted_list_from_data(Data_block data_block_class, int num_unique_occurances, int length_of_inv_list);
Data_block get_block_from_disk(int file_offset_of_block, int length_of_block);
};

#endif /* INVLISTS_FILE_MANAGER_H */

