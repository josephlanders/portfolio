#############################################################################
# COSC 2406 Database Systems Assigmment #2
# Author:            Joseph Peter Landers
# Student Number:    s3163776@student.rmit.edu.au
# Alternate email:   josephlanders@gmail.com
# Yallara Username:  jlanders / s3163776
#
# This is the makefile
#
#############################################################################

NestedLoopOBJ=NestedLoop.o recordcache.o utility.o
HashJoinOBJ=HashJoin.o recordcache.o utility.o
NestedLoopFOpenOBJ=NestedLoopFOpen.o recordcache.o utility.o
LIBC=-lc
FLAGS=-std=c99 -lm

FILES=*.c *.h *.txt makefile *.sh *.pdf

all: NestedLoop HashJoin NestedLoopFOpen

NestedLoop: NestedLoop.o recordcache.o utility.o
	gcc $(NestedLoopOBJ) -ansi -Wall  -pedantic -o NestedLoop $(FLAGS)
		
NestedLoop.o: NestedLoop.c NestedLoop.h
	gcc -c NestedLoop.c -ansi -Wall -pedantic $(FLAGS)

HashJoin: HashJoin.o recordcache.o utility.o
	gcc $(HashJoinOBJ) -ansi -Wall  -pedantic -o HashJoin $(FLAGS)

HashJoin.o: HashJoin.c HashJoin.h
	gcc -c HashJoin.c -ansi -Wall -pedantic $(FLAGS)  

NestedLoopFOpen: NestedLoopFOpen.o recordcache.o utility.o
	gcc $(NestedLoopFOpenOBJ) -ansi -Wall  -pedantic -o NestedLoopFOpen $(FLAGS)

NestedLoopFOpen.o: NestedLoopFOpen.c HashJoin.h
	gcc -c NestedLoopFOpen.c -ansi -Wall -pedantic $(FLAGS)  
		
recordcache.o: recordcache.c recordcache.h
	gcc -c recordcache.c -ansi -Wall -pedantic $(FLAGS)

utility.o: utility.c utility.h
	gcc -c utility.c -Wall -pedantic $(FLAGS)

clean:
	rm -rf *.o NestedLoop HashJoin NestedLoopFOpen scratch//* *.zip core

archive:
	zip $(USER)-dbs-a2.zip $(FILES)
