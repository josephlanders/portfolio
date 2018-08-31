/* 
 * File:   Doc_class.cpp
 * Author: z
 *
 * Created on March 22, 2017, 10:28 PM
 */

#include "Doc_class.h"
//package code;

//import java.util.*;
using namespace std;
/*
    string raw_text = "";
    string text = "";
    string docid = "";
    int id = 0;
    string headline = "";
    bool null = true;
    vector<string> tokens;// = NULL;
    vector<string> cleaned_tokens; //= NULL;
 * */
Doc_class::Doc_class() {
    //null = false;
}

Doc_class::Doc_class(int id)
{
    this->id = id;
    this->null = false;
}

Doc_class::Doc_class(const Doc_class& orig) {
    this->id = orig.id;
    this-> raw_text = orig.raw_text;
    this->text = orig.text;
    this->docid = orig.docid;
    this->headline = orig.headline;
    this->null = orig.null;
    this->tokens = orig.tokens;
    this->cleaned_tokens = orig.cleaned_tokens;
}

Doc_class::~Doc_class() {
}

string Doc_class::toString()
{
    string str = "";
//    str = "id: " + this->id + "\n";
    //str = "raw_text length: " + this->raw_text.length() + "\n";
    //str = "text length: " + this->text.length() + "\n";
    return str;
}
