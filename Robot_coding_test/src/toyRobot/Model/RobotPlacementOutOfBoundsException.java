/*
 * Toy Robot v3 :~)
 */
package toyRobot.Model;

/**
 *
 * @author z
 */
public class RobotPlacementOutOfBoundsException extends Exception {

    String Message = "";

    public RobotPlacementOutOfBoundsException(String Message) {
        super(Message);
    }
}
