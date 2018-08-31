s3163776 - Joseph Peter Landers  s3163776@student.rmit.edu.au   josephlanders@gmail.com

watch ls -o invlists

Execution instructions:

Index
-----

./php index.php document_to_index
./php index.php -p document_to_index
./php index.php -s stopwords -p  document_to_index
./php index.php -p -s stopwords document_to_index  may also work

Optional flags:
Example: -m memory intensive (default behaviour), -v variable inverted list block sizes (default behaviour),
      -x show debug information, -t show run times
      
 ./php index.php  -s stopwords.txt -p small -x -t

Example: -m memory intensive (default behaviour), -v variable inverted list block sizes (default behaviour),
      -x show debug information, -t show run times
      
 ./php index.php  -s stopwords.txt -p small  -m -v -x -t

Example: -d disk intensive, -v variable inverted list block sizes, 
            -b write buffering, -e write out buffer at end of processing,
            -x show debug information, -t show run times
 ./php index.php  -s stopwords.txt -p small  -d -b -v -e -x -t


Optional flags:
-m - Memory intensive (default behaviour) - Write inverted List after all documents processed.
PSEUDOCODE START
    DO        
        Read a 5MB blob into memory,
            FOREACH DOCUMENT IN BLOB
               Create or update inverted lists
            ENDFOREACH             
    UNTIL EOF
    Write inverted list to disk
PSEUDOCODE END

-v - variable block size (default behaviour) 
     Inverted lists on disk and in memory will be stored with variable block sizes.

-d - Disk intensive - Write Inverted Lists after each batch of documents in a file blob are processed.

     Must be used with -b to buffer/cache writes until end of processing
        and -e to delay buffer writes until end of processing

      The reason for these switches is that
      writing during processing would cause lots of mid-file insert operations 
      which are very disk intensive
      unless a file allocation table was used so that we could avoid insert costs

      NOTE: midfile insert operations don't work as expected either,
      we currently just replace blocks so smaller blocks that become
      larger will overwrite the next block in file.
 
     The memory usage of -d -b -v is the same as -m -v

PSEUDOCODE START
    DO         
        FOREACH DOCUMENT IN BLOB 
            Create inverted list
            Write inverted list for this document to memory buffer 
                and merge with existing inverted lists in memory buffer
        ENDFOREACH
    UNTIL EOF
    Write inverted list buffer to disk
PSEUDOCODE END    

-b - Buffer disk writes in memory (in-memory disk block caching)
     Creates php://data memory blocks
     Useful if updating inverted-lists on disk "on the fly" as we can group writes

-e - Delay buffer writes until end of file processing

-x - verbose

-t - measure execution times

-c - inverted list compression (NOT IMPLEMENTED)

Search
------

./php search.php lexicon invlists map term term2 term3 term4 termN