package toyRobot.Model;

/**
 *
 * @author z
 */
public interface ITabletop {
    public Boolean moveRobot(Integer moveSize) throws RobotMoveOutOfBoundsException, RobotInitialisationException, RobotNotOnTableException;
    public Boolean rotateRobot(String direction) throws RobotInitialisationException, RobotNotOnTableException;
    public String reportRobot();
    public Boolean placeRobot(Integer initialX, Integer initialY, String direction) throws RobotInitialisationException, RobotPlacementOutOfBoundsException;
    public Integer getDimensionX();
    public Integer getDimensionY();
}
