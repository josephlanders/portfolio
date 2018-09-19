/*
 * Toy Robot v3 :~)
 * Read data files into memory 
 */
package toyRobot.Model;

import java.io.*;
import java.nio.file.Files;
import java.nio.file.Paths;

/**
 *
 * @author z
 */
public class FileReader {

    private String content = "";

    private Boolean verbose = true;

    public FileReader() {
    }

    /* Read the entire file into a string */
    private String readFile(String filename) throws IOException {
        content = new String(Files.readAllBytes(Paths.get(filename)));
        return content;
    }

    /* Read the lines of the file into a String array */
    public String[] readAllLines(String filename) throws IOException {
        String strFileLines[] = null;
        content = readFile(filename);
        
        strFileLines = this.content.split("\\n");
        if (this.verbose == true) {
            System.out.println("Reading file: " + filename + " for input ");
            System.out.println("File length: " + content.length() + " bytes");
            System.out.println("File lines: " + strFileLines.length);
            System.out.println();
        }
        return strFileLines;
    }
}
