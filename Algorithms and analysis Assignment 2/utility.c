/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains the code for the assignment
 ****************************************************************************/

#include "assign.h"
#include "utility.h"
#include "webcache.h"
#include "resourcenode-main.h"
#include "resourcenode-hash.h"
#include "resourcenode-heap.h"

/* Return a hash value for a string */
unsigned int hash(char * resource)
{
    int i = 0;
    unsigned int hash = 0;

    for (i = 0; i < strlen(resource); i++)
    {
        hash = hash ^ (resource[i] + (hash << 6) + (hash >> 2));
    }
    hash = hash % HASHTABLE_SIZE;

    /*
       printf("\nHash is %d", hash);
       fflush(stdout);
    */

    return hash;
}

/* clears text buffer */
void readRestOfLine()
{
    int c;

    /* Read until the end of the line or end-of-file. */
    while ((c = fgetc(stdin)) != '\n' && c != EOF)
        ;

    /* Clear the error and end-of-file flags. */
    clearerr(stdin);
}

/* get a string from console */
signed int getString(char * message, long max_length, char * returnstring)
{
    /* max length includes CR + \0 */

    int invalidInput = FALSE;
    do
    {
        invalidInput = FALSE;

        /* print the message */
        printf("%s (max %ld chars): ", message, max_length - EXTRA_SPACES);

        /* get string into str */
        fgets(&returnstring[0], max_length, stdin);

        /* A string that doesn't have a newline character is too long.
           flush the buffer */
        if (returnstring[strlen(returnstring)	 - 1] != '\n')
        {
            printf("\nInput was too long.\n\n");
            invalidInput = TRUE;
            readRestOfLine();
            continue;
        }

        /* If enter is pressed, quit */
        if (returnstring[0] == NUM_NEW_LINE_CODE)
        {
            *returnstring = 0;
            return ERROR;
        }

        /*
         * Find the newline character 
         * and replace it with \0 to terminate string
         * this fixes strcmp problems with
         * strings from files that don't have \n char 
         */
        if (returnstring[strlen(returnstring) - 1] == '\n')
        {
            returnstring[strlen(returnstring) - 1] = '\0';
        }
    }
    while (invalidInput == TRUE);

    return TRUE;
}

/* Check the string contains only digits */
signed int numberFormatChecker(char * input)
{
    int pos = 0;

    /* check for correct format
     * note we must use != 0 as false=0 and true=any other value
     * also we can't use our own definition of FALSE as this value 
     * may not be 0 */
    for (pos = 0; pos < strlen(input); pos++)
    {
        /* Ignore new lines as they are ok */
        if(iscntrl((unsigned char) (input[pos])) == CFALSE)
        {
            if(isdigit((unsigned char) (input[pos])) == CFALSE)
            {
                return FALSE;
            }
        }
    }
    return TRUE;
}

/* get an int from the console */
signed int getInt(char * message,
                  long int min,
                  long int max,
                  signed long * returnvalue,
                  int prompt)
{
    int invalidInput = FALSE;

    char posStr[NUM_INT_STRING_LEN] = { 0 };

    do
    {
        invalidInput = FALSE;

        /* output message */
        if (prompt == TRUE)
        {
            printf("%s (%ld to %ld): ", message, min, max);
        }
        else
        {
            printf("%s: ", message);
        }

        /* get string */
        fgets(posStr, NUM_INT_STRING_LEN, stdin);

        /* A string that doesn't have a newline character is too long.
           flush the buffer */
        if (posStr[strlen(posStr) - 1] != '\n')
        {
            printf("\nInput was too long.\n\n");
            invalidInput = TRUE;
            readRestOfLine();
            continue;
        }

        /* If enter is pressed, quit */
        if (posStr[0] == NUM_NEW_LINE_CODE)
        {
            *returnvalue = 0;
            return ERROR;
        }

        /* check string is numeric */
        if (numberFormatChecker(posStr) == FALSE)
        {
            printf("\nInput contains non-digit characters\n");
            invalidInput = TRUE;
            continue;
        }

        /* Convert string to number */
        *returnvalue = atoi(&posStr[0]);

        if ((*returnvalue >= min) && (*returnvalue <= max))
        {
            /* return reference to number */
            return *returnvalue;
        }
        else
        {
            printf("\nNumber must be in range %ld to %ld\n", min, max);
            invalidInput = TRUE;
        }

    }
    while (invalidInput == TRUE);

    return TRUE;
}

/* Stops the program by prompting the user to press enter */
void pause()
{
    printf(STR_CONTINUE);
    readRestOfLine();
}
