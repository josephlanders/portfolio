package toyRobot;

/*
 Toy Robot :~)
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
public class Overall_integration_tests {

    private static IToyRobot toyRobot = null;

    public Overall_integration_tests() {
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
    public void do_the_spec_one() {
        String expected_output = "0,1,NORTH";
        System.out.println("\nTest: do_the_spec_one");
        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("MOVE");
        commands.add("REPORT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void do_the_spec_two() {
        String expected_output = "0,0,WEST";
        System.out.println("\nTest: do_the_spec_two");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 0,0,NORTH");
        commands.add("LEFT");
        commands.add("REPORT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void do_the_spec_three() {
        String expected_output = "3,3,NORTH";
        System.out.println("\nTest: do_the_spec_three");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 1,2,EAST");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("LEFT");
        commands.add("MOVE");
        commands.add("REPORT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

    @Test
    public void try_something_random() {
        String expected_output = "1,3,SOUTH";
        System.out.println("\nTest: try_something_random");

        ArrayList<String> commands = new ArrayList<>();
        commands.add("PLACE 3,2,WEST");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("RIGHT");
        commands.add("MOVE");
        commands.add("MOVE");
        commands.add("LEFT");
        commands.add("LEFT");
        commands.add("MOVE");
        commands.add("REPORT");

        toyRobot.processCommands(commands);
        String report = toyRobot.reportRobot();

        System.out.println("Expected output was: " + expected_output);
        System.out.println("Output was: " + report);

        assertEquals(report.compareTo(expected_output), 0);
    }

}
