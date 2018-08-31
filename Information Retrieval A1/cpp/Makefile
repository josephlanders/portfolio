#
#  There exist several targets which are by default empty and which can be 
#  used for execution of your targets. These targets are usually executed 
#  before and after some main targets. They are: 
#
#     .build-pre:              called before 'build' target
#     .build-post:             called after 'build' target
#     .clean-pre:              called before 'clean' target
#     .clean-post:             called after 'clean' target
#     .clobber-pre:            called before 'clobber' target
#     .clobber-post:           called after 'clobber' target
#     .all-pre:                called before 'all' target
#     .all-post:               called after 'all' target
#     .help-pre:               called before 'help' target
#     .help-post:              called after 'help' target
#
#  Targets beginning with '.' are not intended to be called on their own.
#
#  Main targets can be executed directly, and they are:
#  
#     build                    build a specific configuration
#     clean                    remove built files from a configuration
#     clobber                  remove all built files
#     all                      build all configurations
#     help                     print help mesage
#  
#  Targets .build-impl, .clean-impl, .clobber-impl, .all-impl, and
#  .help-impl are implemented in nbproject/makefile-impl.mk.
#
#  Available make variables:
#
#     CND_BASEDIR                base directory for relative paths
#     CND_DISTDIR                default top distribution directory (build artifacts)
#     CND_BUILDDIR               default top build directory (object files, ...)
#     CONF                       name of current configuration
#     CND_PLATFORM_${CONF}       platform name (current configuration)
#     CND_ARTIFACT_DIR_${CONF}   directory of build artifact (current configuration)
#     CND_ARTIFACT_NAME_${CONF}  name of build artifact (current configuration)
#     CND_ARTIFACT_PATH_${CONF}  path to build artifact (current configuration)
#     CND_PACKAGE_DIR_${CONF}    directory of package (current configuration)
#     CND_PACKAGE_NAME_${CONF}   name of package (current configuration)
#     CND_PACKAGE_PATH_${CONF}   path to package (current configuration)
#
# NOCDDL


# Environment 
MKDIR=mkdir
CP=cp
CCADMIN=CCadmin
invlistOBJ=Invlists_file_manager.o Invlists_manager.o Invlists_block_memory_cache_simple_array.o 
mapOBJ=Lexicon_file_manager.o Lexicon_item.o 
lexiconOBJ=Map_file_manager.o Map_item.o 
stoplistOBJ=Stoplist_file_manager.o
oldindexOBJ=index.o indexer.o configuration.o data_block.o doc_class.o invlists_file_manager.o invlists_manager.o invlists_block_memory_cache_simple_array.o lexicon_file_manager.o lexicon_item.o map_file_manager.o map_item.o stoplist_file_manager.o term_statistics.o
indexOBJ=index.o Indexer.o Configuration.o Data_block.o Doc_class.o $(invlistOBJ) $(lexiconOBJ) $(mapOBJ) $(stoplistOBJ) Term_statistics.o
oldsearchOBJ=search.o searcher.o configuration.o data_block.o doc_class.o invlists_file_manager.o invlists_manager.o invlists_block_memory_cache_simple_array.o lexicon_file_manager.o lexicon_item.o map_file_manager.o map_item.o stoplist_file_manager.o term_statistics.o
searchOBJ=search.o Searcher.o Configuration.o Data_block.o Doc_class.o $(invlistOBJ) $(lexiconOBJ) $(mapOBJ) $(stoplistOBJ) Term_statistics.o
assignOBJ1=main.cpp configuration.cpp indexer.h indexer.cpp map_file_manager.cpp lexicon_file_manager.cpp invlists_file_manager.cpp stoplist_file_manager.cpp
LIBC=
FLAGS=-std=c++17
THIS_IS_BUILD_PATH=
THIS_IS_LIBRARY2=-Lc:/users/z/Desktop/CppApplication_1/ -Ic:/users/z/Desktop/CppApplication_1/
THIS_IS_LIBRARY3=-I/mingw/lib/gcc/mingw32/5.3.0/include/c++/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/backward/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/bits/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/debug/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/decimal/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/experimental/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/ext/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/mingw32/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/parallel/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/profile/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/tr1/ -I/mingw/lib/gcc/mingw32/5.3.0/include/c++/tr2/

FILES=*.cpp *.h *.txt makefile *.sh


# build
build: .build-post

.build-pre:
# Add your pre 'build' code here...

.build-post: .build-impl
# Add your post 'build' code here...


# clean
clean: #.clean-post
	rm -rf $(THIS_IS_BUILD_PATH)*.o assign assign.exe index index.exe search search.exe core *.h.gch

.clean-pre:
# Add your pre 'clean' code here...

.clean-post: .clean-impl
# Add your post 'clean' code here...


# clobber
clobber: .clobber-post

