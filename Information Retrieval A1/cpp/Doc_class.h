/* 
 * File:   Doc_class.h
 * Author: z
 *
 * Created on March 22, 2017, 10:29 PM
 */

#ifndef DOC_CLASS_H
#define DOC_CLASS_H
#include <string>
#include <vector>
using namespace std;
class Doc_class {
public:
    string raw_text;// = "";
    string text;// = "";
    string docid = "DOC_ITEM_UNINITIALISED";// = "";
    int id = -1;// = 0;
    string headline = "DOC_ITEM_UNINITIALISED";// = "";
    bool null = true;
    vector<string> tokens;// = NULL;
    vector<string> cleaned_tokens; //= NULL;
    
    Doc_class();
    Doc_class(int id);
    Doc_class(const Doc_class& orig);
    virtual ~Doc_class();
    string toString();
private:

};

#endif /* DOC_CLASS_H */

