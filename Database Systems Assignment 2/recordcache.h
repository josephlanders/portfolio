/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
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

enum recordKind { character, guild, joined };

typedef struct characterRecord
{
    char name[38];
    unsigned int race;
    unsigned int aclass;
    unsigned int id;
    unsigned int guildID;
}
characterRecord;

typedef struct guildRecord
{
    unsigned int guildID;
    char name[14];
}
guildRecord;

typedef struct joinedRecord
{
    char name[38];
    unsigned int race;
    unsigned int aclass;
    unsigned int id;
    unsigned int guildID;
    char guildName[14];
}
joinedRecord;

typedef union Record {
    struct characterRecord character;
    struct guildRecord guild;
    struct joinedRecord joined;
}
Record;


typedef struct DataStore
{
    enum recordKind innerRecordType;
    enum recordKind outerRecordType;
    unsigned int outerBufferMaxSize;
    unsigned int outerBufferCount;
    union Record joinedBuffer;
    union Record innerBuffer;
    union Record ** outerBuffer;


    int matchCount;
    int verbose;
    int partitionCount;
    double totalTime;

}
DataStore;

/* Length of a string */
#define STRING_SIZE (int) 1024

/*
 * Initialise the Buffer struct 
 * 
 * b is a pointer to the Buffer struct.
 * bufferSize is the Buffer Size.
 * 
 * returns TRUE or FALSE to indicate success or failure of the operation
 */
int DataStoreInit(DataStore *d, int bufferSize, enum recordKind outer, enum recordKind inner, enum recordKind joined, int verbose);

/*
 * Free up the Buffer struct memory 
 * 
 * rc is a pointer to the Buffer struct 
 * 
 * returns TRUE or FALSE to indicate success or failure
 */
int DataStoreDestroy(DataStore *d);


