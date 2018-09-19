package toyRobot;

/*
 * Toy Robot :~)
 * Try to rotate the robot
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
public class Rotate_integration_tests {

    private static IToyRobot toyRobot = null;

    public Rotate_integration_tests() {
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
        commands.add("LEFT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();
        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void rotate_right_4_times() {
        String expected_output1 = "0,0,EAST";
        String expected_output2 = "0,0,SOUTH";
        String expected_output3 = "0,0,WEST";
        String expected_output4 = "0,0,NORTH";

        System.out.println("\nTest: rotate_right_4_times");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("RIGHT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println(report);

        commands.clear();;
        commands.add("RIGHT");

        toyRobot.processCommands(commands);
        String report2 = toyRobot.reportRobot();

        System.out.println(report2);

        commands.clear();;
        commands.add("RIGHT");

        toyRobot.processCommands(commands);
        String report3 = toyRobot.reportRobot();

        System.out.println(report3);

        commands.clear();;
        commands.add("RIGHT");

        toyRobot.processCommands(commands);
        String report4 = toyRobot.reportRobot();

        System.out.println(report4);

        System.out.println("Expected output was: " + expected_output4);

        assertEquals(report.compareTo(expected_output1), 0);
        assertEquals(report2.compareTo(expected_output2), 0);
        assertEquals(report3.compareTo(expected_output3), 0);
        assertEquals(report4.compareTo(expected_output4), 0);

    }

    @Test
    public void rotate_left_4_times() {
        String expected_output1 = "0,0,WEST";
        String expected_output2 = "0,0,SOUTH";
        String expected_output3 = "0,0,EAST";
        String expected_output4 = "0,0,NORTH";
        System.out.println("\nTest: rotate_left_4_times");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("LEFT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println(report);

        commands.clear();;
        commands.add("LEFT");

        toyRobot.processCommands(commands);
        String report2 = toyRobot.reportRobot();

        System.out.println(report2);

        commands.clear();;
        commands.add("LEFT");

        toyRobot.processCommands(commands);
        String report3 = toyRobot.reportRobot();

        System.out.println(report3);

        commands.clear();;
        commands.add("LEFT");

        toyRobot.processCommands(commands);
        String report4 = toyRobot.reportRobot();

        System.out.println(report4);

        System.out.println("Expected output was: " + expected_output4);

        assertEquals(report.compareTo(expected_output1), 0);
        assertEquals(report2.compareTo(expected_output2), 0);
        assertEquals(report3.compareTo(expected_output3), 0);
        assertEquals(report4.compareTo(expected_output4), 0);

    }

    @Test
    public void rotate_left_4_times_with_move_and_arrive_home() {
        String expected_output = "0,0,WEST";
        System.out.println("\nTest: rotate_left_4_times_with_move_and_arrive_home");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("MOVE");
        commands.add("RIGHT");
        commands.add("MOVE");
        commands.add("RIGHT");
        commands.add("MOVE");
        commands.add("RIGHT");
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void rotate_right_4_times_with_move_and_arrive_home() {
        String expected_output = "5,0,EAST";
        System.out.println("\nTest: rotate_right_4_times_with_move_and_arrive_home");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 5,0,NORTH");
        commands.add("MOVE");
        commands.add("LEFT");
        commands.add("MOVE");
        commands.add("LEFT");
        commands.add("MOVE");
        commands.add("LEFT");
        commands.add("MOVE");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }
}
