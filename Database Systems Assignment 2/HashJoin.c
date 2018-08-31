/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment
 * 
 * Joins two data files using the Hash Join method.
 ****************************************************************************/

#include "HashJoin.h"
#include "recordcache.h"
#include "utility.h"

/*
 * Partition guilds and characters according to hash
 *
 * d is the DataStore struct
 */         
void partition_character_guild(DataStore *d)
{
    /* Pointer to data file */
    int fp0 = 0;

    /* Pointer to partition files */
    int ** fp = NULL;

    /* Records */
    Record * guildRecord = NULL;
    Record * characterRecord = NULL;

    /* Hashes */
    int guildHash = 0;
    int charHash = 0;
    int i = 0;

    int zero = 0;

    int count[d -> outerBufferMaxSize];

    /* *** Partitioning Phase ***
    * Open output files for this partition 
    * Read record from first database into r 
    * Hash record 
    * Write record to output file given by fp[hash] 
       * Write count to start of each partition 
       * Free r 
       * Do this until end of file 
       * Close all output files 
       * Now repeat for second data base */

    fp0 = open_file_for_read_solaris(d, "characters");

    fp = open_partition_files_for_write(d, "character", d -> outerBufferMaxSize);

    /* Zero the record count on each file */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        write(*(fp[i]), &zero, sizeof(int));
        count[i] = 0;
    }

    /* Do until EOF */
    while (1)
    {
        /* Read record from file */
        if ((characterRecord = read_character_record_solaris(d, fp0)) == NULL)
        {
            /* END OF FILE stop reading file */
            break;
        }

        /* Hash record */
        charHash = hashfunction2((*characterRecord).character.guildID, d -> outerBufferMaxSize);

        /* Increment record count for records with that Hash */
        count[charHash]++;

        /* Write to the file with that Hash */
        write_character_record_solaris(d, *(fp[charHash]), characterRecord);
        free(characterRecord);
    }

    /* Close data file */
    close(fp0);

    /* Close partition files */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        lseek(*(fp[i]), SEEK_SET, 0);

        /* Write record count to each file */
        write(*(fp[i]), &count[i], sizeof(int));
        close(*(fp[i]));
        free(fp[i]);
    }
    free(fp);

    /* Open data file */
    fp0 = open_file_for_read_solaris(d, "guilds");

    /* Open partition files */
    fp = open_partition_files_for_write(d, "guild", d -> 
outerBufferMaxSize);

    /* Do until EOF */
    while (1)
    {
        /* Read record from file */
        if ((guildRecord = read_guild_record_solaris(d, fp0)) == NULL)
        {
            /* END OF FILE stop reading file */
            break;
        }

        /* Hash the record */
        guildHash = hashfunction2((*guildRecord).guild.guildID, d -> outerBufferMaxSize);

        /* Increment counter for records with that hash */
        count[guildHash]++;

        /* Write to the file with that Hash */
        write_guild_record_solaris(d, *(fp[guildHash]), guildRecord);

        free(guildRecord);
    }

    /* Close data file */
    close(fp0);

    /* Close partition files */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        close(*(fp[i]));
        free(fp[i]);
    }
    free(fp);

    d -> partitionCount = d -> outerBufferMaxSize;
}

/* 
 * Partition guilds and characters according to hash
 * 
 * d is the DataStore struct 
 */
