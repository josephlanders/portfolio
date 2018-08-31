/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment
 * 
 * This code is the data structure code for storing 
 * and retrieving resources in a queue.
 ****************************************************************************/

#include "assign.h"
#include "webcache.h"
#include "utility.h"
#include "resourcenode-main.h"
#include "resourcenode-hash.h"
#include "resourcenode-heap.h"

/*
 * Returns the first resource found in the hashtable at that hash value
 * 
 * NOT NECESSARILY THE CORRECT RESOURCE, the collision chain needs to be 
 * checked by the calling function 
 * 
 * wc is a pointer to the webcache struct
 * resource is a pointer to the resource name we are using as the hash key
 * hashvalue is an int (the hash) that is returned to the calling function
 * 
 * The resource node at the head of the hash chain is returned.
 * 
 * We assume the resource name given exists - otherwise NULL is returned.
 */
resourcenode * getResourceByHash(WebCache * wc,
                                 char * resource,
                                 unsigned int * hashvalue)
{
    *hashvalue = hash(resource);

    return (wc -> hashtable)[*hashvalue];
}

/*
 * Return the first resource found in the hashtable at that hash value 
 * 
 * NOT NECESSARILY THE CORRECT RESOURCE, the collision chain needs to be 
 * checked by the calling function 
 * 
 * wc is a pointer to the webcache struct
 * resource is a pointer to the resource name we are using as the hash key
 * 
 * The resource node at the head of the hash chain is returned.
 * 
 * We assume the resource name given exists - otherwise NULL is returned.
 */
resourcenode * getResourceByHash2(WebCache * wc, char * resource)
{
    unsigned int hashvalue = 0;

    return getResourceByHash(wc, resource, &hashvalue);
}

/*
 * Unlink a resource node from the head of the hash chain 
 * 
 * wc is a pointer to the webcache struct
 * 
 * Note that this function does not free the resources memory
 */
void unlinkFromHeadOfHashChain(WebCache *wc,
                               resourcenode * hashcurrent,
                               unsigned int hashvalue)
{
    (wc -> hashtable)[hashvalue] = hashcurrent -> hashnext;
}

/*
 * Print the contents of the hash table
 *
 * wc is a pointer to the webcache struct
 */
void printHash(WebCache * wc)
{
    resourcenode * res = NULL;
    unsigned int hashvalue = 0;

    printf(STR_PRINTING_HASH_TABLE);

    for (hashvalue = 0; hashvalue < HASHTABLE_SIZE; hashvalue++)
    {
        res = (wc -> hashtable)[hashvalue];
        if (res != NULL)
        {
            printf("\nhash %d", hashvalue);
            do
            {
                printf("\n%s", res -> name);
                res = res -> hashnext;
            }
            while (res != NULL);
            printf("\n");
        }
    }
}


/*
 * Unlink a node from the hashtable 
 *
 * wc is a pointer to the webcache struct
 * searchnode is a pointer to the resource we want to unlink
 * 
 * Note that this function does not free the resource memory 
 */
void unlinkFromHashTable(WebCache * wc, resourcenode * searchnode)
{
    unsigned int hash = 0;

    resourcenode * hashcurrent = NULL;
    resourcenode * hashprevious = NULL;

    /* Find the resource by hash then check collision chain */
    hashcurrent = getResourceByHash(wc, searchnode -> name, &hash);

    /* If resource at head of hash chain, unlink from head of hash chain */
    if (checkMatch(hashcurrent, searchnode -> name, searchnode -> bytes))
    {
        /* Note hashcurrent == res */
        unlinkFromHeadOfHashChain(wc, hashcurrent, hash);
        return;
    }
    else
    {
        /* If resource in middle of hashchain, search then unlink */
        do
        {
            hashprevious = hashcurrent;
            hashcurrent = hashcurrent -> hashnext;

            if (checkMatch(hashcurrent,
                           searchnode -> name,
                           searchnode -> bytes))
            {
                /* Match found, unlink and return */
                hashprevious -> hashnext = hashcurrent -> hashnext;
                return;
            }
        }
        while(hashcurrent != NULL);
    }
}

/*
 * Insert a resource node into the head of the hash table 
 * 
 * wc is a pointer to the webcache struct
 * newres is a pointer to the resourcenode we want to insert
 * resource is a pointer to the name of the resource we want to insert
 */
void insertIntoHashtableHead (WebCache * wc,
                              resourcenode * newres,
                              char * resource)
{
    resourcenode * hashres = NULL;
    unsigned int hashvalue = 0;

    /* Get the first resource at the start
     * of the hash chain for our resources hash value */
    hashres = getResourceByHash(wc, resource, &hashvalue);

    /* If a hashchain already exists,
     * make the head element the next element */
    if (hashres != NULL)
    {
        newres -> hashnext = hashres;
    }

    /* Insert new resource into the head of the hashchain */
    (wc -> hashtable)[hashvalue] = newres;
}
