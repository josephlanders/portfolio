/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Term_statistics.h
 * Author: z
 *
 * Created on March 22, 2017, 9:48 PM
 */

#ifndef TERM_STATISTICS_H
#define TERM_STATISTICS_H

#include <string>
#include <map>
using namespace std;

class Term_statistics {
public:
    bool null = true;
    string term = "TERM_STATISTICS_UNINITIALISED";
    int number_of_unique_documents_occurs_in = 0;
    map<int,int> occurances_per_document_array;
    int length_of_inv_list = 0;
    Term_statistics();
    Term_statistics(string term);
    Term_statistics(const Term_statistics& orig);
    virtual ~Term_statistics();
    string toString();
    void destruct();
private:

};

#endif /* TERM_STATISTICS_H */

