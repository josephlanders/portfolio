/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment.
 * 
 * This file is the back-end code that maintains the record cache.
 ****************************************************************************/

#include "recordcache.h"
#include "NestedLoop.h"


#define INPUT_BUFFER_SIZE (int) 1
#define OUTPUT_BUFFER_SIZE (int) 1
/*
 * Initialise the Buffer struct 
 * 
 * d is a pointer to the DataStore struct.
 * bufferSize is the size of the outerbuffer
 * outer is an int indicating the data type of the output buffer
 * inner is an int indicating the data type of the input buffer
 * joined is an int indicating the data type of the joined buffer
 * 
 * returns TRUE or FALSE to indicate success or failure of the operation
 */
int DataStoreInit(DataStore *d, int bufferSize, enum recordKind outer, enum recordKind inner, enum recordKind joined, int verbose)
{
    int i = 0;
    int outerBufferSize = 0;

    outerBufferSize = bufferSize - OUTPUT_BUFFER_SIZE - INPUT_BUFFER_SIZE;

    d -> innerRecordType = inner;
    d -> outerRecordType = outer;

    /* Malloc pointers to records */
    d -> outerBuffer = (union Record **) malloc(outerBufferSize * sizeof(union Record *));

    /* Zero pointer memory */
    for (i = 0; i < outerBufferSize; i++)
    {
        d -> outerBuffer[i] = NULL;
    }


    d -> outerBufferMaxSize = outerBufferSize;
    d -> outerBufferCount = 0;
    d -> partitionCount = 0;
    d -> verbose = verbose;
    d -> matchCount = 0;
    d -> totalTime = 0;

    /* printf("\nFinished Init, outer buffer size set to %d", d -> outerBufferMaxSize); */

    return TRUE;
}


/*
 * Free up the Buffer struct memory 
 * 
 * b is a pointer to the RecordCache struct 
 * 
 * returns TRUE or FALSE to indicate success or failure
 */
int DataStoreDestroy(DataStore *d)
{
    int i = 0;

    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        if (d -> outerBuffer[i] != NULL)
        {
            free(d -> outerBuffer[i]);
        }
    }
    free (d -> outerBuffer);

    /* printf("\nFinished Destroy"); */
    return TRUE;
}
