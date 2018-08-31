/* 
 * File:   Invlists_manager.h
 * Author: z
 *
 * Created on March 30, 2017, 1:05 PM
 */

#ifndef INVLISTS_MANAGER_H
#define INVLISTS_MANAGER_H
#include "Invlists_block_memory_cache_simple_array.h"
#include "Lexicon_item.h"
#include "Configuration.h"
#include "Lexicon_file_manager.h"
#include "Invlists_file_manager.h"
#include "Invlists_block_memory_cache_simple_array.h"
#include "Term_statistics.h"
#include "Lexicon_item.h"
#include "Data_block.h"
#include <string>
#include <vector>
using namespace std;

class Invlists_manager {
public:
    Invlists_block_memory_cache_simple_array invlists_block_memory_cache;
    Invlists_manager();
    Invlists_manager(const Invlists_manager& orig);
    Invlists_manager(string invlists_filename, string lexicon_filename);
    Invlists_manager(string invlists_filename, string lexicon_filename,
            Lexicon_file_manager lexicon_file_manager, Configuration configuration);
    virtual ~Invlists_manager();
    int update_invlist_block_in_memory_cache_simple_array(Lexicon_item Lexicon_item, Term_statistics Term_statistics);
    void close_invlists_file();
    void close_invlists_file_real();
    FILE* open_invlists_file();
    void initialise_invlists_file();
    Term_statistics get_inverted_index_from_disk(Lexicon_item Lexicon_item);
    //map<string, void *> create_invlist_block_in_memory_cache_simple_array(string term, bool padding, int padded_fixed_block_size);
    //struct Ret_create_invlist_block;       

    struct Ret_create_invlist_block {
        int size_of_block;
        int content_size;
    };
    Ret_create_invlist_block create_invlist_block_in_memory_cache_simple_array(string term, bool padding, int padded_fixed_block_size);
private:
    string invlists_filename = "invlists";
    string lexicon_filename = "lexicon";
    int integer_length = 4; // 2 4 8
    string integer_keyword = "L"; // S = 2, L = 4, Q = 8
    int maximum_fixed_block_size = 0;
    //public $padded_block_size = 3000;
    bool use_buffering = false;
    Lexicon_file_manager lexicon_file_manager; // = NULL;
    bool use_variable_length_disk_blocks = false;
    //Invlists_block_memory_cache_simple_array invlists_block_memory_cache;// = NULL;
    Configuration configuration; // = NULL;
    //RandomAccessFile r = null;
    Invlists_file_manager invlists_file_manager; // = NULL;

    Data_block write_header_efficient(Data_block data_block_class, vector<int> header);
    vector<int> extract_header_efficient(Data_block data_block_class);
    int update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer);

    struct Ret_create_blank_data_block {
        int size_of_block = 0;
        string blank_data_block = "";
    };

    struct Ret_updated_inverted_index {
        int write_length = 0;
        int new_length_of_inv_list = 0;
        Data_block data_block_class = Data_block();
        string inverted_list_bytes = "UNDEFINED_UPDATED_INVERTED_INDEX_INVERTED_LIST_BYTES";
    };

    Ret_create_blank_data_block create_blank_data_block_struct(bool pad_block, int padded_block_size);
    Ret_updated_inverted_index update_inverted_index_occurances_per_document_array_as_append(Data_block data_block_class, int length_of_inv_list, map<int, int> occurances_per_document_array);


};

#endif /* INVLISTS_MANAGER_H */

