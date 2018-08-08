package code;

import code.configuration;
import java.util.*;
import java.io.*;
import java.nio.ByteBuffer;
import java.nio.*;

public class invlists_manager {

    public invlists_manager(String invlists_filename, String lexicon_filename,
            lexicon_file_manager lexicon_file_manager, configuration configuration) {

        this.lexicon_file_manager = lexicon_file_manager;
        this.lexicon_filename = lexicon_filename;
        this.invlists_filename = invlists_filename;
        this.configuration = configuration;
        invlists_block_memory_cache = new invlists_block_memory_cache_simple_array(configuration);
        invlists_file_manager = new invlists_file_manager(invlists_filename, lexicon_filename,
                lexicon_file_manager, configuration);
    }

    public String invlists_filename = "invlists";
    public String lexicon_filename = "lexicon";
    public int integer_length = 4; // 2 4 8
    public String integer_keyword = "L"; // S = 2, L = 4, Q = 8
    public Integer maximum_fixed_block_size = null;
    //public $padded_block_size = 3000;
    public boolean use_buffering = false;
    public lexicon_file_manager lexicon_file_manager = null;
    public boolean use_variable_length_disk_blocks = false;
    public invlists_block_memory_cache_simple_array invlists_block_memory_cache = null;
    private configuration configuration = null;
    private RandomAccessFile r = null;
    public invlists_file_manager invlists_file_manager = null;

    public void initialise_invlists_file() {
        invlists_file_manager.initialise_invlists_file();
    }

    public HashMap<String, Object> create_blank_data_block_struct(boolean pad_block, int padded_block_size) {
        int num_occurances_in_all_docs = 0; // 32 bit pack?
        int length_of_inverted_list = 0; // 32 bit pack?

        String padding = "";
        if (pad_block == true) {
            // Ints are always 32 bits in Java
            //int len = padded_block_size - (num_occurances_in_all_docs + length_of_inverted_list);
            // TODO: Padding in java?
            int len = padded_block_size - 4 - 4;            
            //padding = str_pad(string, len, chr(0));
        }

        // Assume Binary Safe
        //String blank_data_block = num_occurances_in_all_docs + length_of_inverted_list + padding;
        byte[] blank_data_block = new byte[8];
        
        ByteBuffer bb = ByteBuffer.wrap(blank_data_block);
        bb.order(ByteOrder.LITTLE_ENDIAN);
        bb.putInt(num_occurances_in_all_docs);
        bb.putInt(length_of_inverted_list);
        

        int size_of_block = blank_data_block.length;

        HashMap<String, Object> ret = new HashMap<String, Object>();

        ret.put("size_of_block", size_of_block);
        ret.put("blank_data_block", blank_data_block);

        return ret;
    }   

