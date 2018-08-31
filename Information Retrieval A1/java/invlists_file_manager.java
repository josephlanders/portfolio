package code;

import code.configuration;
import java.util.*;
import java.nio.ByteBuffer;
import java.nio.*;
import java.io.*;

public class invlists_file_manager {

    public invlists_file_manager(String invlists_filename, String lexicon_filename,
            lexicon_file_manager lexicon_file_manager, configuration configuration) {
        this.lexicon_file_manager = lexicon_file_manager;
        this.lexicon_filename = lexicon_filename;
        this.invlists_filename = invlists_filename;
        this.configuration = configuration;
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
    private configuration configuration = null;
    private RandomAccessFile r = null;

    public void initialise_invlists_file() {
        try {
            RandomAccessFile r = new RandomAccessFile(invlists_filename, "rw");
            r.setLength(4);
            r.seek(0);

            byte[] b = new byte[4];
            ByteBuffer bb = ByteBuffer.wrap(b);
            bb.order(ByteOrder.LITTLE_ENDIAN);
            bb.putInt(4);
            
            r.write(b);
            
            r.close();
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }
    }


    public int update_unique_occurances_for_block(int num_occurances_in_invlist, int incrementer) {

        num_occurances_in_invlist += incrementer;

        return num_occurances_in_invlist;
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

    public RandomAccessFile open_invlists_file() {
        RandomAccessFile r = this.r;
        if (r == null) {
            try {
                r = new RandomAccessFile(invlists_filename, "rw");
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
        }

        this.r = r;
        return r;
    }

    public void close_invlists_file() {

    }

    public void close_invlists_file_real() {
        if (r != null) {
            try {
                r.close();
            } catch (Exception e) {
                System.out.println(e.getMessage());
            }
            r = null;
        }
    }

    public data_block get_header_and_invlist_bytes_from_data(data_block data_block_class) {
        byte[] data_bytes = data_block_class.data_bytes;

        if (data_bytes.length >= 8) {

            byte[] header_bytes = new byte[8];

            System.arraycopy(data_bytes, 0, header_bytes, 0, 8);

            data_block_class.header_bytes = header_bytes;

            int inv_list_length = data_bytes.length - 8;
            if (inv_list_length > 0) {
                byte[] inv_list_bytes = new byte[inv_list_length];

                System.arraycopy(data_bytes, 8, inv_list_bytes, 0, inv_list_length);
                data_block_class.inverted_list_bytes = inv_list_bytes;

            }
        }

        return data_block_class;
    }

    public term_statistics get_inverted_index_from_disk(lexicon_item lexicon_item) {
        String term = lexicon_item.term;

        term_statistics term_statistics = new term_statistics(term);

        int file_offset_of_block = lexicon_item.file_offset;

        int content_size = lexicon_item.content_size;

        open_invlists_file();

        data_block data_block_class = get_block_from_disk(file_offset_of_block, content_size);

        data_block_class = get_header_and_invlist_bytes_from_data(data_block_class);

        int[] header = extract_header_efficient(data_block_class);

        int num_unique_occurances = header[0];

        term_statistics.number_of_unique_documents_occurs_in = num_unique_occurances;

        int length_of_inv_list = header[1];

        HashMap<Integer, Integer> occurances_per_document_array = get_block_inverted_list_from_data(data_block_class, num_unique_occurances, length_of_inv_list);

        term_statistics.occurances_per_document_array = occurances_per_document_array;

        close_invlists_file();

        return term_statistics;
    }

    public HashMap<Integer, Integer> get_block_inverted_list_from_data(data_block data_block_class, int num_unique_occurances, int length_of_inv_list) {
        HashMap<Integer, Integer> occurances_per_document_array = new HashMap<Integer, Integer>();

        byte[] inverted_list_bytes = data_block_class.inverted_list_bytes;
        if (inverted_list_bytes.length >= 8) {

            byte[] b = inverted_list_bytes;

            ByteBuffer bb = ByteBuffer.wrap(b);
            bb.order(ByteOrder.LITTLE_ENDIAN);

            int array_elements = num_unique_occurances;

            for (int i = 0; i < array_elements; i = i + 1) {
                int key = bb.getInt();
                int value = bb.getInt();
                occurances_per_document_array.put(key, value);
            }
        }
        return occurances_per_document_array;
    }

    public data_block get_block_from_disk(Integer file_offset_of_block, int length_of_block) {

        r = open_invlists_file();
        
        byte[] b = new byte[length_of_block];
        try {
            r.seek(file_offset_of_block);
            r.read(b);
        } catch (Exception e) {
            System.out.println(e.getMessage());
        }

        data_block data_block_class = new data_block();
        data_block_class.data_bytes = b;

        close_invlists_file();

        return data_block_class;
    }


    /*
    public term_statistics get_inverted_index_from_disk_old(lexicon_item lexicon_item) {

        String term = lexicon_item.term;

        term_statistics term_statistics = new term_statistics(term);



        int file_offset_of_block = lexicon_item.file_offset;

        open_invlists_file();

        int num_unique_occurances = get_unique_occurances_for_block(r, file_offset_of_block);

        term_statistics.number_of_unique_documents_occurs_in = num_unique_occurances;

        int length_of_inv_list = get_block_inverted_list_length(r, file_offset_of_block);

        HashMap<Integer, Integer> occurances_per_document_array = get_block_inverted_list(r, file_offset_of_block, num_unique_occurances, length_of_inv_list);

        term_statistics.occurances_per_document_array = occurances_per_document_array;

        close_invlists_file();


        // Don't use the integer sizes they are unreliable as PHP stores them as 1 byte sometimes
        //$content_size = $this -> integer_length + $this -> integer_length + $new_length_of_inv_list;
        //return $content_size;

        return term_statistics;
    } */


    /*
    public function explode_block_data(data)
    {
        term_statistics = new term_statistics("debug variable");
        
        data_unpacked = unpack($this->integer_keyword . "*", $data);
        $num_unique_occurances = $data_unpacked[1];
        $length_of_inv_list = $data_unpacked[2];
        //var_dump($inv_list_unpacked);
        //var_dump($num_unique_occurances);

        $occurances_per_document_array = array();
        //var_dump($num_unique_occurances);
        $expected_array_size = $num_unique_occurances * 2;
        //die();
        for ($i = 1 + 2; $i < $expected_array_size; $i = $i + 2) {

            $key = $data_unpacked[$i];

            $value = $data_unpacked[$i + 1];
            $occurances_per_document_array["$key"] = $value;
        }
       
        $term_statistics->number_of_unique_documents_occurs_in = $num_unique_occurances;

        $term_statistics->occurances_per_document_array = $occurances_per_document_array;
        
        $term_statistics -> length_of_inv_list = $length_of_inv_list;

 

        return $term_statistics;
    } */
    
    /*
        public HashMap<String, Object> create_blank_data_block_struct(boolean pad_block, int padded_block_size) {
        int num_occurances_in_all_docs = 0; // 32 bit pack?
        int length_of_inverted_list = 0; // 32 bit pack?

        String padding = "";
        if (pad_block == true) {
            // TODO: Padding in java?
            int len = padded_block_size - 4 - 4;
            //padding = str_pad(string, len, chr(0));
        }

        // Assume Binary Safe
        //String blank_data_block = num_occurances_in_all_docs + length_of_inverted_list + padding;
        String blank_data_block = num_occurances_in_all_docs + length_of_inverted_list;

        int size_of_block = blank_data_block.length();

        HashMap<String, Object> ret = new HashMap<String, Object>();

        ret.put("size_of_block", size_of_block);
        ret.put("blank_data_block", blank_data_block);

        return ret;
    }

    */
}
