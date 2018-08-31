package code;

import java.util.HashMap;

public class term_statistics {

    public String term = "";
    public int number_of_unique_documents_occurs_in = 0;
    public HashMap<Integer, Integer> occurances_per_document_array = null;
    public String occurances_per_document_string = "";
    public int length_of_inv_list = 0;

    public term_statistics(String term) {
        this.term = term;
        
        occurances_per_document_array = new HashMap<Integer, Integer>();
    }
    
    public String toString()
    {
        String str = "";
        str = "\nterm: " + term;
        str += "\nnum occurances: " + number_of_unique_documents_occurs_in;
        str += "\nlength of inv list: " + length_of_inv_list;
        str += "\noccurances per document array: " + occurances_per_document_array.toString();
        
        return str;
    }
    
    public void destruct()
    {
        occurances_per_document_array.clear();
        occurances_per_document_array = null;
    }
}
