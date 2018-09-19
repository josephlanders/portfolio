This project was coded using Java on Windows in NetBeans with JDK8 and Junit 4.

The code reads the "datafile" named data file for commands. It's in the directory below the source ("src") folder.

There's no interactive prompt.


Assumptions
===========

If a failed place command happens after a valid place - the Robot is no longer on the board.


Possible Improvements?
======================

Improve the exception handling by using JUnit5 with the AssertThrows function and check
 the correct error message is returned to avoid false positives.


How to execute
==============

Options for running:
1.) Import project into Netbeans and run
or
2.) Command line

 
Windows
-------

From the "toy_robot\src" folder execute:

javac -cp . toyRobot\*.java
java -cp . toyRobot.ToyRobot


Linux
-----

From the "toy_robot\src" folder execute:

I suppose for linux this would be:

javac -cp . toyRobot/*.java
java -cp . toyRobot.ToyRobot


Additional notes regarding data file
=====================================

When running from NetBeans the data file in the toy_robot folder is used. 

When running from command line the data file in the toy_robot/src folder is used.


Executing the tests from the command line
===========================================

(Change the slashes for linux)

From \test folder

javac -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src toyRobot\*.java
javac -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src toyRobot\Model\*.java

java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Move_integration_tests
java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Rotate_integration_tests
java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Place_integration_tests
java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Overall_integration_tests

java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Model.Robot_unit_tests
java -cp .;toyRobot\junit-4.10.jar;toyRobot\hamcrest-core-1.3.jar;..\src org.junit.runner.JUnitCore toyRobot.Model.Tabletop_unit_tests


Linux:
javac -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src toyRobot/*.java
javac -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src toyRobot/Model/*.java

java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Move_integration_tests
java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Rotate_integration_tests
java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Place_integration_tests
java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Overall_integration_tests

java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Model.Robot_unit_tests
java -cp .;toyRobot/junit-4.10.jar;toyRobot/hamcrest-core-1.3.jar;../src org.junit.runner.JUnitCore toyRobot.Model.Tabletop_unit_tests




Expected output with datafile_spec
==================================

Successful Execution should result in:

Reading file: datafile for input 
File length: 112 bytes
File lines: 14

Executing: PLACE 0,0,NORTH
Executing: MOVE
Executing: REPORT
0,1,NORTH

Executing: PLACE 0,0,NORTH
Executing: LEFT
Executing: REPORT
0,0,WEST

Executing: PLACE 1,2,EAST
Executing: MOVE
Executing: MOVE
Executing: LEFT
Executing: MOVE
Executing: REPORT
3,3,NORTH


Expected output with datafile_larger
====================================

Reading file: datafile for input 
File length: 1088 bytes
File lines: 127

Executing: ### Examples from specification ###
Executing: ### Example a
Executing: PLACE 0,0,NORTH
Executing: MOVE
Executing: REPORT
0,1,NORTH

Executing: 
Executing: ### Example b
Executing: PLACE 0,0,NORTH
Executing: LEFT
Executing: REPORT
0,0,WEST

Executing: 
Executing: ### Example c
Executing: PLACE 1,2,EAST
Executing: MOVE
Executing: MOVE
Executing: LEFT
Executing: MOVE
Executing: REPORT
3,3,NORTH

Executing: 
Executing: ### Move boundary check tests ###
Executing: PLACE 0,0,NORTH
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Move failed: class toy_robot.Model.RobotMoveOutOfBoundsException
Robot move out of bounds is not possible X,Y 0,6 table dimensions X,Y: 5,5
Executing: REPORT
0,5,NORTH

Executing: 
Executing: PLACE 0,0,EAST
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Executing: MOVE
Move failed: class toy_robot.Model.RobotMoveOutOfBoundsException
Robot move out of bounds is not possible X,Y 6,0 table dimensions X,Y: 5,5
Executing: REPORT
5,0,EAST

Executing: 
Executing: PLACE 0,0,WEST
Executing: MOVE
Move failed: class toy_robot.Model.RobotMoveOutOfBoundsException
Robot move out of bounds is not possible X,Y -1,0 table dimensions X,Y: 5,5
Executing: REPORT
0,0,WEST

Executing: 
Executing: PLACE 0,0,SOUTH
Executing: MOVE
Move failed: class toy_robot.Model.RobotMoveOutOfBoundsException
Robot move out of bounds is not possible X,Y 0,-1 table dimensions X,Y: 5,5
Executing: REPORT
0,0,SOUTH

Executing: 
Executing: ### Rotate tests ###
Executing: PLACE 0,0,SOUTH
Executing: MOVE
Move failed: class toy_robot.Model.RobotMoveOutOfBoundsException
Robot move out of bounds is not possible X,Y 0,-1 table dimensions X,Y: 5,5
Executing: LEFT
Executing: MOVE
Executing: REPORT
1,0,EAST

Executing: 
Executing: PLACE 0,0,NORTH
Executing: LEFT
Executing: REPORT
0,0,WEST

Executing: LEFT
Executing: REPORT
0,0,SOUTH

Executing: LEFT
Executing: REPORT
0,0,EAST

Executing: LEFT
Executing: REPORT
0,0,NORTH

Executing: 
Executing: PLACE 0,0,NORTH
Executing: RIGHT
Executing: REPORT
0,0,EAST

Executing: RIGHT
Executing: REPORT
0,0,SOUTH

Executing: RIGHT
Executing: REPORT
0,0,WEST

Executing: RIGHT
Executing: REPORT
0,0,NORTH

Executing: 
Executing: PLACE 0,5,SOUTH
Executing: MOVE
Executing: LEFT
Executing: MOVE
Executing: LEFT
Executing: MOVE
Executing: LEFT
Executing: MOVE
Executing: LEFT
Executing: REPORT
0,5,SOUTH

Executing: 
Executing: PLACE 0,0,NORTH
Executing: MOVE
Executing: RIGHT
Executing: MOVE
Executing: RIGHT
Executing: MOVE
Executing: RIGHT
Executing: MOVE
Executing: RIGHT
Executing: REPORT
0,0,NORTH

Executing: 
Executing: RIGHT
Executing: LEFT
Executing: MOVE
Executing: REPORT
0,1,NORTH

Executing: 
Executing: ### Place tests ###
Executing: PLACE -1,0,NORTH
Place failed: class toy_robot.Model.RobotPlacementOutOfBoundsException
Robot place out of bounds is not possible X,Y -1,0 table dimensions X,Y: 5,5
Executing: REPORT
Robot is not on table (not placed yet)

Executing: 
Executing: PLACE 0,-1,NORTH
Place failed: class toy_robot.Model.RobotPlacementOutOfBoundsException
Robot place out of bounds is not possible X,Y 0,-1 table dimensions X,Y: 5,5
Executing: REPORT
Robot is not on table (not placed yet)

Executing: 
Executing: PLACE 6,0,NORTH
Place failed: class toy_robot.Model.RobotPlacementOutOfBoundsException
Robot place out of bounds is not possible X,Y 6,0 table dimensions X,Y: 5,5
Executing: REPORT
Robot is not on table (not placed yet)

Executing: 
Executing: PLACE 0,6,NORTH
Place failed: class toy_robot.Model.RobotPlacementOutOfBoundsException
Robot place out of bounds is not possible X,Y 0,6 table dimensions X,Y: 5,5
Executing: REPORT
Robot is not on table (not placed yet)

Executing: 
Executing: PLACE 0,0,NORTH
Executing: REPORT
0,0,NORTH

Executing: 
Executing: PLACE 0,0,EAST
Executing: REPORT
0,0,EAST

Executing: 
Executing: PLACE 0,0,SOUTH
Executing: REPORT
0,0,SOUTH

Executing: 
Executing: PLACE 0,0,WEST
Executing: REPORT
0,0,WEST

Executing: 
Executing: PLACE 0,0,NORT
Place failed: class toy_robot.Model.RobotInitialisationException
Placement Failed: No facing direction
Executing: REPORT
Robot is not on table (not placed yet)
