package code;

import java.io.*;
import java.util.*;

public class test_system {

    public static void main(String[] args) {
        test_system test = new test_system();

        test.testParseDocsIndividually();
    }

    public test_system() {
    }

    // Test 1 tests that the default settings work
    public void testParseDocsIndividually() {
        System.out.println("test ParseDocsIndividually\n");
        //doc_content = get_test_doc();

        //doc_filename = "testdoc.txt";
        //file_put_contents($doc_filename, $doc_content);
        boolean parse_docs_individually = true;
        boolean use_buffering = true;
        boolean use_variable_length_disk_blocks = true;
        boolean write_memory_buffer_during_processing = false;

        boolean measure_times = false;
        boolean verbose = false;
        int maximum_fixed_block_size = 10000;
        int maximum_fixed_block_size_postings = 3000;
        int block_cache_memory_buffer_size = 9000000;
        boolean use_memory_buffer_cache_eviction = false;
        int file_blob_read_length = 5000000;

        configuration configuration = null;
        indexer indexer = new indexer(configuration);
        
        String args[] = new String[1];
        args[0] = "testdoc.txt";
        
        indexer.parse_arguments(args);
        indexer.initialise();
        indexer.clear_files();
        indexer.start_processing();

        configuration configuration2 = null;
        
        String args2[] = new String[3];
        args2[0] = "lexicon";
        args2[1] = "invlists";
        args2[2] = "map";
        //args2[3] = "headline";

        searcher searcher = new searcher(configuration2);
        searcher.parse_arguments(args2);
        searcher.initialise();
        // Search for first term in file.
        term_statistics term_statistics = searcher.search("the");

        HashMap<Integer, Integer> answer2 = null;
        if (term_statistics != null) {
            int answer = 6;
            if (term_statistics.number_of_unique_documents_occurs_in == answer) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }

            answer2 = new HashMap<Integer, Integer>();
            answer2.put(0, 2);
            answer2.put(1, 2);
            answer2.put(2, 2);
            answer2.put(3, 4);
            answer2.put(4, 2);
            answer2.put(5, 4);
            //answer = array("0" => 2, "1" => 2, "2" => 2,
            //"3" => 4, "4" => 2, "5" => 4);

            boolean matches = true;
            Iterator<Map.Entry<Integer, Integer>> it = answer2.entrySet().iterator();
            while (it.hasNext()) {
                Map.Entry<Integer, Integer> pair = it.next();
                int id = pair.getKey();
                int value = pair.getValue();
                if (term_statistics.occurances_per_document_array.containsKey(id)) {
                    if (term_statistics.occurances_per_document_array.get(id) == value) {

                    } else {
                        matches = false;
                        break;

                    }
                } else {
                    matches = false;
                    break;
                }

            }

            if (matches == true) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }
            //die();
        }

        indexer.initialise();

         term_statistics = searcher.search("end");

        if (term_statistics != null) {
            int answer = 1;
            if (term_statistics.number_of_unique_documents_occurs_in == answer) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }

            answer2 = new HashMap<Integer, Integer>();
            answer2.put(5, 1);

            //$answer = array("5" => 1);
            boolean matches = true;
            Iterator<Map.Entry<Integer, Integer>> it = answer2.entrySet().iterator();
            while (it.hasNext()) {
                Map.Entry<Integer, Integer> pair = it.next();
                int id = pair.getKey();
                int value = pair.getValue();
                if (term_statistics.occurances_per_document_array.containsKey(id)) {
                    if (term_statistics.occurances_per_document_array.get(id) == value) {

                    } else {
                        matches = false;
                        break;

                    }
                } else {
                    matches = false;
                    break;
                }

            }

            if (matches == true) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }
            //die();
        }

        indexer.initialise();

        term_statistics = searcher.search("headline");

        if (term_statistics != null) {
            int answer = 6;
            if (term_statistics.number_of_unique_documents_occurs_in == answer) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }

            answer2 = new HashMap<Integer, Integer>();
            answer2.put(0, 1);
            answer2.put(1, 1);
            answer2.put(2, 1);
            answer2.put(3, 2);
            answer2.put(4, 1);
            answer2.put(5, 2);

            //$answer = array("0" =  > 1, "1" =  > 1, "2" =  > 1,
            //        "3" =  > 2, "4" =  > 1, "5" =  > 2);

            boolean matches = true;
            Iterator<Map.Entry<Integer, Integer>> it = answer2.entrySet().iterator();
            while (it.hasNext()) {
                Map.Entry<Integer, Integer> pair = it.next();
                int id = pair.getKey();
                int value = pair.getValue();
                if (term_statistics.occurances_per_document_array.containsKey(id)) {
                    if (term_statistics.occurances_per_document_array.get(id) == value) {

                    } else {
                        matches = false;
                        break;

                    }
                } else {
                    matches = false;
                    break;
                }

            }

            if (matches == true) {
                System.out.println("success");
            } else {
                System.out.println("fails");
            }
            //die();
        }
    }

    /* No heredoc equivalent in java... 
    public void get_test_doc() {
        String doc_content = <<<ENDDOC
<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-1 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline contains 
</HEADLINE>
...
<TEXT>
The very interesting text
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-2 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline contains words
</HEADLINE>
...
<TEXT>
Extraordinarily the very interesting text
</TEXT>
</DOC>


<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-3 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline contains a sheep
</HEADLINE>
...
<TEXT>
Sometimes the very interesting text
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-4 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline is about gorbachev the leader of the USSR
</HEADLINE>
...
<TEXT>
This could be the very interesting text and a nice headline
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-5 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline doesn't go anywhere
</HEADLINE>
...
<TEXT>
much wow the extraordinary text
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> DOC-6 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline is not seen! the end
</HEADLINE>
...
<TEXT>
The last bit of extraordinary text compared to the headline
</TEXT>
</DOC>
ENDDOC;
        return $doc_content;
    } */
}

/*
        // Make test doc
$doc_content = <<<ENDDOC
<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> 12345 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text content of the special document is not a joke. oracle mad mad
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> 1225123 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text extraordinary special good content of the document. mad
</TEXT>
</DOC>


<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> 35 </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text wow content of the document. wow wow
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> ndg </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text hello content of the document.
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> gad </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text content of the document. wow
</TEXT>
</DOC>

<DOC>
<DOCNO> LA010189-0001 </DOCNO>
<DOCID> nfgb </DOCID>
<DATE> 0123/123/123 </DATE>
<SECTION> .6usdgsf </SECTION>
<LENGTH> .5asdd </LENGTH>
<HEADLINE>
The article headline.
</HEADLINE>
...
<TEXT>
The text content of the document document. wow wow
</TEXT>
</DOC>
ENDDOC;
 */



      