package code;

import java.util.HashMap;

public class invlists_block_memory_cache_simple_array {

    private HashMap<String, data_block> data_blocks_array = null;
    private int cache_size_in_use = 0;
    public int block_cache_memory_buffer_size = 0; // 9 MB memory buffer
    private configuration configuration = null;
    private boolean use_memory_buffer_cache_eviction = false;

    public invlists_block_memory_cache_simple_array(configuration configuration) {
        this.configuration = configuration;
        data_blocks_array = new HashMap<String, data_block>();
    }

    public data_block allocate_new_cache_entry(String term, int disk_block_size, int memory_block_size) {
        data_block data_block_class = null;
        if (data_blocks_array.containsKey(term) == false) {
            data_block_class = new data_block(term,
                    null,
                    disk_block_size,
                    memory_block_size,
                    0);

            data_blocks_array.put(term, data_block_class);
            //System.out.println("Put in cache " + term);
        } else {
            System.out.println("didn't put in cache " + term);
        }

        return data_block_class;
    }

    public void update_cache(String term, data_block data_block_class) {
        data_blocks_array.put(term, data_block_class);
    }

    public data_block get_data_block_from_cache(String term) {
        data_block data_block_class = null;

        if (data_blocks_array.containsKey(term)) {
            data_block_class = data_blocks_array.get(term);
        }
        return data_block_class;
    }

    public HashMap<String, data_block> get_data_blocks_array() {
        return data_blocks_array;
    }
}
