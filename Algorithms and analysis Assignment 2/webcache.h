/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

/* Load the correct clock library depending on current operating system */
#if defined(sun) || defined (__sun)
#include <sys/time.h>
#endif
#if defined(linux) || defined (__linux)
#include <time.h>
#endif

typedef struct WebCache
{
    struct resourcenode * head;
    struct resourcenode * tail;
    struct resourcenode ** hashtable;
    unsigned long int bytesused;
    unsigned long int cachesize;
    unsigned long long int pagehitcount;
    unsigned long long int pagerequestcount;
    unsigned long long int bytehitcount;
    unsigned long long int byterequestcount;
    unsigned long long int itemcount;
    unsigned long long int evictedcount;
    double totalaccesstime;
    double totalinsertiontime;
}
WebCache;

/* Length of a string */
#define STRING_SIZE (int) 1024

/* Size of the hashtable */
#define HASHTABLE_SIZE (int) 512

/*
 * Initialise the webcache struct 
 * 
 * wc is a pointer to the webcache struct.
 * cachesize is the size of the cache.
 * 
 * returns TRUE or FALSE to indicate success or failure of the operation
 */
int WebCacheInit(WebCache *wc, int cachesize);

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
                     int bytes);

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
                   int bytes);

/*
 * Print the contents of the webcache 
 * 
 * wc is a pointer to the webcache struct
 */
void WebCachePrint(WebCache *wc);

/*
 * Print the statistics of the webcache 
 *
 * wc is a pointer to the webcache struct 
 */
void WebCacheDisplayStats(WebCache *wc);

/*
 * Free up the webcache struct memory 
 * 
 * wc is a pointer to the webcache struct 
 * 
 * returns TRUE or FALSE to indicate success or failure
 */
int WebCacheDestroy(WebCache *wc);

#define STR_STATS_PAGE_HIT_COUNT "\n Page hit count %-16llu"
#define STR_STATS_PAGE_REQUEST_COUNT "\n Total resource request count %-16llu"
#define STR_STATS_PAGE_HIT_PERCENT "\n Page hit rate as a percentage %-16llu"
#define STR_STATS_BYTE_HIT_COUNT "\n Byte hit count %-16llu"
#define STR_STATS_BYTE_REQUEST_COUNT "\n Byte request count %-16llu"
#define STR_STATS_BYTE_HIT_PERCENT "\n Byte hit rate %-16llu"
#define STR_STATS_COUNT "\n Count of items currently in cache %-16llu"
#define STR_STATS_EVICTION "\n Count of items evicted from cache\
 since initialisation %-16llu"
#define STR_STATS_ACCESS_TOTAL "\n Total time spent accessing items\
 from cache in milliseconds. %-16f"
#define STR_STATS_ACCESS_AVERAGE "\n Average time spent per item\
 to perform a cache access in milliseconds. %-16f"
#define STR_STATS_INSERTION_TOTAL "\n Total time spent inserting\
 items into the cache in milliseconds. %-16f"
#define STR_STATS_INSERTION_AVERAGE "\n Average time spent per item\
 to perform a cache insertion in milliseconds. %-16f"
#define STR_STATS_DISK_INSERTION_COST "\n Total disk cost for cache misses\
 in ms %-16f" 
#define STR_STATS_DISK_INSERTION_COST_AVG "\n Average disk cost for cache misses\
 in ms %-16f"
