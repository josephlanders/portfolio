package code;

import code.configuration;

public class search {

    public static void main(String[] args) {
        searcher searcher = new searcher();
        searcher.parse_arguments(args);
        searcher.initialise();
        searcher.start_processing();;
    }
}
