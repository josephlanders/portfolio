package code;

import java.nio.ByteBuffer;
import java.nio.ByteOrder;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

public class data_block {

    public int access_frequency = 0;
    public Integer file_offset = null;
    public String term = "";
    public int disk_block_size = 0;
    public int memory_block_size = 0;
    public int content_size = 0;
    public String data = "";
    public byte[] data_bytes = new byte[0];        ;
    public byte[] header_bytes = new byte[8];
    public byte[] inverted_list_bytes = new byte[0];

    public data_block(String term,
            Integer file_offset,
            int disk_block_size,
            int memory_block_size,
            int content_size) {
        this.term = term;
        this.file_offset = file_offset;
        this.disk_block_size = disk_block_size;
        this.memory_block_size = memory_block_size;
        this.content_size = content_size;
    }

    public data_block() {
    }

    public String toString() {
        String str = "";
        str = "\nterm: " + term;
        str += "\n file_offset: " + file_offset;
        str += ""
                + "\n disk_block_size: " + disk_block_size;
        str += "\n memory_block_size: " + memory_block_size;
        str += "\n content_size: " + content_size;
        str += "\n data size: " + data.length();

        int[] header = extract_header_efficient(this);
        str += "\n header num unique occurances: " + header[0];
        str += "\n header inv_list_size: " + header[1];
        
        int num_unique_occurances = header[0];
        int length_of_inv_list = header[1];

        System.out.println(num_unique_occurances);
        HashMap<Integer, Integer> occurances_per_document_array = get_block_inverted_list_from_data(this, num_unique_occurances, length_of_inv_list);

        str += "\n occurances per document array size: " + occurances_per_document_array.size();
        str += "\n occurances per document array contents: ";

        Iterator<Map.Entry<Integer, Integer>> it = occurances_per_document_array.entrySet().iterator();
        while (it.hasNext()) {
            Map.Entry<Integer, Integer> pair = it.next();
            int id = pair.getKey();
            int value = pair.getValue();

            str += "\n id: " + id;
            str += " occurances: " + value;
        }

        return str;
    }

    public byte[] get_block_data() {
        byte[] combined = new byte[header_bytes.length + inverted_list_bytes.length];

        System.arraycopy(header_bytes, 0, combined, 0, header_bytes.length);
        System.arraycopy(inverted_list_bytes, 0, combined, header_bytes.length, inverted_list_bytes.length);
        return combined;
    }

    public int[] extract_header_efficient(data_block data_block_class) {
        byte[] header_bytes = data_block_class.header_bytes;
        int length_of_header = 8;

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
        } else {
            System.out.println("Inverted list length too small for term: " + data_block_class.term);

        }
        return occurances_per_document_array;
    }

}