.clobber-pre:
# Add your pre 'clobber' code here...

.clobber-post: .clobber-impl
# Add your post 'clobber' code here...


# all
all: newindex newsearch .all-post


.all-pre:
# Add your pre 'all' code here...

.all-post: .all-impl
# Add your post 'all' code here...

index: indexer.o index.o configuration.o data_block.o doc_class.o invlists_block_memory_cache_simple_array.o invlists_file_manager.o invlists_manager.o lexicon_file_manager.o lexicon_item.o map_file_manager.o map_item.o stoplist_file_manager.o term_statistics.o
	g++ -o index $(THIS_IS_LIBRARY) $(indexOBJ)  $(FLAGS)

search:  search.o searcher.o configuration.o data_block.o doc_class.o invlists_block_memory_cache_simple_array.o invlists_file_manager.o invlists_manager.o lexicon_file_manager.o lexicon_item.o map_file_manager.o map_item.o stoplist_file_manager.o term_statistics.o
	g++ -o search $(THIS_IS_LIBRARY) $(searchOBJ)  $(FLAGS)

newindex: Indexer.o index.o Configuration.o Doc_class.o invlists lexicon map stoplist
	g++ -o index $(THIS_IS_LIBRARY) $(indexOBJ)  $(FLAGS)

newsearch:  search.o Searcher.o Configuration.o Doc_class.o invlists lexicon map stoplist
	g++ -o search $(THIS_IS_LIBRARY) $(searchOBJ)  $(FLAGS)
	
invlists: Invlists_manager.o Invlists_file_manager.o Invlists_block_memory_cache_simple_array.o Data_block.o Term_statistics.o
	
lexicon: Lexicon_file_manager.o Lexicon_item.o
	
map: Map_file_manager.o Map_item.o
	
stoplist: Stoplist_file_manager.o

Configuration.o: Configuration.cpp Configuration.h
	g++ -g -Wall -c Configuration.cpp

Data_block.o: Data_block.cpp Data_block.h
	g++ -g -Wall -c Data_block.cpp
	
Doc_class.o: Doc_class.cpp Doc_class.h
	g++ -g -Wall -c Doc_class.cpp

index.o: index.cpp
	g++ -g -Wall -c $(THIS_IS_BUILD_PATH)index.cpp  $(FLAGS) 
	
Indexer.o: Indexer.cpp Indexer.h
	g++ -g -Wall -c $(THIS_IS_BUILD_PATH)Indexer.cpp $(FLAGS) 

Invlists_block_memory_cache_simple_array.o: Invlists_block_memory_cache_simple_array.cpp Invlists_block_memory_cache_simple_array.h
	g++ -g -Wall -c Invlists_block_memory_cache_simple_array.cpp

Invlists_file_manager.o: Invlists_file_manager.cpp Invlists_file_manager.h
	g++ -g -Wall -c Invlists_file_manager.cpp
	
Invlists_manager.o: Invlists_manager.cpp Invlists_manager.h
	g++ -g -Wall -c Invlists_manager.cpp

Lexicon_file_manager.o: Lexicon_file_manager.cpp Lexicon_file_manager.h
	g++ -g -Wall -c Lexicon_file_manager.cpp
	
Lexicon_item.o: Lexicon_item.cpp Lexicon_item.h
	g++ -g -Wall -c Lexicon_item.cpp

Map_file_manager.o: Map_file_manager.cpp Map_file_manager.h
	g++ -g -Wall -c $(THIS_IS_BUILD_PATH)Map_file_manager.cpp
	
Map_item.o: Map_item.cpp Map_item.h
	g++ -g -Wall -c Map_item.cpp

search.o: search.cpp
	g++ -g -Wall -c $(THIS_IS_BUILD_PATH)search.cpp  $(FLAGS) 
	
Searcher.o: Searcher.cpp Searcher.h
	g++ -g -Wall -c Searcher.cpp

Stoplist_file_manager.o: Stoplist_file_manager.cpp Stoplist_file_manager.h
	g++ -g -Wall -c Stoplist_file_manager.cpp
	
Term_statistics.o: Term_statistics.cpp Term_statistics.h
	g++ -g -Wall -c Term_statistics.cpp
	
# build tests
build-tests: .build-tests-post

.build-tests-pre:
# Add your pre 'build-tests' code here...

.build-tests-post: .build-tests-impl
# Add your post 'build-tests' code here...


# run tests
test: .test-post

.test-pre: build-tests
# Add your pre 'test' code here...

.test-post: .test-impl
# Add your post 'test' code here...


# help
help: .help-post

.help-pre:
# Add your pre 'help' code here...

.help-post: .help-impl
# Add your post 'help' code here...



# include project implementation makefile
#include nbproject/Makefile-impl.mk

# include project make variables
#include nbproject/Makefile-variables.mk
