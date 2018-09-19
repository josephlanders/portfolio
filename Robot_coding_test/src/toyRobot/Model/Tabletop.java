/*
 * Toy Robot v3 :~)
 * The table the robot sits on.
 */
package toyRobot.Model;

import java.util.ArrayList;

/**
 *
 * @author z
 */
public class Tabletop implements ITabletop {

    // Tabletop Size - Width / Depth
    private Integer dimensionX = 0;
    private Integer dimensionY = 0;

    private Robot robot = new Robot();

    /*
    @param dimensionX - Tables Width
    @param dimensionY - Tables Depth
    */
    public  Tabletop(Integer dimensionX, Integer dimensionY) // throws TabletopInitialisationException 
    {
        /*
        if ((this.dimensionX < 0) || (this.dimensionY < 0))
        {
            throw new TabletopInitialisationException("Table Size is too small X,Y " + dimensionX + "," + dimensionY);
        } */

        this.dimensionX = dimensionX;
        this.dimensionY = dimensionY;
    }

    /* Move the robot on the table by a certain amount
        @param moveSize An Integer, equals the amount to move the robot
    
        @throws RobotMoveOutOfBoundsException If the robot would move outside the table bounds
    
        @return a Boolean indicate if the move was successful
     */
    public Boolean moveRobot(Integer moveSize) throws RobotMoveOutOfBoundsException, RobotInitialisationException, RobotNotOnTableException {
        Boolean status = false;

        Integer newX = null;
        Integer newY = null;

        // Get the predicted position of the robot (this could be off the table etc)
        ArrayList<Integer> coords = this.robot.calculatePositionAfterMoveForward(moveSize);
        newX = coords.get(0);
        newY = coords.get(1);

        Boolean is_within_bounds = this.isWithinBounds(newX, newY);
        if (is_within_bounds == true) {
            status = this.robot.moveForward(moveSize);
        } else {
            // Out of bounds
            throw new RobotMoveOutOfBoundsException("Robot move out of bounds is not possible X,Y " + newX + "," + newY + " table dimensions X,Y: " + this.getDimensionX() + "," + this.getDimensionY());
        }
        return status;
    }

    /*
        rotate the robot in the specified direction
    
        @param direction is one of LEFT or RIGHT
    
        @return Returns a boolean indicating whether the Robot was rotated.
     */
    public Boolean rotateRobot(String direction) throws RobotInitialisationException, RobotNotOnTableException {
        Boolean status = false;

        status = this.robot.rotate(direction);

        return status;
    }

    public String reportRobot() {
        return this.robot.report();
    }

    /* Place the Robot on the table
    
    @param  initialX - The initial X coord of the Robot
    @param  initialY - The initial Y coord of the Robot
    @param  initialY - The Direction the robot is facing.
    
    @throws RobotMoveOutOfBoundsException If the robot would move outside the table bounds
    
    @return a boolean indicating success/failure of placement of the Robot
     on the table.
     */
    public Boolean placeRobot(Integer initialX, Integer initialY, String direction) throws RobotInitialisationException, RobotPlacementOutOfBoundsException {
        Boolean status = false;

        Boolean is_within_bounds = this.isWithinBounds(initialX, initialY);
        if (is_within_bounds == true) {
            status = this.robot.placeRobot(initialX, initialY, direction);
        } else {
            this.robot.resetRobot();
            throw new RobotPlacementOutOfBoundsException("Robot place out of bounds is not possible X,Y " + initialX + "," + initialY + " table dimensions X,Y: " + this.getDimensionX() + "," + this.getDimensionY());
        }

        return status;
    }

    /* Check if a set of coordinates X, Y of the Robots prospective move
          are within the bounds of the table
    
        @param  X - X coord
        @param  Y - Y coord
        @return a boolean indicating whether the robot would be within bounds
     */
    private Boolean isWithinBounds(Integer X, Integer Y) {
        Boolean status = false;

        if (X >= 0 && X <= this.dimensionX) {
            if (Y >= 0 && Y <= this.dimensionY) {
                status = true;
            }
        }

        return status;
    }
    
    /* Unused */
    boolean setDimensions(Integer dimensionX, Integer dimensionY) throws TabletopInitialisationException {
        if ((this.dimensionX < 0) || (this.dimensionY < 0)) {
            throw new TabletopInitialisationException("Table Size is too small X,Y " + dimensionX + "," + dimensionY);
        }
        this.dimensionX = dimensionX;
        this.dimensionY = dimensionY;

        return true;
    }

    // Table X dimension (width)
    public Integer getDimensionX() {
        return this.dimensionX;
    }

    // Table Y dimension (depth)
    public Integer getDimensionY() {
        return this.dimensionY;
    }
    
    /* reset the Robot */
    public Boolean reset()
    {
        this.robot.resetRobot();
        
        return true;
    }
}
