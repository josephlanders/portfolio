package toyRobot;

/*
 * Toy Robot :~)
 * Try to place the robot
 */

import org.junit.After;
import org.junit.AfterClass;
import org.junit.Before;
import org.junit.BeforeClass;
import org.junit.Test;
import static org.junit.Assert.*;

import java.util.ArrayList;

/**
 *
 * @author z
 */
public class Place_integration_tests {

    private static IToyRobot toyRobot = null;

    public Place_integration_tests() {
    }

    @BeforeClass
    public static void setUpClass() {
        toyRobot = new ToyRobot();
    }

    @AfterClass
    public static void tearDownClass() {
    }

    @Before
    public void setUp() {
        toyRobot.reset();
    }

    @After
    public void tearDown() {
    }

    @Test
    public void no_commands() {
        String expected_output = "Robot is not on table (not placed yet)";
        System.out.println("\nTest: no_commands");

        ArrayList<String> commands = new ArrayList<>();

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void place_default() {
        String expected_output = "0,0,NORTH";
        System.out.println("\nTest: place_default");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void place_topleft() {
        String expected_output = "0,5,NORTH";
        System.out.println("\nTest: place_topleft");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,5,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void place_topright() {
        String expected_output = "5,5,NORTH";
        System.out.println("\nTest: place_topright");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 5,5,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void place_bottomright() {
        String expected_output = "5,0,NORTH";
        System.out.println("\nTest: place_bottomright");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 5,0,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test //(expected = RobotPlacementOutOfBoundsException.class)
    public void place_out_of_bounds_x_plus() {
        String expected_output = "Robot is not on table (not placed yet)";
        System.out.println("\nTest: place_out_of_bounds_x_plus");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 6,0,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test //(expected = RobotPlacementOutOfBoundsException.class)
    public void place_out_of_bounds_x_minus() {
        String expected_output = "Robot is not on table (not placed yet)";
        System.out.println("\nTest: place_out_of_bounds_x_minus");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE -1,0,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test //(expected = RobotPlacementOutOfBoundsException.class)
    public void place_out_of_bounds_y_plus() {
        String expected_output = "Robot is not on table (not placed yet)";
        System.out.println("\nTest: place_out_of_bounds_y_plus");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,6,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test //(expected = RobotPlacementOutOfBoundsException.class)
    public void place_out_of_bounds_y_minus() {
        String expected_output = "Robot is not on table (not placed yet)";
        System.out.println("\nTest: place_out_of_bounds_y_minus");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,-1,NORTH");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }
}
