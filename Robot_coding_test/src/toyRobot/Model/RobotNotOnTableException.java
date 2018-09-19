/*
 * Toy Robot v3 :~)
 */
package toyRobot.Model;

/**
 *
 * @author z
 */
public class RobotNotOnTableException extends Exception {

    String Message = "";

    public RobotNotOnTableException(String Message) {
        super(Message);
    }
}
