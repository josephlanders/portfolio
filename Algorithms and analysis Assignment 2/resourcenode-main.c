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
 * and retrieving resources in a heap.
 ****************************************************************************/

#include "assign.h"
#include "webcache.h"
#include "utility.h"
#include "resourcenode-main.h"
#include "resourcenode-hash.h"
#include "resourcenode-heap.h"

/*
 * Searches for a resource node by name and size 
 *
 * wc is a pointer to the webcache struct
 * resource is a pointer to the name of the resource we are searchign for
 * bytes is the size of the resource
 * 
 * returns TRUE or FALSE to indicate success or fail (hit/miss)
 */
int retrieveResourceNode(WebCache *wc, char *resource, unsigned int bytes)
{
    resourcenode * current = NULL;
    unsigned int heappos = 0;
    unsigned int leftchildpos = 0;
    unsigned int rightchildpos = 0;
    unsigned int parentpos = 0;

    wc -> pagerequestcount++;
    wc -> byterequestcount += bytes;

    current = getResourceByHash2(wc, resource);

    /* If no resource exists in the hashtable
     * then we can return */
    if(current == NULL)
    {
        return FALSE;
    }

    /* Now iterate through the hash chain (resources with same hash)
       and try to find the correct resource */

    /* If the first resource node exists */
    do
    {
        /* Check if the resource name matches the resource we are adding
           and the resource size is a match */
        if (checkMatch(current, resource, bytes))
        {
            if (wc -> pagerequestcount % 50 == 0)
            {
                doublekeys (wc);
            }

            wc -> pagehitcount++;
            wc -> bytehitcount += bytes;

            if (bytes == 0)
            {
                current -> key = 1;
            }
            else
            {
                /* Reset heap key to resource size */
                current -> key = bytes;
            }

            heappos = current -> heapposition;
            parentpos = (heappos - 1) / 2;
            leftchildpos = (heappos * 2) + 1;
            rightchildpos = (heappos * 2) + 2;

            /* Must re-heapify as key out of order */
            /* Check for parents existance */
            if (heappos != 0)
            {
                if ((current -> key >
                        wc -> heap[parentpos] -> key))
                {
                    /* Sift up if current resource key larger than parents */
                    bottomup_heapify(wc, heappos);
                }
            }


            /* If left and right child exist */
            if (rightchildpos < wc -> itemcount)
            {
                /* check if current resource key larger than either child */
                if
                (((current -> key <
                        wc -> heap[leftchildpos] -> key))
                        ||
                        ((current -> key <
                          wc -> heap[rightchildpos] -> key)))
                {
                    /* Sift down if smaller than either of children */
                    topdown_heapify(wc, heappos, wc -> itemcount - 1);
                }
            }
            else if
            /* If only left child exists */
            (leftchildpos < wc -> itemcount)
            {
                /* check if current resource key larger than either child */
                if
                ((current -> key <
                        wc -> heap[leftchildpos] -> key))
                {
                    /* Sift down if resource key
                     * smaller than either of children */
                    topdown_heapify(wc, heappos, wc -> itemcount - 1);
                }
            }

            /* Return success */
            return TRUE;
        }
        /* Could put this into a function to reduce dependency */
        current = current -> hashnext;
    }
    while(current != NULL);
    return FALSE;
}

/*
 * Frees a resource node from memory 
 * 
 * current is a pointer to a pointer to a resource node
 * 
 * (This is to avoid stack corruption)
 */
void freeResourceNode(resourcenode ** current)
{
    /* DO FREEING */
    free((*current) -> name);
    free(*current);
}



void makeSpace(WebCache * wc, unsigned int bytes)
{
    resourcenode * res = NULL;

    /* Remove root node until there is enough space in heap */
    while (wc -> cachesize < (wc -> bytesused + bytes))
    {
        /* Remove head item from Cache, keep pointer so we can dealloc later */
        /* Could make a function call to getRootNode(); */
        res = wc -> heap[0];

        if ((res == NULL)  || (wc -> itemcount == 0))
        {
            printf(STR_CRITICAL_FAILURE);
            fflush(stdout);
            WebCacheDestroy(wc);
            exit(EXIT_SUCCESS);
        }

        /* Unlink it from the queue */
        unlinkFromHeap(wc);

        /* Unlink the resource from the hash table */
        unlinkFromHashTable(wc, res);

        wc -> evictedcount++;
        wc -> itemcount--;
        wc -> bytesused -= res -> bytes;

        /* printf("evicted"); */

        freeResourceNode(&res);
        /* Free the node in memory */
    };
}

