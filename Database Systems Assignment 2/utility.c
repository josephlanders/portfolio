/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment
 * 
 * Utility file contains code used in multiple source files
 ****************************************************************************/

#include "NestedLoop.h"
#include "recordcache.h"
#include "utility.h"
#include <math.h>

/*
 * Open a file for read
 *
 * b is the DataStore struct
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_read(DataStore *b, char * filename)
{
    /* File pointer to open file */
    FILE * fp = NULL;

    /* Open the file as ascii or die */
    if ((fp = fopen(filename, "r")) == NULL)
    {
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 *  Opens a set of files with filename based on filename passed
 *  and number of files equal to numPartitions
 * 
 * b is the DataStore struct
 * filename is the name of the file
 */
int ** open_partition_files_for_write(DataStore *d, char * filename, int numPartitions)
{
    unsigned int i = 0;

    int ** fp = NULL;

    char newfilename[100] = "";

    /* Allocate memory for array of file pointers */
    fp = malloc(numPartitions * sizeof(int *));

    /* Allocate array of file pointers */
    for (i = 0; i < numPartitions; i++)
    {
        fp[i] = malloc(sizeof(int));
    }

    /* Open files and give them the filename of the original with extension 001 002 etc */
    for (i = 0; i < numPartitions; i++)
    {
        sprintf(newfilename, "scratch//%s%d", filename, i);
        *(fp[i]) = open_file_for_write_solaris(d, newfilename);
    }

    return fp;
}

/*
 *  This function uses Solaris open command not Fopen
 * 
 *  Open a file for write using open
 * 
 * b is the DataStore struct
 * filename is the name of the file
 */
signed int open_file_for_write_solaris(DataStore *b, char * filename)
{
    signed int fp = -1;

    if ((fp = open(filename, O_TRUNC | O_CREAT | O_WRONLY, 0755)) == -1)
    {
        printf("\nError opening file for write");
        printf("\nPlease make sure you have a \"scratch\" subfolder\n");
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 *  This function uses Solaris open command not Fopen
 * 
 *  Open a file for read using open
 * 
 * b is the DataStore struct
 * filename is the name of the file
 */
signed int open_file_for_read_solaris(DataStore *b, char * filename)
{
    signed int fp = -1;

    /* Open the file as ascii or die */
    if ((fp = open(filename,  O_RDONLY)) ==
            -1)
    {
        printf("\nError opening file for read");
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 * Open a file for binary read 
 *
 * b is the DataStore struct
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_read(DataStore *b, char * filename)
{
    /* File pointer to open file */
    FILE * fp = NULL;

    /* Open the file as binary or die */
    if ((fp = fopen(filename, "rb")) == NULL)
    {
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 * Open a file for binary write
 *
 * b is the DataStore struct
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_write(DataStore *b, char * filename)
{
    /* File pointer to open file */
    FILE * fp = NULL;

    /* Open the file as binary or die */
    if ((fp = fopen(filename, "wb")) == NULL)
    {
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 * Open a file for binary read and write
 *
 * b is the DataStore struct
 * filename is the name of the file to open
 *
 * Returns a file pointer 
 */
FILE * open_file_for_binary_read_write(DataStore *b, char * filename)
{
    /* File pointer to open file */
    FILE * fp = NULL;

    /* Open the file as binary or die */
    if ((fp = fopen(filename, "wb+")) == NULL)
    {
        printf(STR_FILE_ERROR);
        DataStoreDestroy(b	);
        exit(EXIT_FAILURE);
    }

    return fp;
}

/*
 * FUNCTION NOT USED 
 *   
 * b is the DataStore struct
 * fp is a pointer to a file
 * 
 *  returns the key or ERROR to indicate an error
 */
int getKey(DataStore * b, FILE * fp)
{
    char line[LINE_SIZE] = "";

    while (!feof(fp))
    {
        /* Terminate on empty line (eof) */
        if (fgets(line, LINE_SIZE, fp) == NULL)
        {
            break;
        }
        return atoi(line);
    }

    /* Error condition */
    return ERROR;
}

/*
 * Hash function used with the guildID
 * 
 * id is the guildID
 * buffers is the size of the hash table
 * 
 * returns the hash
 */
int hashfunction (int id, int buffers)
{
   return (((438439 * id) + 34723753) % 376307) % buffers;  
}

/*
 * Hash function used with the guildID
 * 
 * id is the guildID
 * buffers is the size of the hash table
 * 
 * returns the hash
 */
int hashfunction2 (int id, int buffers)
{
/*
  char string[100]; 

  sprintf(string, "\n%d", id);

  int i = 0;
  unsigned int hash = 0;
  for (i = 0; i < strlen(string); i++)
  {
     hash = hash ^ (string[i] + (hash << 6) + (hash >> 2));
  }
  hash = hash % buffers;
  return hash; 
*/
  return id % buffers;
}

/*
 * Read the guild record using "read" not "fread"
 * 
 * d is the DataStore struct
 * fp is the file pointer to the guild file
 * 
 * Returns the record 
 */
Record * read_guild_record_solaris(DataStore * d, int fp)
{
    char line[LINE_SIZE] = "";

    int i = 0;

    char * guildIDString = NULL;
    char * guildNameString = NULL;

    unsigned int guildID = 0;

    Record * r = NULL;

    /* Allocate memory for the record */
    r = malloc(sizeof(Record));

    /* sizeof(char)? should equal 1 for solaris
     * this code is slightly buggy if ported */

    /* Read characters into our memory buffer until EOF
     * or until one line has been read */
    while (read(fp, &(line[i]), sizeof(char)) > 0)
    {
        i++;
        /* If we detect a new line we must append the
         * String terminating character \0 so that we can
         * tokenise the string later */
        if ((line[i - 1] == '\n') || (line[i- 1] == '\r'))
        {
            line[i - 1] = '\0';
            break;
        }

    };

    /*
     * If we read 0 bytes into our buffer, this means
     * we reached EOF and we didn't read in a record 
     */
    if (i == 0)
    {
        free(r);
        return NULL;
    }

    /* Tokenise string */
    if ((guildIDString = strtok (line, ",")) == NULL)
    {
        free(r);
        return NULL;
    }
    if ((guildNameString = strtok (NULL, ",")) == NULL)
    {
        free(r);
        return NULL;
    }

    guildID = atoi (guildIDString);

    /* Store tokens in record */
    (*r).guild.guildID = guildID;
    strcpy((*r).guild.name, guildNameString);

    return r;
}

/*
 * Read the guild record using "fread" 
 * 
 * d is the DataStore struct
 * fp is the file pointer to the guild file
 * 
 * Returns the record 
 */
Record * read_guild_record(DataStore * d, FILE * fp)
{
    char line[LINE_SIZE] = "";

    char * guildIDString;
    char * guildNameString;

    unsigned int guildID = 0;

    Record * r = NULL;

    /* Allocate memory for the record */
    r = malloc(sizeof(Record));

    /* Do until EOF */
    if (!feof(fp))
    {
        /* Read line */
        fgets(line, LINE_SIZE, fp);

        /* Tokenize line */
        if ((guildIDString = strtok (line, ",")) == NULL)
        {
            free(r);
            return NULL;
        }
        if ((guildNameString = strtok (NULL, ",")) == NULL)
        {
            free(r);
            return NULL;
        }

        guildID = atoi (guildIDString);

        /* Put data into record */
        (*r).guild.guildID = guildID;
        strcpy((*r).guild.name, guildNameString);
        return r;
    }
    free(r);
    return NULL;
}

/*
 * Read the character record using "read" not "fread"
 * 
 * d is the DataStore struct
 * fp is the file pointer to the guild file
 * 
 * Returns the record 
 */
Record * read_character_record_solaris(DataStore * d, int fp)
{

    char line[LINE_SIZE] = "";

    int i = 0;

    char * charNameString = NULL;
    char * charRaceString = NULL;
    char * charClassString = NULL;
    char * charIDString = NULL;
    char * charGuildIDString = NULL;

    int guildID = 0;
    int race = 0;
    int class = 0;
    int ID = 0;


    Record * r = malloc(sizeof(Record));

    /* Read characters into our memory buffer until EOF
     * or until one line has been read */
    while (read(fp, &(line[i]), 1) > 0)
    {
        i++;
        /* If we detect a new line we must append the
         * String terminating character \0 so that we can
         * tokenise the string later */
        if ((line[i - 1] == '\n') || (line[i - 1] == '\r'))
        {
            line[i - 1] = '\0';
            break;
        }
    };

    /*
     * If we read 0 bytes into our buffer, this means
     * we reached EOF and we didn't read in a record 
     */
    if (i == 0)
    {
        free(r);
        return NULL;
    }

    /* Tokenize line */
    if ((charNameString = strtok (line, ",")) == NULL)
    {
        free(r);
        return NULL;
    }
    if ((charRaceString = strtok (NULL, ",")) == NULL)
    {
        free(r);
        return NULL;
    }
    if ((charClassString = strtok (NULL, ",")) == NULL)
    {
        free(r);
        return NULL;
    }
    if ((charIDString = strtok (NULL, ",")) == NULL)
    {
        free(r);
        return NULL;
    }
    if ((charGuildIDString = strtok (NULL, ",")) == NULL)
    {
        free(r);
        return NULL;
    }

    race = atoi (charRaceString);
    class = atoi (charClassString);
    ID = atoi (charIDString);
    guildID = atoi (charGuildIDString);

    strcpy((*r).character.name, charNameString);
    (*r).character.race = race;
    (*r).character.aclass = class;
    (*r).character.id = ID;
    (*r).character.guildID = guildID;

    return r;
}


/*
 * Read the character record using "fread"
 * 
 * d is the DataStore struct
 * fp is the file pointer to the guild file
 * 
 * Returns the record 
 */
Record * read_character_record(DataStore * d, FILE * fp)
{
    char line[LINE_SIZE] = "";

    char * charNameString = NULL;
    char * charRaceString =  NULL;
    char * charClassString = NULL;
    char * charIDString = NULL;
    char * charGuildIDString = NULL;

    int guildID = 0;
    int race = 0;
    int class = 0;
    int ID = 0;

    Record * r = NULL;

    /* Allocate memory for the record */
    r = malloc(sizeof(Record));

    if (!feof(fp))
    {
        /* read line */
        fgets(line, LINE_SIZE, fp);

        /*Tokenise line */
        if ((charNameString = strtok (line, ",")) == NULL)
        {
            free(r);
            return NULL;
        }
        if ((charRaceString = strtok (NULL, ",")) == NULL)
        {
            free(r);
            return NULL;
        }
        if ((charClassString = strtok (NULL, ",")) == NULL)
        {
            free(r);
            return NULL;
        }
        if ((charIDString = strtok (NULL, ",")) == NULL)
        {
            free(r);
            return NULL;
        }
        if ((charGuildIDString = strtok (NULL, ",")) == NULL)
        {
            free(r);
            return NULL;
        }

        race = atoi (charRaceString);
        class = atoi (charClassString);
        ID = atoi (charIDString);
        guildID = atoi (charGuildIDString);

        strcpy((*r).character.name, charNameString);
        (*r).character.race = race;
        (*r).character.aclass = class;
        (*r).character.id = ID;
        (*r).character.guildID = guildID;

        return r;
    }
    free(r);
    return NULL;
}


/*
 * Add any type of record to the outer buffer
 * 
 * d is the DataStore struct
 * r is the record
 * 
 */
void add_record_to_outer_buffer(DataStore *d, Record * r)
{
    unsigned int guildID = 0;
    unsigned int hash = 0;

    /* Offset in hash table */
    unsigned int offset = 0;
    unsigned int i = 0;

    /*
     * Get the guildID from the record 
     * We need the guildID so that we can hash 
     * the record
     */
    if (d -> outerRecordType == guild)
    {
        guildID = r -> guild.guildID;
    }
    else if (d -> outerRecordType == character)
    {
        guildID = r -> character.guildID;
    }

    /* Hash the guild ID */
    hash = hashfunction (guildID, d -> outerBufferMaxSize);

    /* The offset is initially the hash value */
    offset = hash;
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        if (((d -> outerBuffer)[offset]) == NULL)
        {
            /* Insert record here */
            (d -> outerBuffer)[offset] = r;
            break;
        }
        else
        {
            /* Need to find the next slot, increment offset */
            if (offset < (d -> outerBufferMaxSize) - 1)
            {
                offset++;
            }
            else
            {
                /* If no more slots go to start of hashtable */
                offset = 0;
            }
        }
    };
}

/*
 * Free the outer buffer
 * 
 * d is the DataStore struct
 */
void free_outer_buffer(DataStore *d)
{
    unsigned int i = 0;
    unsigned int size = d -> outerBufferMaxSize;

    for (i = 0; i < size; i++)
    {
        if ((d -> outerBuffer)[i] != NULL)
        {
            free((d -> outerBuffer)[i]);
            d -> outerBuffer[i] = NULL;
        }
    }
}

/*
 * Print out some statistics to the console
 * 
 * d is the DataStore struct
 */
void print_stats(DataStore *d)
{
   double searchtime = d -> totalTime;
	    
	/* System specific code to calculate correct access and insertion times */
#if defined (__linux) || defined (linux)

    searchtime /= (CLOCKS_PER_SEC / 1000);
#endif

#if defined (__sun) || defined (sun)

    searchtime /= 1000000;
#endif

    printf("\nNumber of matching Tuples %d", d -> matchCount);
    printf(STR_STATS_SEARCH_TOTAL, searchtime);
}

/*
 * Read all character records (one at a time) and match against
 * the hashtable of guilds in memory 
 * 
 * d is the DataStore struct
 * fp2 is the file pointer of the open character file
 */
void search_guild_buffer_for_character(DataStore *d, FILE * fp2)
{
    union Record * characterRecord = NULL;
    union Record * bufferRecord = NULL;

    int charHash = 0;
    int offset = 0;
    int i = 0;

    /* Go to start of file */
    rewind(fp2);

    /* Read in entire file and try to find a match */
    while (!feof(fp2))
    {
        /* Read record */
        if ((characterRecord = read_character_record(d, fp2)) == NULL)
        {
            /* END OF FILE, stop reading the file */
            break;
        }

        /* Hash the character record */
        charHash = hashfunction(characterRecord -> character.guildID, d -> outerBufferMaxSize);

        offset = charHash;

        /* Try to find the same hash in the buffer
         * (record with same guildID) */
        for (i = 0; i < d -> outerBufferMaxSize; i++)
        {
            bufferRecord = d -> outerBuffer[offset];
            if (bufferRecord != NULL)
            {
                /* If we find a match then stop searching
                 * the buffer (until we read another character record in)
                 * else continue
                 * until we reach an empty space 
                 * or we have read the entire buffer 
                 * and not found a match */
                if ((*bufferRecord).guild.guildID ==
                        (*characterRecord).character.guildID)
                {
                    /* Output the tuple if -d set */
                    d -> matchCount++;
                    if (d -> verbose == TRUE)
                    {
                        printf("\n%d MATCHED %s guildID %d WITH %d %s",
                               d -> matchCount,
                               (*characterRecord).character.name,
                               (*characterRecord).character.guildID,
                               (*bufferRecord).guild.guildID,
                               (*bufferRecord).guild.name);
                    }
                    break;
                } 
            } else {
               break;
            }
            if (offset < (d -> outerBufferMaxSize) - 1)
            {
                offset++;
            }
            else
            {
                offset = 0;
            }
        }

        free(characterRecord);
        characterRecord = NULL;
    }
}


/*
 * Read in guild records into the outer buffer
 * 
 * d is the DataStore struct
 * 
 * fp is the file pointer 
 */
void read_guild_records_to_outer_buffer(DataStore *d, FILE * fp)
{
    int i = 0;

    union Record * guildRecord = NULL;

    /* Read in bufferSize (-2) number of records to outer buffer */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        /* Read in a single record */
        if ((guildRecord = read_guild_record(d, fp)) == NULL)
        {
            break;
        }

        /* Add record to outer buffer */
        add_record_to_outer_buffer(d, guildRecord);
    }
}




/*
 * Read all guild records (one at a time) and match against
 * the hashtable of characters in memory 
 * 
 * d is the DataStore struct
 * fp2 is the file pointer of the open guilds file
 */
void search_character_buffer_for_guild(DataStore *d, FILE * fp2)
{
    union Record * guildRecord = NULL;
    union Record * bufferRecord = NULL;

    int guildHash = 0;
    int found = FALSE;
    int offset = 0;
    int i = 0;

    rewind(fp2);



    /* Read in entire file and try to find a match */
    while (!feof(fp2))
    {
        if ((guildRecord = read_guild_record(d, fp2)) == NULL)
        {
            /* END OF FILE, stop reading file */
            break;
        }

        /* Try to find a match of outer buffer records to characterRecord by hashing */
        guildHash = hashfunction(guildRecord -> guild.guildID, d -> outerBufferMaxSize);

        found = FALSE;
        offset = guildHash;
        /* Try to find the same hash in the buffer
        * (record with same guildID) */
        for (i = 0; i < d -> outerBufferMaxSize; i++)
        {

            bufferRecord = d -> outerBuffer[offset];
            if (bufferRecord != NULL)
            {
                /* If we find a match then stop searching
                * the buffer (until we read another character record in)
                * else continue
                * until we reach an empty space 
                * or we have read the entire buffer 
                * and not found a match */
                if ((*bufferRecord).character.guildID
                        == (*guildRecord).guild.guildID)
                {
                    found = TRUE;
                    /* Output the tuple if -d set */
                    d -> matchCount++;
                    if (d -> verbose == TRUE)
                    {
                        printf("\n%d MATCHED %s guildID %d WITH %d %s",
                               d -> matchCount,
                               (*guildRecord).guild.name,
                               (*guildRecord).guild.guildID,
                               (*bufferRecord).character.guildID,
                               (*bufferRecord).character.name);
                    }
                    /* break; DO NOT BREAK for this one */
                }
            }
            if (offset < (d -> outerBufferMaxSize) - 1)
            {
                offset++;
            }
            else
            {
                offset = 0;
            }
        }

        free(guildRecord);
        guildRecord = NULL;
    }
}

/*
 * Read in character records into the outer buffer
 * 
 * d is the DataStore struct
 * 
 * fp is the file pointer 
 */
void read_character_records_to_outer_buffer(DataStore *d, FILE * fp)
{
    int i = 0;

    union Record * characterRecord = NULL;

    /* Read in bufferSize (-2) number of records to outer buffer */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
    	/* Read a signle character record */
        if ((characterRecord = read_character_record(d, fp)) == NULL)
        {
            break;
        }

        /* Add record to outer buffer */
        add_record_to_outer_buffer(d, characterRecord);
    }
}


/*
 * Read in guild records into the outer buffer
 * Uses read instead of fgets
 * 
 * d is the DataStore struct
 * 
 * fp is the file pointer 
 */
int read_guild_records_to_outer_buffer_solaris(DataStore *d, int fp)
{
    int i = 0;

    union Record * guildRecord = NULL;

    /* Read in bufferSize (-2) number of records to outer buffer */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        /* A single guild record */
        if ((guildRecord = read_guild_record_solaris(d, fp)) == NULL)
        {
            if (i == 0)
            {
                return FALSE;
            }
            return TRUE;
        }

        /* Add record to outer buffer */
        add_record_to_outer_buffer(d, guildRecord);
    }

    return TRUE;
}


/*
 * Read in character records into the outer buffer
 * Uses read instead of fgets
 * 
 * d is the DataStore struct
 * 
 * fp is the file pointer 
 */
int read_character_records_to_outer_buffer_solaris(DataStore *d, int fp)
{
    int i = 0;

    union Record * characterRecord = NULL;

    /* Read in bufferSize (-2) number of records to outer buffer */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
    	/* A single chbaracter record */
        if ((characterRecord = read_character_record_solaris(d, fp)) == NULL)
        {
            if (i == 0)
            {
                return FALSE;
            }
            return TRUE;
        }

        /* Add record to outer buffer */
        add_record_to_outer_buffer(d, characterRecord);
    }
    return TRUE;
}


/*
 * Read all character records (one at a time) and match against
 * the hashtable of guilds in memory
 * 
 * Uses a function that uses solaris commands
 * Could optimise this with function pointer 
 * 
 * d is the DataStore struct
 * fp2 is the file pointer of the open guilds file
 */
void search_guild_buffer_for_character_solaris(DataStore *d, int fp2)
{
    union Record * characterRecord = NULL;
    union Record * bufferRecord = NULL;

    int charHash = 0;
    int offset = 0;
    int i = 0;

    lseek(fp2, SEEK_SET, 0);

    /* Read in entire file and try to find a match */
    while (1)
    {
        if ((characterRecord = read_character_record_solaris(d, fp2)) == NULL)
        {
            /* END OF FILE TERMINATE THIS FUNCTION */
            break;
        }

        /* Try to find a match of outer buffer records to characterRecord by hashing */
        charHash = hashfunction(characterRecord -> character.guildID, d -> outerBufferMaxSize);

        offset = charHash;
        for (i = 0; i < d -> outerBufferMaxSize; i++)
        {
            bufferRecord = d -> outerBuffer[offset];
            if (bufferRecord != NULL)
            {                
                /* If we find a record in the hash slot see if it matches the 
                 * record we are searching for */
                if ((*bufferRecord).guild.guildID 
                == (*characterRecord).character.guildID)
                {
                    /* Output the tuple if -d set */
                    d -> matchCount++;
                    if (d -> verbose == TRUE)
                    {
                        printf("\n%d MATCHED %s guildID %d WITH %d %s", d -> matchCount, (*characterRecord).character.name,
                               (*characterRecord).character.guildID, (*bufferRecord).guild.guildID, (*bufferRecord).guild.name);
                    }
                    break;
                } 
            /* Note we can stop on an empty slot for this relationship 
               but not the other way around */
            } else {
                break;
            }
            if (offset < (d -> outerBufferMaxSize) - 1)
            {
                offset++;
            }
            else
            {
                offset = 0;
            }
        }

        free(characterRecord);
        characterRecord = NULL;
    }
}


/*
 * Read all guild records (one at a time) and match against
 * the hashtable of characters in memory
 * 
 * Uses a function that uses solaris commands
 * Could optimise this with function pointer 
 * 
 * d is the DataStore struct
 * fp2 is the file pointer of the open guilds file
 */
void search_character_buffer_for_guild_solaris(DataStore *d, int fp2)
{
    union Record * guildRecord = NULL;
    union Record * bufferRecord = NULL;

    int guildHash = 0;
    int offset = 0;
    int i = 0;

    lseek(fp2, SEEK_SET, 0);

    /* Read in entire file and try to find a match */
    while (1)
    {
        if ((guildRecord = read_guild_record_solaris(d, fp2)) == NULL)
        {
            /* END OF FILE TERMINATE THIS FUNCTION */
            break;
        }

        /* Try to find a match of outer buffer records to characterRecord by hashing */
        guildHash = hashfunction(guildRecord -> guild.guildID, d -> outerBufferMaxSize);

        offset = guildHash;
        for (i = 0; i < d -> outerBufferMaxSize; i++)
        {
            bufferRecord = d -> outerBuffer[offset];
            if (bufferRecord != NULL)
            {
            	/* if we find a record see if it matches our search guildID */
                if ((*bufferRecord).character.guildID 
                == (*guildRecord).guild.guildID)
                {
                    /* Output the tuple if -d set */
                    d -> matchCount++;
                    if (d -> verbose == TRUE)
                    {
                        printf("\n%d MATCHED %s guildID %d WITH %d %s",
                               d -> matchCount,
                               (*guildRecord).guild.name,
                               (*guildRecord).guild.guildID,
                               (*bufferRecord).character.guildID,
                               (*bufferRecord).character.name);
                    }
                    /* break; DO NOT BREAK for this one */
                }
            }
            if (offset < (d -> outerBufferMaxSize) - 1)
            {
                offset++;
            }
            else
            {
                offset = 0;
            }
        }

        free(guildRecord);
        guildRecord = NULL;
    }
}


/*
 * Write a character record to a file using solaris "write" 
 * 
 * d is the DataStore struct
 * fp is the file pointer to the opened file
 * r is the record to write 
 */
void write_character_record_solaris(DataStore *d, int fp, Record * r)
{
    int len = strlen((*r).character.name);

    char race[100] = "";
    char class[100] = "";
    char id[100] = "";
    char guildID[100] = "";

    sprintf(race, "%d", (*r).character.race);
    sprintf(class, "%d", (*r).character.aclass);
    sprintf(id, "%d", (*r).character.id);
    sprintf(guildID, "%d", (*r).character.guildID);

    write(fp, (*r).character.name, len);
    write(fp, ",", sizeof(char));
    write(fp, race, strlen(race));
    write(fp, ",", sizeof(char));
    write (fp, class, strlen(class));
    write(fp, ",", sizeof(char));
    write(fp, id, strlen(id));
    write(fp, ",", sizeof(char));
    write(fp, guildID, strlen(guildID));
    write(fp, "\n", sizeof(char));
}

/*
 * Write a guild record to a file using solaris "write" 
 * 
 * d is the DataStore struct
 * fp is the file pointer to the opened file
 * r is the record to write 
 */
void write_guild_record_solaris(DataStore *d, int fp, Record * r)
{
    int len = strlen((*r).guild.name);

    char guildID[100] = "";

    sprintf(guildID, "%d", (*r).guild.guildID);

    write (fp, guildID, strlen(guildID));
    write (fp, ",", sizeof(char));
    write (fp, (*r).guild.name, len);
    write (fp, "\n", sizeof(char));
}

