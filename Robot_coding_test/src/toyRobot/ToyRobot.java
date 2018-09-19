/*
* Toy Robot v3 :~)
*
* Entrypoint to our program
 */
package toyRobot;

/**
 *
 * @author z
 */
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import toyRobot.Model.FileReader;
import toyRobot.Model.Tabletop;
import toyRobot.Model.RobotInitialisationException;
import toyRobot.Model.RobotMoveOutOfBoundsException;
import toyRobot.Model.RobotNotOnTableException;
import toyRobot.Model.RobotPlacementOutOfBoundsException;
//import toy_robot.Model.TabletopInitialisationException;

public class ToyRobot implements IToyRobot {

    private Tabletop tableTop = new Tabletop(5,5);
    private Boolean verbose = false;

    public static void main(String[] args) {
        ToyRobot toyRobot = new ToyRobot();

        toyRobot.readDataFileAndExecute();
    }

    public ToyRobot() {
    }

    // Reads our "datafile" and then calls function to process commands
    private void readDataFileAndExecute() {

        ArrayList<String> commands = new ArrayList<>();
        FileReader fileReader = new FileReader();

        String[] commandStr = new String[0];
        try {
            commandStr = fileReader.readAllLines("datafile");

            commands = new ArrayList<>(Arrays.asList(commandStr));
            //Collections.addAll(commands, commands_str);          

            String report = processCommands(commands);
        } catch (IOException e) {
            System.out.println("Unable to read file: "
                    + "check datafile is in correct location");
            System.out.println("Unable to read file: data file should be in "
                    + "src folder if executing from command line" + "\n");
            System.out.println("IOException: " + e);
        }

    }

    /* Iterate over commands, executing them and output everything to console.
     * @param commands An ArrayList of Strings containing commands
    */
    public String processCommands(ArrayList<String> commands) {
        ArrayList<String> output = new ArrayList<>();

        for (int i = 0; i < commands.size(); i++) {
            String cmd = commands.get(i);

            cmd = cmd.trim();

            //String split / tokenize
            String[] tokens = cmd.split("\\s");
            if (tokens.length == 0) {
                System.out.println("\n");
                continue;
            }
            
            String action = tokens[0];
            
            if (action.compareTo("EXPECTED") == 0)
            {
                System.out.println(cmd);
                System.out.println();
                continue;
            }

            System.out.println("Executing: " + cmd);

            

            if (this.verbose == true) {
                System.out.print("Command: ");
                System.out.println(tokens[0]);
            }

            if (action.compareTo("PLACE") == 0) {
                Boolean placeStatus = false;

                placeStatus = placeFunction(tokens[1]);
            }

            if (action.compareTo("MOVE") == 0) {
                Boolean moveStatus = false;

                moveStatus = moveFunction();
            }

            if ((action.compareTo("LEFT") == 0) || (action.compareTo("RIGHT") == 0)) {
                Boolean rotateStatus = false;

                String direction = tokens[0];

                rotateStatus = rotateFunction(direction);
            }

            if (action.compareTo("REPORT") == 0) {
                String report = tableTop.reportRobot();
                System.out.println(report + "\n");
            }
            

        }

        return reportRobot();

    }

    /* PARSE the PLACE command line
       Execute the PLACE command line
       FORMAT is "PLACE X,Y,DIRECTION" like "PLACE 0,1,NORTH"
    
       @param Substring is the SECOND part of the command line "0,1,NORTH"
       @return a boolean indicating success or failure
    */
    private Boolean placeFunction(String substring) {
        Boolean placeStatus = false;
        String errorMessage = "";
        Integer initialX  = null;
        Integer initialY = null;
        String facing = null;
        Boolean data_error = false;
        
        String[] subTokens = substring.split(",");

        if (this.verbose == true) {
            System.out.print("Following: ");
            System.out.println(substring);
        }

        if (subTokens.length == 3) {
            try {
                initialX = Integer.parseInt(subTokens[0]);
                initialY = Integer.parseInt(subTokens[1]);
                facing = subTokens[2];
            } catch (NumberFormatException e) {
                System.out.println("Invalid numeric data in PLACE command");
                data_error = true;
            }
        } else {
            // Insufficient tokens.. throw exception or something!
            errorMessage = "Incorrect command length for PLACE, check syntax";
            System.out.println(errorMessage);
            data_error = true;
        }

        if (data_error == false) {
            try {
                placeStatus = tableTop.placeRobot(initialX, initialY, facing);
            } catch (RobotPlacementOutOfBoundsException | RobotInitialisationException e) {
                errorMessage = e.getClass() + "\n" + e.getMessage();
            }
        }

        if (placeStatus == false) {
            System.out.println("Place failed: " + errorMessage);
        }

        if (this.verbose == true) {

            System.out.println("place status is: " + placeStatus.toString());
        }
        return placeStatus;
    }

    /* 
       Execute the move robot command
    
        @return a boolean indicating success or failure
    */
    private Boolean moveFunction() {
        Boolean moveStatus = false;
        String errorMessage = "";
        
        try {
            moveStatus = tableTop.moveRobot(1);
        } catch (RobotMoveOutOfBoundsException | RobotInitialisationException | RobotNotOnTableException e) {
            errorMessage = e.getClass() + "\n" + e.getMessage();
        }

        if (moveStatus == false) {
            System.out.println("Move failed: " + errorMessage);
        }

        if (this.verbose == true) {

            System.out.println("move status is: " + moveStatus.toString());
        }

        return moveStatus;
    }

    /* 
        Execute the rotate robot command
        @param Direction is either "LEFT" or "RIGHT" (parsed earlier)
    
        @return a boolean indicating success or failure
    */
    private boolean rotateFunction(String direction) {
        Boolean rotateStatus = false;
        String errorMessage = "";
        
        try {
            rotateStatus = tableTop.rotateRobot(direction);
        } catch (RobotInitialisationException | RobotNotOnTableException e) {
            errorMessage = e.getClass() + "\n" + e.getMessage();
        }

        if (rotateStatus == false) {
            System.out.println("Rotate failed: " + errorMessage);
        }

        if (this.verbose == true) {

            System.out.println("rotate status is: " + rotateStatus.toString());
        }

        return rotateStatus;
    }

    /*
        Report the robot status
    
    @return a string, containing either: "X,Y,DIRECTION" such as "0,1,NORTH"
    or a message.
    */
    public String reportRobot() {
        return tableTop.reportRobot();
    }
    
    public Boolean reset()
    {
        return this.tableTop.reset();
    }

}