void partition_guild_character(DataStore *d)
{
    /* Pointer to data file */
    int fp0 = 0;

    /* Pointer to partition files */
    int ** fp = NULL;

    /* Records */
    Record * guildRecord = NULL;
    Record * characterRecord = NULL;

    /* Hashes */
    int guildHash = 0;
    int charHash = 0;
    int i = 0;

    int zero = 0;

    int count[d -> outerBufferMaxSize];

    /* *** Partitioning Phase ***
    * Open output files for this partition 
    * Read record from first database into r 
    * Hash record 
    * Write record to output file given by fp[hash] 
       * Write count to start of each partition 
       * Free r 
       * Do this until end of file 
       * Close all output files 
       * Now repeat for second data base */

    fp0 = open_file_for_read_solaris(d, "guilds");

    fp = open_partition_files_for_write(d, "guild", d -> outerBufferMaxSize);

    /* Zero the record count on each file */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        write(*(fp[i]), &zero, sizeof(int));
        count[i] = 0;
    }

    /* Do until EOF */
    while (1)
    {
        /* Read record from file */
        if ((guildRecord = read_guild_record_solaris(d, fp0)) == NULL)
        {
            /* END OF FILE stop reading file */
            break;
        }

        /* Hash record */
        guildHash = hashfunction2((*guildRecord).guild.guildID, d -> outerBufferMaxSize);

        /* Increment record count for records with that Hash */
        count[guildHash]++;

        /* Write to the file with that Hash */
        write_guild_record_solaris(d, *(fp[guildHash]), guildRecord);
        free(guildRecord);
    }

    /* Close data file */
    close(fp0);

    /* Close partition files */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        lseek(*(fp[i]), SEEK_SET, 0);

        /* Write record count to each file */
        write(*(fp[i]), &count[i], sizeof(int));
        close(*(fp[i]));
        free(fp[i]);
    }
    free(fp);

    /* Open data file */
    fp0 = open_file_for_read_solaris(d, "characters");

    /* Open partition files */
    fp = open_partition_files_for_write(d, "character", d -> 
outerBufferMaxSize);

    /* Do until EOF */
    while (1)
    {
        /* Read record from file */
        if ((characterRecord = read_character_record_solaris(d, fp0)) == NULL)
        {
            /* END OF FILE stop reading file */
            break;
        }

        /* Hash the record */
        charHash = hashfunction2((*characterRecord).character.guildID, d -> outerBufferMaxSize);

        /* Increment counter for records with that hash */
        count[charHash]++;

        /* Write to the file with that Hash */
        write_character_record_solaris(d, *(fp[charHash]), characterRecord);

        free(characterRecord);
    }

    /* Close data file */
    close(fp0);

    /* Close partition files */
    for (i = 0; i < d -> outerBufferMaxSize; i++)
    {
        close(*(fp[i]));
        free(fp[i]);
    }
    free(fp);

    d -> partitionCount = d -> outerBufferMaxSize;
}

/*
 * Try to match characters to guilds using Hash Join method
 *
 * d is the DataStore struct
 */
void match_character_guild(DataStore *d)
{
    /* *** Matching Phase ***
    * Open partition files for first file one at a time 
    * Define hashtable size from the count at start of file 
    * Read all records into hashtable 
    * Now match "outer" against second file line by line */

    int i = 0;
    int j = 0;

    int fpP1 = -1;
    int fpP2 = -1;
    char filenameP1[100] = "";
    char filenameP2[100] = "";
    int countP1 = 0;

    /* For each partition from the first file */
    for (i = 0; i < d -> partitionCount; i++)
    {
        /* Open partition files */
        sprintf(filenameP1, "scratch//character%d", i);
        sprintf(filenameP2, "scratch//guild%d", i);
        fpP1 = open_file_for_read_solaris(d, filenameP1);
        fpP2 = open_file_for_read_solaris(d, filenameP2);

        /* Read count of records in partition */
        read(fpP1, &countP1, sizeof(int));

        /* Set the hashtable size to the partition record count */
        d -> outerBufferMaxSize = countP1;

        /* Re-allocate the hashtable size for this partition size */
        d -> outerBuffer = (union Record **) realloc(d ->
                           outerBuffer, sizeof(union Record *) * countP1);

        /* Zero the pointer memory */
        for (j = 0; j < countP1; j++)
        {
            d -> outerBuffer[j] = NULL;
        }

        /* Reads all the records to the Hashtable buffer */
        read_character_records_to_outer_buffer_solaris(d, fpP1);

        /* Scan matching partition for matches */
        search_character_buffer_for_guild_solaris(d, fpP2);
        
        /* Free outer buffer memory */
        free_outer_buffer(d);

        /* Close partition files */
        close(fpP1);
        close(fpP2);
    }
}

/* 
 * Try to match characters to guilds using Hash Join method 
 * 
 * d is the DataStore struct
 */
void match_guild_character(DataStore *d)
{
    /* *** Matching Phase ***
    * Open partition files for first file one at a time 
    * Define hashtable size from the count at start of file 
    * Read all records into hashtable 
    * Now match "outer" against second file line by line */

    int i = 0;
    int j = 0;

    int fpP1 = -1;
    int fpP2 = -1;
    char filenameP1[100] = "";
    char filenameP2[100] = "";
    int countP1 = 0;

    /* For each partition from the first file */
    for (i = 0; i < d -> partitionCount; i++)
    {
        /* Open partition files */
        sprintf(filenameP1, "scratch//guild%d", i);
        sprintf(filenameP2, "scratch//character%d", i);
        fpP1 = open_file_for_read_solaris(d, filenameP1);
        fpP2 = open_file_for_read_solaris(d, filenameP2);

        /* Read count of records in partition */
        read(fpP1, &countP1, sizeof(int));

        /* Set the hashtable size to the partition record count */
        d -> outerBufferMaxSize = countP1;

        /* Re-allocate the hashtable size for this partition size */
        d -> outerBuffer = (union Record **) realloc(d ->
                           outerBuffer, sizeof(union Record *) * countP1);

        /* Zero the pointer memory */
        for (j = 0; j < countP1; j++)
        {
            d -> outerBuffer[j] = NULL;
        }

        /* Reads all the records to the Hashtable buffer */
        read_guild_records_to_outer_buffer_solaris(d, fpP1);

        /* Scan matching partition for matches */
        search_guild_buffer_for_character_solaris(d, fpP2);
        
        /* Free outer buffer memory */
        free_outer_buffer(d);

        /* Close partition files */
        close(fpP1);
        close(fpP2);
    }
}

