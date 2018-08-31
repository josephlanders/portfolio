/****************************************************************************
 * COSC1285 Algorithms and Analysis Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define TRUE (int) 1
#define FALSE (int) 0

#define STR_FATAL "\nError allocating memory"
#define STR_RET "\nQuitting to console"
#define STR_EXIT "\n\nExiting"

#define STR_WEBCACHE_ERROR "Couldn't init webcache - quitting to console"
#define STR_INSUFFICIENT_ARGS "Insufficient command line arguments"
#define STR_FILE_ERROR "Error opening file - quitting to console"
#define STR_CACHESIZE "Cache size too small, must be at least %d bytes"

#define STR_TOOMANY_ARGS "Too many command line arguments"

/* Minimum size of the cache in bytes */
#define CACHE_SIZE (unsigned int) 50000

/* Length of a line of text, including terminating chars */
#define LINE_SIZE (int) 1024
