package toyRobot.Model;
/*
 * Toy Robot :~)
 * Test the tabletop class.
 */

import org.junit.After;
import org.junit.AfterClass;
import org.junit.Before;
import org.junit.BeforeClass;
import org.junit.Test;
import static org.junit.Assert.*;

/**
 *
 * @author z
 */
public class Tabletop_unit_tests {

    private static Tabletop tableTop = null;

    public Tabletop_unit_tests() {
    }

    @BeforeClass
    public static void setUpClass() {
        tableTop = new Tabletop(5, 5);
    }

    @AfterClass
    public static void tearDownClass() {
    }

    @Before
    public void setUp() {
        tableTop.reset();
    }

    @After
    public void tearDown() {
    }
    

    @Test
    public void tabletop_robot_place_success() throws RobotInitialisationException, RobotPlacementOutOfBoundsException {
        Boolean expected_output_bool = true;
        String expected_output = "0,0,NORTH";
        System.out.println("\nTest: tabletop_robot_place_success");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        System.out.println("Expected output was: " + expected_output_bool);
        System.out.println("Output was: " + place_status);

        String report = tableTop.reportRobot();
        System.out.println("report was: " + report);
        System.out.println("Expected ouput was: " + expected_output);

        assertEquals(place_status, true);
        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test(expected = RobotInitialisationException.class)
    public void tabletop_robot_place_fail_no_facing_direction() throws RobotInitialisationException, RobotPlacementOutOfBoundsException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_place_fail_no_facing_direction");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "";

        Boolean place_status = false;

        System.out.println("Expecting: RobotInitialisationException");

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected ouput was: " + expected_output);
    }

    @Test(expected = RobotPlacementOutOfBoundsException.class)
    public void tabletop_robot_place_out_of_bounds() throws RobotInitialisationException, RobotPlacementOutOfBoundsException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_place_out_of_bounds");
        Integer InitialX = 6;
        Integer InitialY = 6;
        String Direction = "NORTH";

        Boolean place_status = false;

        System.out.println("Expecting: RobotPlacementOutOfBoundsException");

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected ouput was: " + expected_output);
    }

    @Test(expected = RobotInitialisationException.class)
    public void tabletop_robot_rotate_no_initial_direction() throws RobotInitialisationException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_rotate_no_initial_direction");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "";

        Boolean place_status = false;

        System.out.println("Expecting: RobotInitialisationException");

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("RIGHT");

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected output was: " + expected_output);
    }

    @Test(expected = RobotNotOnTableException.class)
    public void tabletop_robot_rotate_right_no_placement() throws RobotInitialisationException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_rotate_right_no_placement");

        System.out.println("Expecting: RobotNotOnTableException");

        Boolean rotate_status = tableTop.rotateRobot("RIGHT");

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected ouput was: " + expected_output);
    }

    @Test
    public void tabletop_robot_rotate_right() throws RobotInitialisationException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "0,0,EAST";

        System.out.println("\nTest: tabletop_robot_rotate_right");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("RIGHT");

        String report = tableTop.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void tabletop_robot_rotate_left() throws RobotInitialisationException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "0,0,WEST";

        System.out.println("\nTest: tabletop_robot_rotate_left");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("LEFT");

        String report = tableTop.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);

    }

    @Test(expected = RobotMoveOutOfBoundsException.class)
    public void tabletop_robot_move_out_of_bounds_after_rotate() throws RobotInitialisationException, RobotMoveOutOfBoundsException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_move_out_of_bounds_after_rotate");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        System.out.println("Expecting: RobotMoveOutOfBoundsException");

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("LEFT");

        Boolean move_status = tableTop.moveRobot(1);

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected ouput was: " + expected_output);        
    }

    @Test
    public void tabletop_robot_move_rotate_etc() throws RobotInitialisationException, RobotPlacementOutOfBoundsException, RobotMoveOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "1,0,EAST";
        System.out.println("\nTest: tabletop_robot_move_rotate_etc");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("RIGHT");

        Boolean move_status = tableTop.moveRobot(1);

        String report = tableTop.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);

    }

    @Test
    public void tabletop_robot_move_rotate_move() throws RobotInitialisationException, RobotPlacementOutOfBoundsException, RobotMoveOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "1,1,NORTH";
        System.out.println("\nTest: tabletop_robot_move_rotate_move");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean rotate_status = tableTop.rotateRobot("RIGHT");

        Boolean move_status = tableTop.moveRobot(1);

        Boolean rotate_status2 = tableTop.rotateRobot("LEFT");

        Boolean move_status2 = tableTop.moveRobot(1);

        String report = tableTop.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test(expected = RobotMoveOutOfBoundsException.class)
    public void tabletop_robot_move_out_of_bounds() throws RobotInitialisationException, RobotMoveOutOfBoundsException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "NO_EXPECTED_OUTPUT";
        System.out.println("\nTest: tabletop_robot_move_out_of_bounds");
        Integer InitialX = 0;
        Integer InitialY = 5;
        String Direction = "NORTH";

        System.out.println("Expecting: RobotMoveOutOfBoundsException");

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean move_status = tableTop.moveRobot(1);
        Boolean move_status2 = tableTop.moveRobot(1);

        String report = tableTop.reportRobot();
        System.out.println("output was: " + report);
        System.out.println("Expected ouput was: " + expected_output);
    }

    @Test
    public void tabletop_robot_move_within_of_bounds() throws RobotInitialisationException, RobotMoveOutOfBoundsException, RobotPlacementOutOfBoundsException, RobotNotOnTableException {
        String expected_output = "0,1,NORTH";
        System.out.println("\nTest: tabletop_robot_move_within_of_bounds");
        Integer InitialX = 0;
        Integer InitialY = 0;
        String Direction = "NORTH";

        Boolean place_status = false;

        place_status = tableTop.placeRobot(InitialX, InitialY, Direction);

        Boolean move_status = tableTop.moveRobot(1);

        String report = tableTop.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);

    }
}
