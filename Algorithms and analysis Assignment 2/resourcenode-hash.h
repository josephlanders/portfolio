/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#define STR_PRINTING_HASH_TABLE "\n\nPRINTING HASH TABLE"

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
                                 unsigned int * hashvalue);

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
resourcenode * getResourceByHash2(WebCache * wc, char * resource);

/*
 * Unlink a resource node from the head of the hash chain 
 * 
 * wc is a pointer to the webcache struct
 * 
 * Note that this function does not free the resources memory
 */
void unlinkFromHeadOfHashChain(WebCache *wc,
                               resourcenode * hashcurrent,
                               unsigned int hashvalue);

/*
 * Print the contents of the hash table
 *
 * wc is a pointer to the webcache struct
 */
void printHash(WebCache * wc);

/*
 * Unlink a node from the hashtable 
 *
 * wc is a pointer to the webcache struct
 * searchnode is a pointer to the resource we want to unlink
 * 
 * Note that this function does not free the resource memory 
 */
void unlinkFromHashTable(WebCache * wc, resourcenode * searchnode);

/*
 * Insert a resource node into the head of the hash table 
 * 
 * wc is a pointer to the webcache struct
 * newres is a pointer to the resourcenode we want to insert
 * resource is a pointer to the name of the resource we want to insert
 */
void insertIntoHashtableHead (WebCache * wc,
                              resourcenode * newres,
                              char * resource);
