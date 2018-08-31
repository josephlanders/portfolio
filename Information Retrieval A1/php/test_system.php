<?php

require_once("indexer.php");
require_once("utility.php");
require_once("invlists_file_manager.php");
require_once("map_file_manager.php");
require_once("lexicon_file_manager.php");
require_once("invlists_block_memory_cache_simple_array.php");
require_once("searcher.php");
require_once("term_statistics.php");

$test = new test();

//$testDefault = $test->testDefault();

  $testParseDocsIndivudally = $test ->testParseDocsIndividually();

  //$testParseDocsAllDocs = $test ->testParseAllDocs();

  //$testSmallBlob = $test->testSmallBlob();
  //$testMediumBlob = $test->testMediumBlob();
  //$testLargeBlob = $test->testLargeBlob();

class test {

    public function __construct() {
        
    }

    // Test 1 tests that the default settings work
    public function testDefault() {
        echo "test Default\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        /*
          $parse_docs_individually = true;
          $use_buffering = true;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         */

        $parse_docs_individually = false;
        $use_buffering = false;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 5000000;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            $new_answer = $term_statistics->number_of_unique_documents_occurs_in;
            if ($new_answer == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            var_dump($term_statistics->occurances_per_document_array);

            $new_answer = $term_statistics->occurances_per_document_array;
            if ($answer == $new_answer ) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();
        //$index->clear_files();
        //$index->start_processing();


        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    // Test 1 tests that the default settings work
    public function testParseAllDocs() {
        echo "test parse all docs\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        /*
          $parse_docs_individually = true;
          $use_buffering = true;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         */

        $parse_docs_individually = false;
        $use_buffering = false;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 5000000;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();

        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    // Test 1 tests that the default settings work
    public function testParseDocsIndividually() {
        echo "test ParseDocsIndividually\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        $parse_docs_individually = true;
        $use_buffering = true;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        /*
          $parse_docs_individually = false;
          $use_buffering = false;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         * 
         */

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 5000000;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            //var_dump($term_statistics);
            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();

        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    // A small blob is smaller than one doc size (thus requires blob stitching).
    public function testSmallBlob() {
        echo "test small blob\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        /*
          $parse_docs_individually = true;
          $use_buffering = true;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         */

        $parse_docs_individually = false;
        $use_buffering = false;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 10;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();

        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    // Test 1 tests that the default settings work
    // A medium blob is one that is bigger than one DOC size but smaller than the entire docs
    public function testMediumBlob() {
        echo "test medium blob\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        /*
          $parse_docs_individually = true;
          $use_buffering = true;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         */

        $parse_docs_individually = false;
        $use_buffering = false;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 300;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();

        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    // A large blob is big enough to read the entire file
    public function testLargeBlob() {
        echo "test large blob\n";
        $doc_content = $this->get_test_doc();

        $doc_filename = "testdoc.txt";
        file_put_contents($doc_filename, $doc_content);

        /*
          $parse_docs_individually = true;
          $use_buffering = true;
          $use_variable_length_disk_blocks = true;
          $write_memory_buffer_during_processing = false;
         */

        $parse_docs_individually = false;
        $use_buffering = false;
        $use_variable_length_disk_blocks = true;
        $write_memory_buffer_during_processing = false;

        $measure_times = false;
        $verbose = false;
        $maximum_fixed_block_size = 10000;
        $maximum_fixed_block_size_postings = 3000;
        $block_cache_memory_buffer_size = 9000000;
        $use_memory_buffer_cache_eviction = false;
        $file_blob_read_length = 10000;

        $config_array = array("parse_docs_individually" => $parse_docs_individually,
            "use_buffering" => $use_buffering,
            "use_variable_length_disk_blocks" => $use_variable_length_disk_blocks,
            "measure_times" => $measure_times,
            "verbose" => $verbose,
            //"padded_block_size" => $this->padded_block_size,
            "maximum_fixed_block_size" => $maximum_fixed_block_size,
            "maximum_fixed_block_size_postings" => $maximum_fixed_block_size_postings,
            "write_memory_buffer_during_processing" => $write_memory_buffer_during_processing,
            "block_cache_memory_buffer_size" => $block_cache_memory_buffer_size,
            "use_memory_buffer_cache_eviction" => $use_memory_buffer_cache_eviction,
            "file_blob_read_length" => $file_blob_read_length,
            "collection_to_index" => $doc_filename,
            "lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists");

        $configuration = new configuration($config_array);

        //var_dump($configuration);
        //die();
        $index = new Indexer($configuration);
        $index->initialise();
        $index->clear_files();
        $index->start_processing();


        $config_array = array("lexicon_filename" => "test_lexicon",
            "map_filename" => "test_map",
            "invlists_filename" => "test_invlists",
            //"query_terms_array" => array("the"),
            "measure_time" => true);
        $configuration = new configuration($config_array);

        $search = new searcher($configuration);
        $search->initialise();
        // Search for first term in file.
        $term_statistics = $search->search("the");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 2, "1" => 2, "2" => 2,
                "3" => 4, "4" => 2, "5" => 4);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
            //die();
        }


        $index->initialise();

        // Search for term in middle of lexicon (not first or last term)
        $term_statistics = $search->search("headline");

        if ($term_statistics != null) {
            $answer = 6;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("0" => 1, "1" => 1, "2" => 1,
                "3" => 2, "4" => 1, "5" => 2);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        //die();

        $index->initialise();

        // Search for term at end of lexicon
        $term_statistics = $search->search("end");

        if ($term_statistics != null) {
            $answer = 1;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }

        $index->initialise();

        // Search for term in middle of file (not first or last term)
        $term_statistics = $search->search("of");

        if ($term_statistics != null) {
            $answer = 2;
            if ($term_statistics->number_of_unique_documents_occurs_in == $answer) {
                echo "success\n";
            } else {
                echo "fails\n";
            }

            $answer = array("3" => 1, "5" => 1);

            //var_dump($term_statistics->occurances_per_document_array);

            if ($answer == $term_statistics->occurances_per_document_array) {
                echo "success\n";
            } else {
                echo "fails\n";
            }
        }



        //$term_statistics = $search->search("document");
        //if ($term_statistics != null) {
        //    echo $term_statistics->number_of_unique_documents_occurs_in;
        //}
    }

    public function get_test_doc() {
        $doc_content = <<<ENDDOC
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
    }

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


/*
 *     /*
          $lexicon_filename = "test_lexicon";
          $invlists_filename = "test_invlists";
          $map_filename = "test_map";

          $config_array = array("use_buffering" => $this->use_buffering,
          "use_variable_length_disk_blocks" => $this->use_variable_length_disk_blocks,
          "measure_times" => $this->measure_times,
          "verbose" => $this->verbose,
          //"padded_block_size" => $this->padded_block_size,
          "maximum_fixed_block_size" => $this->maximum_fixed_block_size,
          "maximum_fixed_block_size_postings" => $this-> maximum_fixed_block_size_postings,
          "write_memory_buffer_during_processing" => $this->write_memory_buffer_during_processing,
          "block_cache_memory_buffer_size" => $this->block_cache_memory_buffer_size,
          "use_memory_buffer_cache_eviction" => $this->use_memory_buffer_cache_eviction);

          $configuration = new configuration($config_array);

          $lexicon_file_manager = new lexicon_file_manager(array("lexicon_filename" => $lexicon_filename,
          "configuration" => $configuration));
          $lexicon_file_manager->initialise_lexicon_file();

          $map_file_manager = new map_file_manager(array("map_filename" => $map_filename,
          "configuration" => $configuration));
          $map_file_manager->initialise_map_file();

          $invlists_file_manager = new invlists_file_manager(array("invlists_filename" => $invlists_filename, "lexicon_filename" => $this->lexicon_filename,
          "lexicon_file_manager" => $lexicon_file_manager,
          "configuration" => $configuration));
          $invlists_file_manager->initialise_invlists_file();

          $utility = new utility(array("lexicon_filename" => $lexicon_filename,
          "invlists_filename" => $invlists_filename,
          "map_filename" => $map_filename,
          "configuration" => $configuration,
          "invlists_file_manager" => $invlists_file_manager,
          "lexicon_file_manager" => $lexicon_file_manager,
          "map_file_manager" => $map_file_manager));

          // Write a file, parse it then search for correct reuslts
          $this->parse_data_one_doc_at_a_time($collection_to_index);
         */