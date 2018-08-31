/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Configuration.cpp
 * Author: z
 * 
 * Created on March 22, 2017, 10:28 PM
 */

#include "Configuration.h"
#include <map>
#include <string>
using namespace std;

    Configuration::Configuration() {
    }

    Configuration::Configuration(const Configuration& orig) {
    }

    Configuration::~Configuration() {
    }

    Configuration::Configuration(map<string, string> config_strings, map<string, bool> config_bools,
            map<string, int> config_ints) {
        this->config_strings = config_strings;
        this->config_bools = config_bools;
        this->config_ints = config_ints;
    }

    map<string, string> Configuration::get_Configuration() {
        return this->config_strings;
    }

    bool Configuration::get_boolean(string key) {
        bool status = false;
        map<string, bool>::iterator it;
        it = config_bools.find(key);
        if (it != config_bools.end()) {
            //status = config_bools[key);
            status = it -> second;
        }
        return status;
    }

    void Configuration::set_boolean(string key, bool boolbool) {
        // will this work on an empty key? or do we need to insert and check if exists?
        this->config_bools[key] = boolbool;
    }
