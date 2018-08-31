/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#include <time.h>

typedef struct resourcenode
{
    /*
       struct resourcenode * prev;
       struct resourcenode * next;
       */
    struct resourcenode * hashnext;
    char * name;
    unsigned int bytes;
    double key;
    unsigned int heapposition;
}
resourcenode;

#define STR_CRITICAL_FAILURE "CRITICAL FAILURE, \
EMPTY CACHE IS NOT BIG ENOUGH FOR NEW CACHE OBJECT, \
PLEASE INCREASE CACHE SIZE"

/*
 * Searches for a resource node by name and size 
 *
 * wc is a pointer to the webcache struct
 * resource is a pointer to the name of the resource we are searchign for
 * bytes is the size of the resource
 * 
 * returns TRUE or FALSE to indicate success or fail (hit/miss)
 */
int retrieveResourceNode(WebCache *wc, char *resource, unsigned int bytes);

/*
 * Make space in the cache
 *
 * wc is a pointer to the webcache struct
 * bytes is the size of the item we want to insert 
 */
void makeSpace(WebCache * wc, unsigned int bytes);

/*
 * Inserts a resourcenode into the hashtable and queue 
 * 
 * wc is a pointer to the webcache struct
 * resource is a pointer to the name of the resource we want to insert
 * bytes is the size of the resource we want to insert
 * 
 * returns size of the cache that is in use
 */
int insertResourceNode(WebCache *wc, char *resource, unsigned int bytes);

/*
 * Create a resourcenode and return a pointer to it 
 *
 * wc is a pointer to the webcache struct
 * name is a pointer to the name of the resource we want to create
 * bytes is the size of the resource we want to create
 * 
 * returns a pointer to the new resource node
 */
resourcenode * createResourceNode(WebCache *wc, char *name, unsigned int bytes);

/*
 * Iterates through the resource nodes, printing their name 
 *
 * current is a pointer to the first node we want to print 
 */
void printNodes(WebCache * wc);

/*
 * Free all resources in the queue and associated nodes 
 *
 * current is a pointer to the first node we want to free 
 */
void freeResources(resourcenode * current);

/*
 * Frees a resource node from memory 
 * 
 * current is a pointer to a pointer to a resource node
 * 
 * (This is to avoid stack corruption)
 */
void freeResourceNode(resourcenode ** current);

/*
 * Compares a resource node to a set of criteria and returns the result.
 * 
 * current is a pointer to the resource node.
 * resource is a pointer to the resource name we are looking for.
 * bytes is the size of the resource we are looking for.
 * 
 * returns TRUE or FALSE to indicate status of operation.
 */
int checkMatch(resourcenode * current, char * resource, unsigned int bytes);
