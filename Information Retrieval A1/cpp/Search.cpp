//#include <cstdlib>
#include <cstdio>
//#include <cstring>
//#include <iostream>
//#include <string>
//#include <vector>
//#include "c:\users\z\Desktop\CppApplication_1\Indexer.h"
#include "Searcher.h"
using namespace std;  
/*
 * 
 */
int main(int argc, char** argv) {

    printf("hello");
    
//    Indexer *ind2 = new Indexer();

  //  int a = ind2 -> parse_arguments(argc, argv);
    
    
        Searcher sea2; 
    sea2.parse_arguments(argc, argv);
    
    sea2.initialise();
        printf("start processing");
        sea2.start_processing();;
        printf("end processing");
    return 0;
}



