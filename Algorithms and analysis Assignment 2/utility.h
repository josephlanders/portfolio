/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#include <ctype.h>

/* Return a hash value for a string */
unsigned int hash(char * resource);

/* clears text buffer */
void readRestOfLine();

/* get a string from console */
signed int getString(char * message, long max_length, char * returnstring);

/* Check the string contains only digits */
signed int numberFormatChecker(char * input);

/* get an int from the console */
signed int getInt(char * message,
                  long int min,
                  long int max,
                  signed long * returnvalue,
                  int prompt);

/* Stops the program by prompting the user to press enter */
void pause();

#define ERROR -1
#define NUM_MAX_LINE_LENGTH 100
#define NUM_INT_STRING_LEN 100
#define NUM_NEW_LINE_CODE 10
#define NUM_NEW_LINE_LEN 1

/* Constants. */
/* Number of extra characters to read in,
   to take into account \n and \0 */
#define EXTRA_SPACES 2

/* The values most C functions use for TRUE and FALSE */
#define CFALSE 0
#define CTRUE 1

#define STR_CONTINUE "\n\nPress enter to continue"
