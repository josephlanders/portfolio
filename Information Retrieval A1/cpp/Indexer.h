/* 
 * File:   Indexer.h
 * Author: z
 *
 * Created on March 22, 2017, 10:25 PM
 */

#ifndef INDEXER_H
#define INDEXER_H
#include "Term_statistics.h"
#include "Invlists_file_manager.h"
#include "Invlists_manager.h"
#include "Map_file_manager.h"
#include "Lexicon_file_manager.h"
#include "Stoplist_file_manager.h"
#include "Configuration.h"
#include "Doc_class.h"
#include "Map_item.h"
#include <map>
#include <list>
#include <string>
using namespace std;

class Indexer {
public:
    Indexer();
    Indexer(const Indexer& orig);
    virtual ~Indexer();

    void parse_arguments(int argc, char** argv);
    void initialise();
    void start_processing();
    map<string, list<string>> arguments(int argc, char** argv);
    void parse_data_one_doc_at_a_time(string collection_to_index);

    // Count term frequency per document and store in array
    // store in Term_statistics class -> occurances_per_document array
    //   the key is the ordinal_number and the value is the number of occurances

    struct Ret_term_frequency_per_document {
        map<string, Term_statistics> inverted_list;
        map<int, Doc_class> blob_doc_array;
    };

    struct Ret_find_doc {
        Doc_class doc_class;
        bool found_whole_doc_status = false;
        int blob_end_pos = 0;
        bool start_doc_found = false;
        bool end_doc_found = false;
    };

    struct Ret_docs_from_blob {
        int blob_end_pos;
        bool found_doc;
        bool stitch_doc;
        map<int, Doc_class> blob_doc_array;
    };

    struct Ret_next_blob {
        string blob = "";
        int length_to_read = 0;
    };

    //    struct Ret_term_frequency_per_document;
    //    struct Ret_term_frequency_per_document_one_doc;

    struct Ret_term_frequency_per_document_one_doc {
        Doc_class doc_class;
        map<string, Term_statistics> inverted_list;
    };

private:

    /*
     long all_doc_number;
        bool print_content_terms;
        string stoplist_file;
        bool stoplist_file_specified;
        //std::vector<Term_statistics> inverted_list;
        bool clean_immediately;
        bool clean_individual;
        bool parse_docs_individually;
        bool debug;
        long file_blob_read_length;
        bool use_buffering;
        bool use_variable_length_disk_blocks;
        string lexicon_filename;
        string map_filename;
        string invlists_filename;
        string stoplist_filename;
        Map_file_manager map_file_manager;
        Invlists_file_manager invlists_file_manager;
        Lexicon_file_manager lexicon_file_manager;
        Stoplist_file_manager stoplist_file_manager;
        bool measure_times;
        bool verbose;
        long block_cache_memory_buffer_size;
        bool write_memory_buffer_during_processing;
        bool use_memory_buffer_cache_eviction;
        Configuration configuration;
        string collection_to_index;
        int integer_length;
     */

    // Our unique reference to documents in memory, used as an incrementer
    long all_doc_number = 0;
    bool print_content_terms = false;
    string stoplist_filename = "";
    bool stoplist_file_specified = false;
    //std::vector<Term_statistics> inverted_list;
    bool clean_immediately = false;
    bool clean_individual = false;
    bool parse_docs_individually = false;
    bool debug = false;
    long file_blob_read_length = 10000000;
    bool use_buffering = false;
    bool use_variable_length_disk_blocks = true;
    string lexicon_filename = "lexicon";
    string map_filename = "map";
    string invlists_filename = "invlists";
    Map_file_manager map_file_manager;
    Invlists_manager invlists_manager;
    Lexicon_file_manager lexicon_file_manager;
    Stoplist_file_manager stoplist_file_manager;
    bool measure_times = false;
    bool verbose = false;
    long block_cache_memory_buffer_size = 10000;
    bool write_memory_buffer_during_processing = true;
    bool use_memory_buffer_cache_eviction = false;
    Configuration configuration;
    string collection_to_index = "";
    int integer_length = 4;
    bool eof = false;

    map<string, Term_statistics> get_term_frequency_by_unique_document_count(map<string, Term_statistics> inverted_list);
    Ret_term_frequency_per_document get_term_frequency_per_document(map<string, Term_statistics> inverted_list, map<int, Doc_class> blob_doc_array);
    Ret_term_frequency_per_document_one_doc get_term_frequency_per_document_one_doc_array(map<string, Term_statistics> inverted_list, Doc_class doc_class);        
    map<int, Doc_class> get_content_terms_as_tokens(map<int, Doc_class> blob_doc_array);
    Doc_class get_content_terms_as_tokens_one_doc(Doc_class doc_class);    
    map<int, Doc_class> extract_doc_text_from_doc_array(map<int, Doc_class> blob_doc_array);
    string get_data_in_tag_multiline(Doc_class doc_class, string start_tag, string end_tag);
    Ret_docs_from_blob get_docs_from_blob(string blob, int blob_start_pos, map<int, Doc_class> blob_doc_array);
    Ret_find_doc find_doc(string blob, int blob_start_pos, string start_tag, string end_tag);
    Ret_next_blob get_next_blob(FILE *fp, long filesize, int file_start_pos, int length_to_read, bool stitch_blob);
    int get_length_to_read(int length_to_read, long filesize, int file_start_pos, bool stitch_blob);
    bool increment_file_pointer(FILE *fp, int new_start_position);  
    string read_blob(FILE *fp, int length_to_read);
    int get_next_file_pointer(int file_start_pos, int blob_start_pos, bool stitch_blob);
    void write_out_inverted_list_array_struct_to_disk_variable_length_blocks_with_simple_write_buffering(map<string, Term_statistics> inverted_list);
    void write_buffer_simple_array();
    string trim(string token);
    string toLower(string token);
    vector<string> split(string token, string split_regex_string);
    int parseLine(char* line);
    int get_memory_stats();
};

#endif /* INDEXER_H */

