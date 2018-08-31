/* 
 * File:   Term_statistics.cpp
 * Author: z
 * 
 * Created on March 22, 2017, 9:48 PM
 */

#include "Term_statistics.h"
#include <string>
#include <iostream>
using namespace std;
/*
    string term = "";
    int number_of_unique_documents_occurs_in = 0;
    map<int, int> occurances_per_document_array;
    string occurances_per_document_string = "";
    int length_of_inv_list = 0;
    bool null = false;
*/
    Term_statistics::Term_statistics() {
        //cout << "Constructor A" << endl;
        //cout << "null is: " << this->null << endl;
    }

        
    Term_statistics::Term_statistics(string term) {
        //cout << "Constructor B" << endl;
        this->term = term;
        this->null = false;
        //cout << "null is: " << this->null << endl;
        
        //occurances_per_document_array = new map<int, int>();
        map<int, int> occurances_per_document_array;
    }

    Term_statistics::Term_statistics(const Term_statistics& orig) {
        this->occurances_per_document_array = orig.occurances_per_document_array;
        this->number_of_unique_documents_occurs_in = orig.number_of_unique_documents_occurs_in;
        this->term = orig.term;
        this->length_of_inv_list = orig.length_of_inv_list;
        this->null = orig.null;
        //cout << "Constructor C" << endl;
        //cout << "null is: " << this->null << endl;
    }

    Term_statistics::~Term_statistics() {
    }
    

    
    string Term_statistics::toString()
    {
        string str = "";
        str = "\nterm: " + term;
        str += "\nnum occurances: " + number_of_unique_documents_occurs_in;
        str += "\nlength of inv list: " + length_of_inv_list;
        str += "\noccurances per document array: "; //+ occurances_per_document_array.toString();
        
        return str;
    }
    
    void Term_statistics::destruct()
    {
        occurances_per_document_array.clear();
//        occurances_per_document_array = NULL;
    }