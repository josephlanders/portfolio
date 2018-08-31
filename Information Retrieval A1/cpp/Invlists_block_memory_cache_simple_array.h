/* 
 * File:   Invlists_block_memory_cache_simple_array.h
 * Author: z
 *
 * Created on April 4, 2017, 2:11 PM
 */

#ifndef INVLISTS_BLOCK_MEMORY_CACHE_SIMPLE_ARRAY_H
#define INVLISTS_BLOCK_MEMORY_CACHE_SIMPLE_ARRAY_H

#include "Data_block.h"
#include <string>
#include <map>
#include "Configuration.h"
using namespace std;
class Invlists_block_memory_cache_simple_array {
public:
    Invlists_block_memory_cache_simple_array();
    Invlists_block_memory_cache_simple_array(const Invlists_block_memory_cache_simple_array& orig);
    virtual ~Invlists_block_memory_cache_simple_array();
    Invlists_block_memory_cache_simple_array(Configuration configuration);
    //Data_block allocate_new_cache_entry(string term, int disk_block_size, int memory_block_size);
    Data_block allocate_new_cache_entry(string term, Data_block data_block);
    void update_cache(string term, Data_block data_block_class);
    Data_block get_data_block_from_cache(string term);
    map<string, Data_block> get_data_blocks_array();
private:
    map<string, Data_block> data_blocks_array;// = NULL;
    int cache_size_in_use = 0;
    int block_cache_memory_buffer_size = 0; // 9 MB memory buffer
    Configuration configuration;// = NULL;
    bool use_memory_buffer_cache_eviction = false;


};

#endif /* INVLISTS_BLOCK_MEMORY_CACHE_SIMPLE_ARRAY_H */

