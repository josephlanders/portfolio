/*
 Toy Robot v3 :~)
 */
package toyRobot.Model;

/**
 *
 * @author z
 */
public class RobotInitialisationException extends Exception {

    String Message = "";

    public RobotInitialisationException(String Message) {
        super(Message);
    }
}
