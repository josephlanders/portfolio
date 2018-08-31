/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains header code
 * 
 ****************************************************************************/

#include <sys/uio.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <fcntl.h>

/* Load the correct clock library depending on current operating system */
#if defined(sun) || defined (__sun)
#include <sys/time.h>
#endif
#if defined(linux) || defined (__linux)
#include <time.h>
#endif

#define STR_STATS_SEARCH_TOTAL "\nTotal time: %f ms"

/*
 * When saving ints to file we have to check 
 * the endianness of the operating system and byte size
 * 
 * Will just assume that solaris is big endian 32bit for simplicity
 *
 * a is the int passed in to be saved
 *
 * An int is returned
 */
int save2ByteInt(int a);

/*
 * When saving ints to file we have to check 
 * the endianness of the operating system and byte size
 * 
 * Will just assume that solaris is big endian 32bit for simplicity
 *
 * a is the int passed in to be saved
 *
 * An int is returned
 */
int save3ByteInt(int a);


/*
 * When restoring ints from file we have to know
 * the endianness of the operating system and byte size
 * 
 * Will just assume that solaris is big endian 32bit for simplicity
 *
 * a is the int passed in to be saved
 * an int is returned
 */

int restore3ByteInt(int a);

/*
 * When restoring ints from file we have to know
 * the endianness of the operating system and byte size
 * 
 * Will just assume that solaris is big endian 32bit for simplicity
 *
 * a is the int passed in to be saved
 * an int is returned
 */

int restore2ByteInt(int a);

/*
 * Hashes a key
 *
 * id is the id of the record
 * pagesize is the number of records per page
 *
 * Returns a hash
 */
int hashfunction (int id, int pages);

/*
 * Open a file for read
 *
 * rc is a cache of records
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_read(DataStore *b, char * filename);

/*
 * Open a file for binary read 
 *
 * rc is a cache of records
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_read(DataStore *b, char * filename);

/*
 * Open a file for binary write
 *
 * rc is a cache of records
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_write(DataStore *b, char * filename);

/*
 * Open a file for binary read and write
 *
 * rc is a cache of records
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_read_write(DataStore *b, char * filename);

/*
 * Calculate the number of buckets in the database
 *
 * Takes as input the number of records, page size and occupancy percentage
 *
 * returns the number of buckets as an int
 */
int calculateNumberOfBuckets(int numberOfRecords, int pagesize, int occupancy);

/*
 *  Get a search key from an ascii file 
 *   
 *  rs is a pointer to a record cache
 *  fp is a pointer to a file
 * 
 *  returns the key or ERROR to indicate an error
 */
int getKey(DataStore * b, FILE * fp);

/*
 *  Searches records in cache to find a record with matching key
 *   
 *  rs is a pointer to a record cache
 *  key is an integer
 * 
 *  returns the record if found or NULL otherwise
 */
/* record * search_records(DataStore *b, unsigned int key); */

/*
 * Get the number of buckets in the binary file 
 * This is held as the first BUCKET_HEADER_SIZE bytes of the file
 *
 * returns an int
 */
int getNumberOfBuckets(FILE * fp);

/*
 * Reads the number of records in a normal 
 * comma seperated text file 
 * by reading the number of newlines
 *
 * fp a pointer to the open file
 * 
 * returns the number of records/lines
 */
int getNumberOfRecords(FILE * fp);

int hashfunction (int id, int buffers);

int hashfunction2 (int id, int buffers);

Record * read_guild_record(DataStore * d, FILE * fp);

Record * read_character_record(DataStore * d, FILE * fp);

void add_record_to_outer_buffer(DataStore *d, Record * r);

void free_outer_buffer(DataStore *d);

void print_stats(DataStore *d);

void search_guild_buffer_for_character(DataStore *d, FILE * fp2);

void read_guild_records_to_outer_buffer(DataStore *d, FILE * fp);

void search_character_buffer_for_guild(DataStore *d, FILE * fp2);

void read_character_records_to_outer_buffer(DataStore *d, FILE * fp);

int open_file_for_read_solaris(DataStore *b, char * filename);
int open_file_for_write_solaris(DataStore *d, char * filename);
/* signed int open_file_solaris_for_read(DataStore *b, char * filename); */

int ** open_partition_files_for_write(DataStore *d, char * filename, int 
numPartitions);

Record * read_guild_record_solaris(DataStore * d, int fp);

Record * read_character_record_solaris(DataStore * d, int fp);

int read_guild_records_to_outer_buffer_solaris(DataStore *d, int fp);

int read_character_records_to_outer_buffer_solaris(DataStore *d, int fp);

void search_guild_buffer_for_character_solaris(DataStore *d, int fp2);
void search_character_buffer_for_guild_solaris(DataStore *d, int fp2);

void write_character_record_solaris(DataStore *d, int fp, Record * r);
void write_guild_record_solaris(DataStore *d, int fp, Record * r);
