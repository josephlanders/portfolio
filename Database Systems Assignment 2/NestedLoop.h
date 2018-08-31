/****************************************************************************
 * COSC 2406 Database Systems Assignment #2
 * Author:            Joseph Peter Landers
 * Student Number:    s3163776@student.rmit.edu.au
 * Alternate contact: josephlanders@gmail.com
 * Yallara Username:  jlanders / s3163776
 *
 * This file contains data structure definitions and constants
 ****************************************************************************/

#include <sys/stat.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

/* Specific to wHash */
#include <math.h>
#define OCCUPANCY (int) 80
#define RECORD_SIZE (int) 52
#define HEADER_SIZE (int) 2
#define ERROR (int) -1
#define BUCKET_HEADER_SIZE (int) 2

/* Load the correct clock library depending on current operating system */
#if defined(sun) || defined (__sun)
#include <sys/time.h>
#endif
#if defined(linux) || defined (__linux)
#include <time.h>
#endif

#define TRUE 1
#define FALSE 0

#define STR_FATAL "\nError allocating memory"
#define STR_RET "\nQuitting to console"
#define STR_EXIT "\n\nExiting"

#define STR_RECORDCACHE_ERROR "Couldn't init recordcache - quitting to console"
#define STR_INSUFFICIENT_ARGS "Insufficient command line arguments"
#define STR_FILE_ERROR "Error opening file - quitting to console"
#define STR_BUFFER "Buffer size too small, must be at least %d"

#define STR_TOOMANY_ARGS "Too many command line arguments"

/* Minimum size of the page in bytes */
#define BUFFER_SIZE (int) 3

/* Length of a line of text, including terminating chars */
#define LINE_SIZE 1024
