//#include <cstdlib>
#include <cstdio>
//#include <cstring>
//#include <iostream>
//#include <string>
//#include <vector>
//#include "c:\users\z\Desktop\CppApplication_1\Indexer.h"
#include "Indexer.h"
using namespace std;  
/*
 * 
 */
int main(int argc, char** argv) {

    printf("index");
    
//    Indexer *ind2 = new Indexer();

  //  int a = ind2 -> parse_arguments(argc, argv);
    
    
        Indexer ind2; 
    ind2.parse_arguments(argc, argv);
    
        ind2.initialise();
        //ind2.clear_files();
        printf("start processing");
        ind2.start_processing();;
        printf("end processing");
        /*
    ind2 -> initialise();
    ind2 -> start_processing();*/
    return 0;
}



