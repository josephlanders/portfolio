/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment.
 * 
 * This file is the back-end code that sits between the driver and the ADT
 * specific code.
 ****************************************************************************/

#include "assign.h"
#include "utility.h"
#include "webcache.h"
#include "resourcenode-main.h"
#include "resourcenode-hash.h"
#include "resourcenode-heap.h"

/*
 * Initialise the webcache struct 
 * 
 * wc is a pointer to the webcache struct.
 * cachesize is the size of the cache.
 * 
 * returns TRUE or FALSE to indicate success or failure of the operation
 */
int WebCacheInit(WebCache *wc, unsigned int cachesize)
{
    int i = 0;

    wc -> bytesused = 0;
    wc -> cachesize = cachesize;
    wc -> pagehitcount = 0;
    wc -> pagerequestcount = 0;
    wc -> bytehitcount = 0;
    wc -> byterequestcount = 0;
    wc -> itemcount = 0;
    wc -> evictedcount = 0;
    wc -> totalaccesstime = 0;
    wc -> totalinsertiontime = 0;

    /*
     * Allocate memory for an array of pointers to resources 
     * This means allocate the hash table.
     */
    if ((wc -> hashtable = (resourcenode **)
                           malloc (HASHTABLE_SIZE * sizeof(resourcenode *)))
            == NULL)
    {
        return FALSE;
    }

    /*
     * Not sure if hashtable pointers are set to NULL on malloc
     * so NULL to be safe 
     */
    for (i = 0; i < HASHTABLE_SIZE; i++)
    {
        wc -> hashtable[i] = NULL;
    }

    /*
     * Allocate memory for an array of pointers to resources 
     * This means allocate the heap array 
     */
    if ((wc -> heap = (resourcenode **)
                      malloc (HEAP_INITIALSIZE * sizeof(resourcenode *)))
            == NULL)
    {
        free(wc -> hashtable);
        return FALSE;
    }

    /*
     * Not sure if heap pointers are set to NULL on malloc
     * so NULL to be safe 
     */
    for (i = 0; i < HEAP_INITIALSIZE; i++)
    {
        wc -> heap[i] = NULL;
    }

    return TRUE;
}

/*
 * Get a resource from the webcache 
 * 
 * wc is a pointer to the webcache struct.
 * resource is a pointer to the name of the resource.
 * bytes is the size of the resource.
 * 
 * returns TRUE or FALSE to indicate cache HIT or MISS.
 */
int WebCacheRetrieve(WebCache *wc,
                     char *resource,
                     unsigned int bytes)
{
    /* Stores status of retrieve function call */
    int response = 0;

    /*
     * The following code is system specific
     * 
     * The code retrieves from the webcache and
     * works out how long the operation took, 
     * adding the time to a cumulative count 
     */
#if defined(linux) || defined (__linux)

    clock_t ticks1 = { 0 };
    clock_t ticks2 = { 0 };

    ticks1 = clock();
    ticks2 = ticks1;

    response = retrieveResourceNode(wc, resource, bytes);
    ticks2 = clock();

    wc -> totalaccesstime += (ticks2 - ticks1);

#endif

#if defined(sun) || defined(__sun)

    hrtime_t ticks1 = { 0 };
    hrtime_t ticks2 = { 0 };

    ticks1 = gethrtime();
    ticks2 = ticks1;

    response = retrieveResourceNode(wc, resource, bytes);
    ticks2 = gethrtime();

    wc -> totalaccesstime += (ticks2 - ticks1);

#endif

    return response;


}

/*
 * Insert a resource into the webcache 
 * 
 * wc is a pointer to the webcache struct.
 * resource is a pointer to the name of the resource.
 * bytes is the size of the resource.
 * 
 * returns TRUE or FALSE to indicate success or failure.
 */
int WebCacheInsert(WebCache *wc,
                   char *resource,
                   unsigned int bytes)
{
    int response = 0;

    /*
     * The following code is system specific
     * 
     * The code inserts into the webcache and
     * works out how long the operation took, 
     * adding the time to a cumulative count 
     */
#if defined(linux) || defined (__linux)

    clock_t ticks1 = { 0 };
    clock_t ticks2 = { 0 };

    ticks1 = clock();
    ticks2 = ticks1;

    response = insertResourceNode(wc, resource, bytes);

    ticks2 = clock();

    /* / (CLOCKS_PER_SEC / 1000) needs to be done to convert to millseconds */
    wc -> totalinsertiontime += (ticks2 - ticks1);

#endif

#if defined(sun) || defined(__sun)

    hrtime_t ticks1 = { 0 };
    hrtime_t ticks2 = { 0 };

    ticks1 = gethrtime();
    ticks2 = ticks1;

    response = insertResourceNode(wc, resource, bytes);

    ticks2 = gethrtime();

    wc -> totalinsertiontime += (ticks2 - ticks1);

#endif

    return response;
}

