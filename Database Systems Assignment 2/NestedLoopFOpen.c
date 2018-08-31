/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment
 *
 * DO NOT MARK THIS FILE - THIS FILE IS NOT USED IN THIS ASSIGNMENT
 *
 * Please see NestedLoop.c for fully commented code.
 * 
 * Joins two files using the Nested Loop Method and uses FOpen not Open
 ****************************************************************************/

#include "NestedLoopFOpen.h"
#include "recordcache.h"
#include "utility.h"

void simple_join(DataStore *d, FILE * fp, FILE * fp2)
{
    /* 
       Perform the join in the specific order  
       if guilds was passed first, 

       fp1 = guilds, 
       fp2 = characters 
    */
    if (d -> outerRecordType == guild)
    {
         while (!feof(fp))
         {
            read_guild_records_to_outer_buffer(d, fp);

            search_guild_buffer_for_character(d, fp2);

            /* Free buffers here */
            free_outer_buffer(d); 
         }
    } else {
    /* 
       Note the search code needs modifying as there may be many characters in the same guild in the buffer
       hence search entire buffer 

       This is due to the foreign key being in the outer buffer.
     */
         while (!feof(fp))
         {
            read_character_records_to_outer_buffer(d, fp);

            search_character_buffer_for_guild(d, fp2);

            /* Free buffers here */
            free_outer_buffer(d); 
         }

    }
}

/* 
 * Does the processing and records the time taken 
 * 
 * d is the DataStore struct
 * fp is a pointer to the first file
 * fp2 is a pointer to the second file
 */
void process_files(DataStore *d, FILE * fp, FILE * fp2)
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


    simple_join(d, fp, fp2);

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

    /* File pointers to the input and output file */
    FILE * fp = NULL;
    FILE * fp2 = NULL;

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
    } else if ((strcmp(argv[2 + offset], "guilds") == 0) && (strcmp(argv[3 + offset], "characters") == 0))
    {
       /* printf("First file is guild file"); */
       outer = guild;
       inner = character;
    } else {
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

    /* Open both input and output files and record file pointers */
    fp = open_file_for_read(&d, argv[2 + offset]);
    fp2 = open_file_for_read(&d, argv[3 + offset]);

    process_files(&d, fp, fp2);

    /* Close the files */
    fclose(fp);
    fclose(fp2);

    print_stats(&d);


    /* Destroy the cache and free memory */
    DataStoreDestroy(&d);

    /* Terminate */
    return EXIT_SUCCESS;
}
