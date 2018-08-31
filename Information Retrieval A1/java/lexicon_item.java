package code;

public class lexicon_item {
    String term = "";
    Integer file_offset = 0;
    int disk_block_size = 0;
    int content_size = 0;
    
    public lexicon_item()
    {
        
    }
    
    public lexicon_item(String term, Integer file_offset,
                    int disk_block_size,
                    int content_size)
    {
        this.term = term;
        this.file_offset = file_offset;
        this.disk_block_size = disk_block_size;
        this.content_size = content_size;
    }
    
    public String toString()
    {
        String str = "term: " + term;
        str += "\n   file offset: " + file_offset;
        str += "\n   disk_block_size: " + disk_block_size;
        str += "\n   content_size: " + content_size;
        return str;
    }
}