/*
 * This function just calls the separate 
 * partitioning and matching functions
 */
void process_files(DataStore *d)
{
        
        /* 
         * START THE TIMER -
         * this really should be in 
         * it's own function
         */
#if defined(linux) || defined (__linux)

        clock_t ticks1 = { 0 };
        clock_t ticks2 = { 0 };

        ticks1 = clock();
        ticks2 = ticks1;
#endif

#if defined(sun) || defined(__sun)

        hrtime_t ticks1 = { 0 };
        hrtime_t ticks2 = { 0 };

        ticks1 = gethrtime();
        ticks2 = ticks1;
#endif

	if (d -> outerRecordType == guild)
	{ 
        	partition_guild_character(d);
   	        match_guild_character(d);
	} else {
		partition_character_guild(d);
                match_character_guild(d);
	}

        /* Stop the timer */        
#if defined(linux) || defined (__linux)
        ticks2 = clock();

        d -> totalTime += (ticks2 - ticks1);
#endif

#if defined(sun) || defined(__sun)
        ticks2 = gethrtime();

        d -> totalTime += (ticks2 - ticks1);
#endif
}

/*
 * Main function, 
 * checks for correct number of variables,
 * initialises everything, 
 * runs the main loop 
 *
 * argc - number of arguments passed on command line
 * argv - array of arguments passed from command line  
 */
int main(int argc, char * argv[])
{
    /* New DataStore data struct */
    DataStore d = { 0 };

    /* Buffer size */
    int buffers = 0;

    int verbose = FALSE;

    int offset = 0;

    enum recordKind outer = character;
    enum recordKind inner = guild;
    enum recordKind joined = joined;

    /* Check for correct number of command line args (must be between 4 and 5) */
    if (argc < 4)
    {
        printf(STR_INSUFFICIENT_ARGS);
        exit(EXIT_FAILURE);
    }
    else if (argc > 5)
    {
        printf(STR_TOOMANY_ARGS);
        exit(EXIT_FAILURE);
    }

    /* Check for optional -d argument */
    if (argc == 5)
    {
        if(strcmp(argv[1], "-d") == 0)
        {
            verbose = TRUE;
        }
    }

    /* If we have 5 arguments all the other arguments are offset by one */
    if (argc == 5)
    {
        offset = 1;
    }

    /* Set the page size using command line arg */
    buffers = atoi(argv[1 + offset]);

    /* Check page size is bigger than minimum value */
    if (buffers < BUFFER_SIZE)
    {
        printf(STR_BUFFER, BUFFER_SIZE);
        exit(EXIT_FAILURE);
    }

    /* Determine the join order from the order of the files given */
    if ((strcmp(argv[2 + offset], "characters") == 0) && (strcmp(argv[3 + offset], "guilds") == 0))
    {
        /* printf("First file is character file"); */
        outer = character;
        inner = guild;
    }
    else if ((strcmp(argv[2 + offset], "guilds") == 0) && (strcmp(argv[3 + offset], "characters") == 0))
    {
        /* printf("First file is guild file"); */
        outer = guild;
        inner = character;
    }
    else
    {
        printf("BAD FILENAMES PROVIDED");
        fflush(stdout);
        exit(EXIT_FAILURE);
    }

    /* Initialise the Buffer data struct */
    if (DataStoreInit(&d, buffers, outer, inner, joined, verbose) == FALSE)
    {
        printf(STR_RECORDCACHE_ERROR);
        printf(STR_FATAL);
        printf(STR_RET);
        exit(EXIT_FAILURE);
    }

    mkdir("scratch",0755);

    /* Do the work (Partitioning + Matching) */
    process_files(&d);

    /* Print the stats */
    print_stats(&d);

    /* Destroy the cache and free memory */
    DataStoreDestroy(&d);

    fflush(stdout);

    pid_t pid;
    pid = fork();
    if (pid == 0)
    {
      execl("/bin/rm", "rm", "-r", "scratch", NULL); 
    }

    /* Terminate */
    return EXIT_SUCCESS;
}
