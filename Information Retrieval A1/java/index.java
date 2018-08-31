package code;

import code.configuration;

public class index {

    public static void main(String[] args) {
        indexer indexer = new indexer();
        indexer.parse_arguments(args);
        indexer.initialise();
        indexer.clear_files();
        indexer.start_processing();;
    }

}