    public int update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer) {

        num_occurances_in_invlist += incrementer;

        return num_occurances_in_invlist;
    }

    public HashMap<String, Object> create_invlist_block_in_memory_cache_simple_array(String term, boolean padding, int padded_fixed_block_size) {

        HashMap<String, Object> ret = create_blank_data_block_struct(padding, padded_fixed_block_size);
        int size_of_block = (int) ret.get("size_of_block");
        byte[] blank_data_block = (byte[]) ret.get("blank_data_block");
        
        data_block data_block_class = invlists_block_memory_cache.allocate_new_cache_entry(term, size_of_block, size_of_block);

        int disk_block_size = size_of_block;

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

        HashMap<String, Object> ret2 = new HashMap<String, Object>();
        ret2.put("size_of_block", size_of_block);
        ret2.put("content_size", content_size);

        return ret2;
    }

    
    public int[] extract_header_efficient(data_block data_block_class) {
        byte[] header_bytes = data_block_class.header_bytes;
        int length_of_header = integer_length * 2;

        int[] header = new int[2];
        
        if (header_bytes.length < 8) {
            header[0] = 0;
            header[1] = 0;

        } else {            
            
            ByteBuffer bb = ByteBuffer.wrap(header_bytes, 0, 8);
            bb.order(ByteOrder.LITTLE_ENDIAN);
            header[0] = bb.getInt();
            header[1] = bb.getInt();
        
        }
       
        return header;
    }
        
    public data_block write_header_efficient(data_block data_block_class, int[] header) {
                
        byte[] header_bytes = data_block_class.header_bytes;
        
        byte[] new_header_bytes = new byte[8];
        ByteBuffer bb = ByteBuffer.wrap(new_header_bytes);
        bb.order( ByteOrder.LITTLE_ENDIAN);
        bb.putInt(header[0]);
        bb.putInt(header[1]);

        if (header_bytes.length < 8) {
            new_header_bytes = new byte[8];
            System.out.println("this condition should never be reached - header broken?");
            System.exit(0);
        } else {

        }

        data_block_class.header_bytes = new_header_bytes;

        return data_block_class;
    }
    
    public int update_invlist_block_in_memory_cache_simple_array(lexicon_item lexicon_item, term_statistics term_statistics) {

        String term = lexicon_item.term;
        data_block data_block_class = invlists_block_memory_cache.get_data_block_from_cache(term);

        int incrementer = term_statistics.number_of_unique_documents_occurs_in;
        int[] header =extract_header_efficient(data_block_class);

        int num_unique_occurances = header[0];
        int num_unique_occurances_updated = num_unique_occurances + incrementer;
              
        header[0] = num_unique_occurances_updated;

        int length_of_inv_list = header[1];

        HashMap<Integer, Integer> occurances_per_document_array = term_statistics.occurances_per_document_array;
        Object[] ret = update_inverted_index_occurances_per_document_array_as_append(data_block_class, length_of_inv_list, occurances_per_document_array);

        //TODO insert Length of inv list into class
        int write_length_occurances_per_document_appended_array = (int) ret[0];
        int new_length_of_inv_list = (int) ret[1];
        data_block_class = (data_block) ret[2];
        
        header[1] = new_length_of_inv_list;

        data_block_class = write_header_efficient(data_block_class, header);
        
        //int content_size = 4 + 4 + length_of_inv_list;
        int content_size = 4 + 4 + new_length_of_inv_list;

        if (use_variable_length_disk_blocks == true) {
            data_block_class.content_size = content_size;
            data_block_class.disk_block_size = content_size;
            data_block_class.memory_block_size = content_size;
        } else {
            data_block_class.content_size = content_size;
            data_block_class.memory_block_size = content_size;
        }

        //if (data_block_class.term == "headline") {
        //}

        invlists_block_memory_cache.update_cache(lexicon_item.term, data_block_class);

        return content_size;
    }
    
    public Object[] update_inverted_index_occurances_per_document_array_as_append(data_block data_block_class, int length_of_inv_list, HashMap<Integer, Integer> occurances_per_document_array) {
        byte[] inverted_list_bytes = data_block_class.inverted_list_bytes;
        
        // 8 bytes 
        // Jump to the end of the inverted list where we can safely append
        //int end_of_inv_list = 0 + 4 + 4 + length_of_inv_list;
        int end_of_inv_list = length_of_inv_list;

        int expected_size_in_bytes = occurances_per_document_array.size() * 2 * 4;
        byte[] b = new byte[expected_size_in_bytes];
        ByteBuffer bb = ByteBuffer.wrap(b);
        bb.order( ByteOrder.LITTLE_ENDIAN);

        int id = 0;
        int value = 0;
        Iterator<Map.Entry<Integer, Integer>> it = occurances_per_document_array.entrySet().iterator();
        while (it.hasNext()) {
            Map.Entry<Integer, Integer> pair = it.next();
            id = pair.getKey();
            value = pair.getValue();

            bb.putInt(id);
            bb.putInt(value);   
        }
               
        int write_length = expected_size_in_bytes;
        
        byte[] combined = new byte[inverted_list_bytes.length + b.length];
        int new_length_of_inv_list = combined.length;
        
        System.arraycopy(inverted_list_bytes, 0, combined, 0, inverted_list_bytes.length);
        System.arraycopy(b, 0, combined, inverted_list_bytes.length, b.length);

        //data_block_class.inverted_list_bytes = null;
        data_block_class.inverted_list_bytes = combined;

        Object[] ret = new Object[3];
        ret[0] = write_length;
        ret[1] = new_length_of_inv_list;
        ret[2] = data_block_class;

        return ret;
    }
    
    public RandomAccessFile open_invlists_file() {
        return invlists_file_manager.open_invlists_file();
    }

    public void close_invlists_file() {
        invlists_file_manager.close_invlists_file();
    }

    public void close_invlists_file_real() {
        invlists_file_manager.close_invlists_file_real();
    }

    public term_statistics get_inverted_index_from_disk(lexicon_item lexicon_item) {
        term_statistics term_statistics = invlists_file_manager.get_inverted_index_from_disk(lexicon_item);

        return term_statistics;
    }

}
