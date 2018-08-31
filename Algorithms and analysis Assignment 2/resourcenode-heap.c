/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment.
 * 
 * This code is the data structure code for storing
 * and retrieving resources in a heap and a hashtable.
 ****************************************************************************/

#include "assign.h"
#include "webcache.h"
#include "utility.h"
#include "resourcenode-main.h"
#include "resourcenode-hash.h"
#include "resourcenode-heap.h"

/*
 * Doubles all keys in the heap
 * 
 * wc is a pointer to the webcache struct
 * 
 * 9223372036854775808 is largest signed long long int you can get
 * So largest unsigned long long int is (above - 1) * 2 of that.
 */
void doublekeys (WebCache * wc)
{
    int i = 0;

    for (i = 0; i < wc -> itemcount; i++)
    {
        wc -> heap[i] -> key *= 2;
    }
}

/*
 * Heapify from the bottom to the top
 * 
 * wc is a pointer to the webcache struct
 * k is an int which is the the node we start from in the heap array
 */
void bottomup_heapify(WebCache * wc, int k)
{
    resourcenode * temp = NULL;
    resourcenode ** heap = wc -> heap;

    /*
     * 
     * 0 1 2 3 4 5 6 7 8
     * 
     *               0                               1
     *             /    \                          /    \
     *           1        2                       2      3
     *         /   \    /   \                    /  \   /   \
     *        3     4  5     6                  4    5 6     7       
     * 
     * (child - 1 )/ 2 = parent for my version
     *  child / 2 = parent for the lecture notes
     */

    while ((k > 0) && ((heap[(k - 1) /2] -> key) < (heap[k] -> key)))
    {
        /* Note must change heapposition values */
        heap[(k - 1) / 2] -> heapposition = k;
        heap[k] -> heapposition = (k - 1) / 2;

        /* swap(a[k], a[j]); */
        temp = heap[(k - 1) /2];
        heap[(k - 1) /2]  = heap[k];
        heap[k] = temp;

        k = (k - 1) / 2;
    }
}


/*
 * Insert a resource node into the heap 
 *
 * wc is a pointer to the webcache struct
 * newres is a pointer to the resource node we want to insert 
 */
void insertIntoHeap(WebCache *wc, resourcenode * newres)
{
    /* Find the last node in the heap, insert there */
    wc -> heap[wc -> itemcount] = newres;

    newres -> heapposition = wc -> itemcount;

    /* heapify bottom up to put the heap in order */
    bottomup_heapify(wc, wc -> itemcount);
}

/*
 * Print the contents of the heap
 * 
 * wc is a pointer to the webcache struct 
 */
void printHeap(WebCache * wc)
{
    int i = 0;

    for (i = 0; i < wc -> itemcount; i++)
    {
        printf("\n%d %f Parent %d",i, wc -> heap[i] -> key, (i - 1) / 2);
        if (wc -> heap[i] -> key > wc -> heap[(i - 1) / 2] -> key)
        {
            printf(STR_HEAP_PRINT_ERROR,
                   wc -> heap[(i - 1) / 2] -> name,
                   wc -> heap[(i - 1) / 2] -> key,
                   wc -> heap[i] -> name,
                   wc -> heap[i] -> key);
        }
    }
}

/*
 * Tests the heap is in max-heap order
 * 
 * wc is a pointer to the webcache struct 
 */
void testHeap(WebCache * wc)
{
    int i = 0;
    int error = FALSE;

    printf("\nTesting Heap is a max heap");
    for (i = 0; i < wc -> itemcount; i++)
    {
        if (wc -> heap[i] -> key > wc -> heap[(i - 1) / 2] -> key)
        {
            printf(STR_HEAP_PRINT_ERROR,
                   wc -> heap[(i - 1) / 2] -> name,
                   wc -> heap[(i - 1) / 2] -> key,
                   wc -> heap[i] -> name,
                   wc -> heap[i] -> key);
            error = TRUE;
        }
    }
    if (error == FALSE)
    {
        printf("\nPASS - Heap is a max heap");
    }
    else
    {
        printf("\nFAIL - Heap is not a max heap");
    }
}

/*
 * Heapify from the root downwards
 * 
 * wc is a pointer to a webcache struct
 * k is the node to start at
 * N is the number of elements in the heap array
 */
void topdown_heapify(WebCache * wc, int k, int N)
{
    resourcenode * temp = NULL;
    resourcenode ** heap = wc -> heap;

    int j = 0;

    /* printf("\nTop down called"); */
    /* pause(); */

    if (N == 0)
    {
        return;
    };

    /*    0    1 2
       0    1 2      3 4 5 6      7 8 9 10 11 12 13 14 
        
        
                  0
                /   \
              1      2
            /
             */

    while ((2 * k) + 1 <= N)
    {
        j = (2 * k) + 1;
        if ((j < N) && ((heap[j] -> key) < (heap[j + 1] -> key)))
        {
            j++;
        }

        if ((heap[j] -> key) < (heap[k] -> key))
        {
            break;
        }

        /* Note must change heapposition values */
        heap[k] -> heapposition = j;
        heap[j] -> heapposition = k;

        /* swap(a[k], a[j]); */
        temp = heap[k];
        heap[k]  = heap[j];
        heap[j] = temp;

        k = j;
    }
}

/*
 * Unlinks a node from the heap
 * 
 * wc is a pointer to the webcache struct
 * 
 * Note: Does not free the memory
 */
void unlinkFromHeap(WebCache * wc)
{
    /* Delete root node */
    /* Replace with the correct (very last node in last level of tree/array) */
    /* Heapify top down */


    if (wc -> itemcount != 0)
    {
        /* Set the root node to the last node in the queue */
        wc -> heap[0] = wc -> heap[wc -> itemcount - 1];

        fflush(stdout);
        if (wc -> itemcount > 1)
        {
            topdown_heapify(wc, 0, wc -> itemcount - 1);
        }

    }
    else
    {
        wc -> heap[0] = NULL;
    }


}
