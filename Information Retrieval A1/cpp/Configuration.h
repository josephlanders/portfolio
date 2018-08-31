/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Configuration.h
 * Author: z
 *
 * Created on March 22, 2017, 10:28 PM
 */

#ifndef CONFIGURATION_H
#define CONFIGURATION_H
#include <string>
#include <map>
using namespace std;
class Configuration {
public:
    Configuration();
    Configuration(const Configuration& orig);
    virtual ~Configuration();
    bool get_boolean(string key);
    void set_boolean(string key, bool boolbool);
    map<string, string> get_Configuration();
    Configuration(map<string, string> config_strings, map<string, bool> config_bools,
            map<string, int> config_ints);
private:
    map<string, string> config_strings; // = new map<string, string>();
    map<string, bool> config_bools; // = new map<string, bool>();
    map<string, int> config_ints; // = new map<string, int>();

};

#endif /* CONFIGURATION_H */

