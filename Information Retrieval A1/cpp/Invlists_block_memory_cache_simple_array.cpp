/* 
 * File:   Invlists_block_memory_cache_simple_array.cpp
 * Author: z
 * 
 * Created on April 4, 2017, 2:11 PM
 */

#include "Invlists_block_memory_cache_simple_array.h"
#include "Data_block.h"
#include "Configuration.h"
#include <iterator>
#include <map>
#include <string>
#include <iostream>
/*package code;

import java.util.HashMap;
*/

using namespace std;
/*
    map<string, Data_block> data_blocks_array;// = NULL;
    int cache_size_in_use = 0;
    int block_cache_memory_buffer_size = 0; // 9 MB memory buffer
    Configuration configuration;// = NULL;
    bool use_memory_buffer_cache_eviction = false;
 * */

Invlists_block_memory_cache_simple_array::Invlists_block_memory_cache_simple_array() {
}

Invlists_block_memory_cache_simple_array::Invlists_block_memory_cache_simple_array(const Invlists_block_memory_cache_simple_array& orig) {
}

Invlists_block_memory_cache_simple_array::~Invlists_block_memory_cache_simple_array() {
}

    Invlists_block_memory_cache_simple_array::Invlists_block_memory_cache_simple_array(Configuration configuration) {
        this -> configuration = configuration;
        //data_blocks_array = new map<string, Data_block>();
    }

    Data_block Invlists_block_memory_cache_simple_array::allocate_new_cache_entry(string term, Data_block data_block) {
        
        map<string, Data_block>::iterator it;
        
        it = data_blocks_array.find(term);
        
        if (it == data_blocks_array.end())
        {
            //data_block_class = Data_block(term, 0, disk_block_size, memory_block_size, 0);
            data_blocks_array[term] = data_block;
        } else {
            cout << "Can't allocate new cache entry because entry already exists for this term " << endl;
        }
        
        
        return data_block;
    }

/*
    Data_block Invlists_block_memory_cache_simple_array::allocate_new_cache_entry(string term, int disk_block_size, int memory_block_size) {
        Data_block data_block_class;// = NULL;
        
        map<string, Data_block>::iterator it;
        
        it = data_blocks_array.find(term);
        
        if (it == data_blocks_array.end())
        {
            //data_block_class = Data_block(term, 0, disk_block_size, memory_block_size, 0);
            data_blocks_array[term] = data_block_class;
        } else {
            cout << "Can't allocate new cache entry because entry already exists for this term " << endl;
        }
        
        cout << "e" << endl;
        
        return data_block_class;
    }
 * */    

    void Invlists_block_memory_cache_simple_array::update_cache(string term, Data_block data_block_class) {
        if (data_block_class.null == false)
        {
        data_blocks_array[term] = data_block_class;       
        } else {
            cout << "can't update data block cache with a null block" << endl;
        }
    }

    Data_block Invlists_block_memory_cache_simple_array::get_data_block_from_cache(string term) {
        Data_block data_block_class;// = NULL;
        
        map<string, Data_block>::iterator it;
        
        it = data_blocks_array.find(term);
        if (it != data_blocks_array.end())
        {
            // This is just an assignment
            data_block_class = it -> second;
            // This is a copy constructor
            //data_block_class = Data_block(it -> second);
        }
        
        if (data_block_class.null == true)
        {
            cout << "data block class in cache is null: " << data_block_class.null << endl;
        } 
        
        return data_block_class;
    }

    map<string, Data_block> Invlists_block_memory_cache_simple_array::get_data_blocks_array() {
        return data_blocks_array;
    }