/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 
 * File:   Map_item.h
 * Author: z
 *
 * Created on April 3, 2017, 7:30 PM
 */

#ifndef MAP_ITEM_H
#define MAP_ITEM_H

#include <string>
using namespace std;
class Map_item {
public:
    int id = 0;
    string docid = "MAP_ITEM_UNINITIALISED";
    bool null = true;
    Map_item();
    Map_item(const Map_item& orig);
    virtual ~Map_item();
    Map_item(int id, string docid);
private:

};

#endif /* MAP_ITEM_H */

