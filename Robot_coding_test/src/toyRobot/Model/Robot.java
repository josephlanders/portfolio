/*
 Toy Robot v3 :~)

 The Robot class, encapsulates Robot data and actions.
 The Robot can move, rotate and be placed on the table.
 */
package toyRobot.Model;

/**
 *
 * @author z
 */
import java.util.ArrayList;
import java.util.Arrays;

class Robot {

    // Which direction is our robot facing?  Valid values NORTH EAST SOUTH WEST
    private String facing = null;

    // Which direction is our robot facing?
    // Value 0-3 - Represents NORTH, EAST, SOUTH, WEST
    private Integer facingInt = null;

    // Is our robot on the table?
    // (this indicates whether the robot has been placed) 
    // Not whether it is within bounds
    private Boolean onTable = false;

    // Robot X,Y position when on Table
    private Integer X = null;
    private Integer Y = null;

    // Clockwise Directions
    private final ArrayList<String> NESW = new ArrayList<>(Arrays.asList("NORTH", "EAST", "SOUTH", "WEST"));

    Robot() {

    }

    // Reset all robot variables to defaults
    void resetRobot() {
        this.X = null;
        this.Y = null;
        this.facing = null;
        this.facingInt = null;
        this.onTable = false;
    }

    /* 
       Place the robot on the table
    
    @param InitialX an Integer representing X coord of robot
    @param InitialY an Integer representing Y coord of robot
    @param String Direction The Direction the robot initially faces
                One of - North, East, South, West  
    
    @throws RobotInitialisationException when input parameters are incorrect.
    
    @return a boolean indicating Success or failure
     */
    boolean placeRobot(Integer initialX, Integer initialY, String direction) throws RobotInitialisationException {
        Boolean status = false;
        
        if ((initialX < 0) || (initialY < 0))
        {
            resetRobot();
            throw new RobotInitialisationException("Placement Failed: X,Y " + initialX + ", " + initialY + " Must be > 0");
        }

        if (getFacingInt(direction) == null) {
            resetRobot();
            throw new RobotInitialisationException("Placement Failed: No facing direction");
        }

        this.X = initialX;
        this.Y = initialY;
        this.facing = direction;
        this.facingInt = getFacingInt(direction);
        this.onTable = true;
        status = true;

        return status;
    }

    /* Returns an integer to be used with NESW array.
       The integer maps to the Direction name in the array.
    
    @param Direction a String, has the values "NORTH" "EAST" "SOUTH" or "WEST" 
    
    @return Returns an Integer either 0 = NORTH, 1 = EAST, 2 = SOUTH, 3 = WEST 
     */
    Integer getFacingInt(String direction) {
        Integer directionInt = null;

        for (int i = 0; i < NESW.size(); i++) {
            if (direction.compareTo(NESW.get(i)) == 0) {
                directionInt = i;
                break;
            }
        }

        /*
        if (directionInt == null)
        {
            throw new Exception("uh oh :(");
        } */
        return directionInt;
    }

    /* rotate the robot - there are two commands that allow the Robot to rotate
     Clockwise (LEFT) and anticlockwise (RIGHT) in 90 degree increments.
    
    @param Direction is a string of either "LEFT" or "RIGHT"
    
    @throws RobotInitialisationException If the robot hasn't been properly initialised
     or the rotate direction is incorrect
    @throws RobotNotOnTableException when the robot is not on the table.
    
    @return a boolean indicating success or failure
     */
    boolean rotate(String direction) throws RobotInitialisationException, RobotNotOnTableException {
        Boolean status = false;

        if (this.onTable == false) {
            throw new RobotNotOnTableException("Robot not on table");
        }

        if (this.facingInt == null) {
            throw new RobotInitialisationException("Robot has no facing direction");
        }

        if (direction.compareTo("LEFT") != 0 && direction.compareTo("RIGHT") != 0) {
            throw new RobotInitialisationException("Invalid Rotation Direction " + direction);
        }

        if (direction.compareTo("LEFT") == 0) {
            this.facingInt -= 1;
            if (this.facingInt == -1) {
                this.facingInt = 3;
            }
        } else if (direction.compareTo("RIGHT") == 0) {
            this.facingInt += 1;
            if (this.facingInt == 4) {
                this.facingInt = 0;
            }
        }

        this.facing = NESW.get(this.facingInt);

        status = true;

        return status;
    }

    /* Calculate and update the robots X,Y position 
    @param move_size an integer (usually 1) of how many units to move in current
        direction
    
    @throws RobotNotOnTableException when the robot is not on the table.
    
    @return a boolean indicating success or failure
     */
    Boolean moveForward(Integer moveSize) throws RobotInitialisationException, RobotNotOnTableException {
        Boolean status = false;

        if (this.onTable == false) {
            throw new RobotNotOnTableException("Robot not on table");
        }

        ArrayList<Integer> coords = null;

        coords = calculatePositionAfterMoveForward(moveSize);
        this.X = coords.get(0);
        this.Y = coords.get(1);

        status = true;

        return status;
    }

    /*
       Calculate the robots coordinates after it moves forward
    but doesn't update the coords
    
        @param move_size is the unit size of the move (1,2,3 etc).
    
        @throws RobotInitialisationException If the robot hasn't been properly initialised
        @throws RobotNotOnTableException when the robot is not on the table.
    
        @return the new coordinates as an ArrayList containing elements 
            0 - the new X position and 
            1 - the new Y position
     */
    ArrayList<Integer> calculatePositionAfterMoveForward(Integer moveSize) throws RobotInitialisationException, RobotNotOnTableException {
        ArrayList<Integer> coords = new ArrayList<>();

        Integer newX = this.X;
        Integer newY = this.Y;

        if (this.onTable == false) {
            throw new RobotNotOnTableException("Robot not on table");
        }

        if (this.facingInt == null) {
            throw new RobotInitialisationException("Robot has no facing direction");
        }
        if ((this.X == null) || (this.Y == null)) {
            throw new RobotInitialisationException("Robot has no X and/or Y set");
        }

        if (this.facingInt == 0) {
            newX += 0;
            newY += moveSize;
        } else if (this.facingInt == 1) {
            newX += moveSize;
            newY += 0;
        } else if (this.facingInt == 2) {
            newX -= 0;
            newY -= moveSize;
        } else if (this.facingInt == 3) {
            newX -= moveSize;
            newY -= 0;
        }

        coords.add(newX);
        coords.add(newY);

        return coords;
    }

    // Returns the facing direction as "NORTH" "SOUTH" "EAST" or "WEST"
    // @return a string
    String getFacing() {
        return this.facing;
    }

    // Get the X coord of the robot on the table
    Integer getX() {
        return this.X;
    }

    // Get the Y coord of the robot on the table
    Integer getY() {
        return this.Y;
    }

    /* report provides a string in the form X,Y,DIRECTION
    
        @return Returns a String
        Format: X,Y,DIRECTION or a nessage
        Example: 0,0,NORTH or "Robot is not on table (not placed yet)"
     */
    public String toString() {
        String str = "";

        if (this.onTable == true)
        {        
            str = this.X + "," + this.Y + "," + this.facing;
        } else {
            str = "Robot is not on table (not placed yet)";
        }

        return str;
    }

    /* report provides a string in the form X,Y,DIRECTION
    
        @return Returns a String
        Format: X,Y,DIRECTION or a nessage
        Example: 0,0,NORTH or "Robot is not on table (not placed yet)"
     */
    String report() {
        return this.toString();
    }
}
