package toyRobot;
/*
 * Toy Robot :~)
 *
 * Try to move the robot.
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
public class Move_integration_tests {

    private static IToyRobot toyRobot = null;

    public Move_integration_tests() {
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
        //toyRobot = new ToyRobot();
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
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void move_x_plus_out_of_bounds() {
        String expected_output = "0,5,NORTH";
        System.out.println("\nTest: move_x_plus_out_of_bounds");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void move_x_minus_out_of_bounds() {
        String expected_output = "0,0,SOUTH";

        System.out.println("\nTest: move_x_minus_out_of_bounds");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,SOUTH");
        commands.add("MOVE");
        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println(report);

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void move_y_plus_out_of_bounds() {
        String expected_output = "5,0,EAST";

        System.out.println("\nTest: move_y_plus_out_of_bounds");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,EAST");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void move_y_minus_out_of_bounds() {
        String expected_output = "0,0,WEST";

        System.out.println("\nTest: move_y_minus_out_of_bounds");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,WEST");
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

}