/*
 * Inserts a resourcenode into the hashtable and heap 
 * 
 * wc is a pointer to the webcache struct
 * resource is a pointer to the name of the resource we want to insert
 * bytes is the size of the resource we want to insert
 * 
 * returns size of the cache that is in use
 */
int insertResourceNode(WebCache *wc, char *resource, unsigned int bytes)
{
    resourcenode * newres = NULL;

    /* If the item to be inserted is bigger than the cache size */
    if (bytes >= wc -> cachesize)
    {
        /* Automatic cache miss */
        /* Note byte request count was updated in the retrieve function
         * already */
        return wc -> bytesused;
    }

    makeSpace(wc, bytes);

    /* Create resource node and get pointer to it */
    newres = createResourceNode(wc, resource, bytes);

    insertIntoHeap(wc, newres);

    insertIntoHashtableHead(wc, newres, resource);

    wc -> bytesused += bytes;
    wc -> itemcount++;

    return  wc -> bytesused;
}


/*
 * Create a resourcenode and return a pointer to it 
 *
 * wc is a pointer to the webcache struct
 * name is a pointer to the name of the resource we want to create
 * bytes is the size of the resource we want to create
 * 
 * returns a pointer to the new resource node
 */
resourcenode * createResourceNode(WebCache *wc, char *name, unsigned int bytes)
{
    resourcenode * newres = NULL;

    /* Allocate memory for the new resource node */
    if ((newres = malloc(sizeof(resourcenode))) == NULL)
    {
        printf(STR_FATAL);
        printf(STR_RET);
        exit(EXIT_FAILURE);
    }

    /* Keep left and right for ?
    newres -> left = NULL;
    newres -> right = NULL; */
    newres -> hashnext = NULL;
    newres -> bytes = bytes;
    newres -> heapposition = 0;

    /*
     * Set any 0 byte item to a key of 1 
     * so that it will be evicted from the cache at some point 
     */
    if (bytes == 0)
    {
        newres -> key = 1;
    }
    else
    {
        /* Reset heap key to resource size */
        newres -> key = bytes;
    }

    /* Allocate memory for the string */
    if ((newres -> name =
                (char *) malloc(
                    (strlen(name) + EXTRA_SPACES)
                    * sizeof(char))) == NULL)
    {
        printf(STR_FATAL);
        printf(STR_RET);
        exit(EXIT_FAILURE);
    }

    /* Copy the string into the node */
    strcpy(newres -> name, name);

    return newres;
}

/*
 * Iterates through the resource nodes, printing their name 
 *
 * current is a pointer to the first node we want to print 
 */
void printNodes(WebCache * wc)
{
    int i = 0;

    printf("\nListing Resources in Cache (Heap)");
    printf("\n----------------------------------");
    /* Iterates through resource nodes */
    for (i = 0; i < wc -> itemcount; i++)
    {
        /* Prints the name of the resource node */
        printf("\n%s %d", wc -> heap[i] -> name, wc -> heap[i] -> bytes);
    }

    printf("\n\n");
    fflush(stdout);
}

/*
 * Free the resources contents 
 *
 * current is a pointer to the node in question 
 */

void freeResources(resourcenode * current)
{
    /* Free the current node */
    free(current -> name);
}

/*
 * Compares a resource node to a set of criteria and returns the result.
 * 
 * current is a pointer to the resource node.
 * resource is a pointer to the resource name we are looking for.
 * bytes is the size of the resource we are looking for.
 * 
 * returns TRUE or FALSE to indicate status of operation.
 */
int checkMatch(resourcenode * current, char * resource, unsigned int bytes)
{
    if (
        (strcmp(current -> name, resource) == 0)
        && (current -> bytes == bytes)
    )
    {
        return TRUE;
    }
    return FALSE;

}
