s3163776 - Joseph Peter Landers  s3163776@student.rmit.edu.au   josephlanders@gmail.com

Execution instructions:

Index
-----

./php index.php document_to_index
./php index.php -p document_to_index
./php index.php -s stoplist -p  document_to_index
./php index.php -p -s stoplist document_to_index  may also work

Optional flags:
      -x show debug information, -t show run times
      
 ./php index.php  -s stoplist -p small -x -t     


LATIMES file
------------
If you try to process a big file you have to increase PHPs memory limit - 512M may not be enough I don't know because I haven't got it to complete processing the latimes file. :

 ./php -d memory_limit=512M -dzend.enable_gc=1 index.php  -s stoplist -p latimes -x -t

What i've found is that I can process 1M or 2M files.

So I just cut the latimes file into a 2M chunk:

 head -c $(( 2 * 1024 * 1024 )) /KDrive/SEH/SCSIT/Students/Courses/ISYS1078/2017/a1/latimes  > smaller2MB





Search
------

./php search.php lexicon invlists map term term2 term3 term4 termN


Test System
----
./php test_system.php
