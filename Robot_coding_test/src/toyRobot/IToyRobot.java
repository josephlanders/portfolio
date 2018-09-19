package toyRobot;

import java.util.ArrayList;

/**
 *
 * @author z
 */
interface IToyRobot {
    public String processCommands(ArrayList<String> commands);
    public String reportRobot();
    public Boolean reset();
}