/*
 * Print the contents of the webcache 
 * 
 * wc is a pointer to the webcache struct
 */
void WebCachePrint(WebCache *wc)
{
    /* Iterate through heap printing */
    printNodes(wc);
    return;
}

/* 
 * Gets the total disk cost for inserting 
 * into the webcache on cache misses
 * 
 * wc is the webcache struct
 * 
 * value returned is in ms
 */
double getInsertionTime(WebCache * wc)
{
	double cost = 0;
	unsigned long long int misscount = wc -> pagerequestcount 
	- wc -> pagehitcount;
	
    /* 
     * Calculate seek time in ms
     * 
     * 11ms is the seek time for a single request
     */ 
    cost = (double) 11 * misscount;
    
   
    /* 
     * Calculate Transfer time in ms
     * 
     * 67.5 MB/s (megabytes per second) is the transfer speed 
     */ 
    cost += (wc -> byterequestcount * 1000) 
          / (double) (67.5 * 1024 * 1024);
    
    /* 
     * Calculate rotational delay in ms
     * 
     * 5400 rpm is the disk rotation speed 
     */
    cost += (1 * 60 * 1000 * misscount) / (double) (2 * 5400);
        
    /* 
     * Calculate latency in ms
     * 
     * 5.5 ms is the latency for one file read 
     */ 
    cost += (double) 5.5 * (wc -> pagerequestcount - wc -> pagehitcount);
    
    return cost;
}

/*
 * Print the statistics of the webcache 
 *
 * wc is a pointer to the webcache struct 
 */
void WebCacheDisplayStats(WebCache *wc)
{
    unsigned long long int pagehitrate = 0;
    unsigned long long int bytehitrate = 0;

    double accesstime = wc -> totalaccesstime;
    double insertiontime = wc -> totalinsertiontime;
    
    double diskcost = 0;
    
    diskcost = getInsertionTime(wc);
    

    /* System specific code to calculate correct access and insertion times */
#if defined (__linux) || defined (linux)

    accesstime /= (CLOCKS_PER_SEC / 1000);
    insertiontime /= (CLOCKS_PER_SEC / 1000);
#endif

#if defined (__sun) || defined (sun)

    accesstime /= 1000000;
    insertiontime /= 1000000;
#endif

    /* Calculate page hit rate */
    if (wc -> pagerequestcount != 0)
    {
        pagehitrate = (wc -> pagehitcount * 100) / wc -> pagerequestcount;
    }

    /* Calculate byte hit rate */
    if (wc -> byterequestcount != 0)
    {
        bytehitrate = (unsigned long long int) (wc -> bytehitcount * 100)
                      / wc -> byterequestcount;
    }

    /* Print out relevant statistics */

    printf("\n Printing Statistics");
    printf("\n -------------------");
    printf(STR_STATS_PAGE_HIT_COUNT, wc -> pagehitcount);
    printf(STR_STATS_PAGE_REQUEST_COUNT, wc -> pagerequestcount);
    printf(STR_STATS_PAGE_HIT_PERCENT, pagehitrate);
    printf(STR_STATS_BYTE_HIT_COUNT, wc -> bytehitcount);
    printf(STR_STATS_BYTE_REQUEST_COUNT, wc -> byterequestcount);
    printf(STR_STATS_BYTE_HIT_PERCENT,  bytehitrate);
    printf(STR_STATS_COUNT, wc -> itemcount);
    printf(STR_STATS_EVICTION, wc -> evictedcount);
    printf(STR_STATS_ACCESS_TOTAL,  accesstime);
    printf(STR_STATS_ACCESS_AVERAGE, accesstime
           / wc -> pagerequestcount);
    printf(STR_STATS_INSERTION_TOTAL, insertiontime);
    printf(STR_STATS_INSERTION_AVERAGE, insertiontime
           / (wc -> pagerequestcount - wc -> pagehitcount));
    /* Could also use wc - itemcount + wc - evicted count as divisor */
    
        printf(STR_STATS_DISK_INSERTION_COST, diskcost);
    printf(STR_STATS_DISK_INSERTION_COST_AVG, diskcost 
    / (wc -> pagerequestcount - wc -> pagehitcount));

    printf("\n\n");
    fflush(stdout);
}

/*
 * Free up the webcache struct memory 
 * 
 * wc is a pointer to the webcache struct 
 * 
 * returns TRUE or FALSE to indicate success or failure
 */
int WebCacheDestroy(WebCache *wc)
{
    int i = 0;

    /* For all heap items, de-allocate them from memory */
    for (i = 0; i < wc -> itemcount; i++)
    {
        /* Free the contents of the resource node */
        freeResources(wc -> heap[i]);

        /* Free the resource node */
        free(wc -> heap[i]);
    }

    /* Now we can free the heap */
    free (wc -> heap);

    /* Now we can free the hashtable safely */
    free(wc -> hashtable);

    return TRUE;
}
