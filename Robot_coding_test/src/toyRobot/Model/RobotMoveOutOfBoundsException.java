/*
 Toy Robot v3 :~)
 */
package toyRobot.Model;

/**
 *
 * @author z
 */
public class RobotMoveOutOfBoundsException extends Exception {

    String Message = "";

    public RobotMoveOutOfBoundsException(String Message) {
        super(Message);
    }
}
