/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#define GREATERTHAN (int) 2
#define LESSTHAN (int) 1
#define EQUAL (int) 0

#define STR_HEAP_PRINT_ERROR "\nError KEYS in wrong order for\
 parent: %s key: %f child %s key: %f"

/*
 * Doubles all keys in the heap
 * 
 * wc is a pointer to the webcache struct
 */
void doublekeys (WebCache * wc);

/*
 * Heapify from the bottom to the top
 * 
 * wc is a pointer to the webcache struct
 * k is an int which is the the node we start from in the heap array
 */
void bottomup_heapify(WebCache * wc, int k);

/*
* Insert a resource node into the heap 
*
* wc is a pointer to the webcache struct
* newres is a pointer to the resource node we want to insert 
*/
void insertIntoHeap(WebCache *wc, resourcenode * newres);

/*
 * Print the contents of the heap
 * 
 * wc is a pointer to the webcache struct 
 */
void printHeap(WebCache * wc);

/*
 * Tests the heap is in max-heap order
 * 
 * wc is a pointer to the webcache struct 
 */
void testHeap(WebCache * wc);

/*
 * Heapify from the root downwards
 * 
 * wc is a pointer to a webcache struct
 * k is the node to start at
 * N is the number of elements in the heap array
 */
void topdown_heapify(WebCache * wc, int k, int N);

/*
 * Unlinks a node from the heap
 * 
 * wc is a pointer to the webcache struct
 * 
 * Note: Does not free the memory
 */
void unlinkFromHeap(WebCache * wc);
