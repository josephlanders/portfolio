#############################################################################
# COSC1285 Algorithms and Analysis Assignment #2
# Author:            Joseph Peter Landers
# Student Number:    s3163776@student.rmit.edu.au
# Alternate email:   josephlanders@gmail.com
# Yallara Username:  jlanders / s3163776
#
# This is the makefile
#
#############################################################################

assignOBJ=assign.o webcache.o utility.o \
resourcenode-main.o resourcenode-heap.o resourcenode-hash.o
LIBC=-lc
FLAGS=-std=c99

FILES=*.c *.h *.txt makefile *.sh

all: assign

assign: assign.o webcache.o resourcenode-main.o resourcenode-hash.o \
resourcenode-heap.o utility.o
	gcc $(assignOBJ) -ansi -Wall  -pedantic -o assign $(FLAGS)
	
assign.o: assign.c assign.h
	gcc -c assign.c -ansi -Wall -pedantic $(FLAGS) 
	
utility.o: utility.c utility.h
	gcc -c utility.c -ansi -Wall -pedantic $(FLAGS)  
	
webcache.o: webcache.c webcache.h
	gcc -c webcache.c -ansi -Wall -pedantic $(FLAGS)
	
resourcenode-main.o: resourcenode-main.c resourcenode-main.h
	gcc -c resourcenode-main.c -ansi -Wall -pedantic $(FLAGS)
	
resourcenode-hash.o: resourcenode-hash.c resourcenode-hash.h
	gcc -c resourcenode-hash.c -ansi -Wall -pedantic $(FLAGS)

resourcenode-heap.o: resourcenode-heap.c resourcenode-heap.h
	gcc -c resourcenode-heap.c -ansi -Wall -pedantic $(FLAGS)

clean:
	rm -rf *.o assign *.zip core

archive:
	zip $(USER)-aa-a2-part2.zip $(FILES)
