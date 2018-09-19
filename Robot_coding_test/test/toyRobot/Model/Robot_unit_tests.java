package toyRobot.Model;
/*
 * Toy Robot :~)
 * Test the robot class
 */

import org.junit.After;
import org.junit.AfterClass;
import org.junit.Before;
import org.junit.BeforeClass;
import org.junit.Rule;
import org.junit.Test;
import static org.junit.Assert.*;

/**
 *
 * @author z
 */
public class Robot_unit_tests {

    private static Robot robot = null;

    public Robot_unit_tests() {
    }

    @BeforeClass
    public static void setUpClass() {
        robot = new Robot();
    }

    @AfterClass
    public static void tearDownClass() {
    }

    @Before
    public void setUp() {
        robot.resetRobot();
    }

    @After
    public void tearDown() {
    }

    @Test
    public void Robot_place_success() throws RobotInitialisationException {
        String expected_output = "0,0,NORTH";
        System.out.println("\nTest: Robot_place_success");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";
        
        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);
        System.out.println("Place status was: " + place_status);
        System.out.println("Expected output place status: " + place_status);
        
        assertEquals(place_status, true);
        assertEquals(report.compareTo(expected_output), 0);
    }
    
    @Test(expected = RobotInitialisationException.class)
    public void robot_place_fail_no_facing_direction() throws RobotInitialisationException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: robot_place_fail_no_facing_direction");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "";

        Boolean place_status = false;

        System.out.println("Expected: RobotInitialisationException");

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);
    }
    
    // TODO: We should upgrade to JUnit 5 and check the right error message is thrown
    @Test(expected = RobotInitialisationException.class)
    public void Robot_rotate_incorrect_direction() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: Robot_rotate_incorrect_direction");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        System.out.println("Expected: RobotInitialisationException");

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("LEF");

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);
    }



    @Test(expected = RobotInitialisationException.class)
    public void Robot_rotate_no_rotate_direction() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: Robot_rotate_no_rotate_direction");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";


        Boolean place_status = false;

        System.out.println("Expected: RobotInitialisationException");

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("");

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);
    }

    @Test(expected = RobotNotOnTableException.class)
    public void Robot_rotate_right_no_placement() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: Robot_rotate_right_no_placement");

        Boolean place_status = false;

        System.out.println("Expected: RobotNotOnTableException");

        Boolean rotate_status = robot.rotate("RIGHT");
        System.out.println("Facing: ");
        String Facing = robot.getFacing();       

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);

        assertEquals(rotate_status, false);
        assertEquals(Facing.compareTo("EAST"), 0);
        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void Robot_rotate_right() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "0,0,EAST";
        System.out.println("\nTest: Robot_rotate_right");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";
 

        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("RIGHT");

        String Facing = robot.getFacing();

        String report = robot.report();
        System.out.println("Report was: " + report);
        System.out.println("Expected output was: " + expected_output);

        assertEquals(rotate_status, true);
        assertEquals(Facing.compareTo("EAST"), 0);
        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void Robot_rotate_left() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "0,0,WEST";
        System.out.println("\nTest: Robot_rotate_left");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";


        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("LEFT");

        String Facing = robot.getFacing();

        String report = robot.report();
        System.out.println("Report was: " + report);
        System.out.println("Expected output was " + expected_output);

        assertEquals(rotate_status, true);
        assertEquals(Facing.compareTo("WEST"), 0);
                
        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void Robot_move_forward() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "0,1,NORTH";
        System.out.println("\nTest: Robot_move_forward");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean move_status = robot.moveForward(1);

        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);
        
        assertEquals(report.compareTo(expected_output), 0);
    }
    
    @Test
    public void Robot_move_rotate_and_move() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "1,0,EAST";
        System.out.println("\nTest: Robot_move_rotate_and_move");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("RIGHT");

        Boolean move_status = robot.moveForward(1);

        String Facing = robot.getFacing();
        Integer X = robot.getX();
        Integer Y = robot.getY();
        
        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);

        assertEquals(rotate_status, true);
        assertEquals(move_status, true);
        assertEquals(Facing.compareTo("EAST"), 0);
        assertEquals(X.equals(1), true);
        assertEquals(Y.equals(0), true);
        
        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void Robot_move_rotate_and_move_and_rotate_and_move() throws RobotInitialisationException, RobotNotOnTableException {        
        String expected_output = "1,1,NORTH";
        System.out.println("\nTest: Robot_move_rotate_and_move_and_rotate_and_move");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = robot.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = robot.rotate("RIGHT");

        Boolean move_status = robot.moveForward(1);

        Boolean rotate_status2 = robot.rotate("LEFT");

        Boolean move_status2 = robot.moveForward(1);

        String Facing = robot.getFacing();
        Integer X = robot.getX();
        Integer Y = robot.getY();
        
        String report = robot.report();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Report was: " + report);

        assertEquals(rotate_status, true);
        assertEquals(move_status, true);
        assertEquals(Facing.compareTo("NORTH"), 0);
        assertEquals(X.equals(1), true);
        assertEquals(Y.equals(1), true);
        
        assertEquals(report.compareTo(expected_output), 0);
    }

}
