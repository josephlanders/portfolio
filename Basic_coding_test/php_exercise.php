<?php

/*
 * First method - use two arrays, one for positives and negatives.
 *            Sort arrays then extra highest/lowest values.
 * 
 * Second method - use two heaps, minheap, maxheap
 *            extract from top of heap to get highest/lowest values
 * 
 * Third method - just do it the *normal* way and compare and store
 *         the negative and positive highest / lowest value.
 */

function smallestNumber($inputs) {
    $inputs = explode(" ", $inputs);

    $arr_positives = array();
    $arr_negatives = array();

    $closest = null;
    $closest_positive = null;
    $closest_negative = null;


    //your code goes here
    foreach ($inputs as $key => $value) {
        if ($value >= 0) {
            $arr_positives[] = $value;
        } else {
            $arr_negatives[] = $value;
        }
    }

    sort($arr_positives);
    sort($arr_negatives);

    if (count($arr_positives) > 0) {
        $closest_positive = $arr_positives[0];
    }

    if (count($arr_negatives) > 0) {
        $closest_negative = $arr_negatives[count($arr_negatives) - 1];
    }

    if ($closest_positive != null && $closest_negative != null) {
        if (abs($closest_positive) <= abs($closest_negative)) {
            $closest = $closest_positive;
        } else {
            $closest = $closest_negative;
        }
    } elseif ($closest_positive == null) {
        $closest = $closest_negative;
    } elseif ($closest_negative == null) {
        $closest = $closest_positive;
    }

    if ($closest !== null) {
        $closest = (int) $closest;
    }

    echo "\nclosest: " . $closest;

    return $closest;
}

class PQtest extends SplMinHeap {

    public function compare($priority1, $priority2) {
        if ($priority1 === $priority2)
            return 0;
        return $priority1 > $priority2 ? -1 : 1;
    }

}

class PQtest2 extends SplMaxHeap {

    public function compare($priority1, $priority2) {
        if ($priority1 === $priority2)
            return 0;
        return $priority1 < $priority2 ? -1 : 1;
    }

}

function smallestNumber2($inputs) {
    $inputs = explode(" ", $inputs);

    $closest = null;
    $closest_positive = null;
    $closest_negative = null;

    $objPQ = new PQtest();
    $objPQ2 = new PQtest2();

    foreach ($inputs as $key => $value) {
        if ($value >= 0) {
            $objPQ->insert($value);
        } else {
            $objPQ2->insert($value);
        }
    }

    if ($objPQ->count() > 0) {
        $objPQ->top();
        if ($objPQ->valid()) {
            $closest_positive = $objPQ->current();
        }
    }

    if ($objPQ2->count() > 0) {
        $objPQ2->top();
        if ($objPQ2->valid()) {
            $closest_negative = $objPQ2->current();
        }
    }

    if ($closest_positive != null && $closest_negative != null) {
        if (abs($closest_positive) <= abs($closest_negative)) {
            $closest = $closest_positive;
        } else {
            $closest = $closest_negative;
        }
    } elseif ($closest_positive == null) {
        $closest = $closest_negative;
    } elseif ($closest_negative == null) {
        $closest = $closest_positive;
    }

    if ($closest !== null) {
        $closest = (int) $closest;
    }

    echo "\nclosest: " . $closest;
    return $closest;
}

function smallestNumber3($inputs) {
    $inputs = explode(" ", $inputs);

    $closest = null;
    $closest_positive = null;
    $closest_negative = null;


    //your code goes here
    foreach ($inputs as $key => $value) {
        if ($value >= 0) {
            if ($closest_positive === null) {
                $closest_positive = $value;
            } else {
                if ($value < $closest_positive) {
                    $closest_positive = $value;
                }
            }
        } else {
            if ($closest_negative === null) {
                $closest_negative = $value;
            } else {
                if ($value > $closest_negative) {
                    $closest_negative = $value;
                }
            }
        }
    }

    if ($closest_positive != null && $closest_negative != null) {
        if (abs($closest_positive) <= abs($closest_negative)) {
            $closest = $closest_positive;
        } else {
            $closest = $closest_negative;
        }
    } elseif ($closest_positive == null) {
        $closest = $closest_negative;
    } elseif ($closest_negative == null) {
        $closest = $closest_positive;
    }

    //echo $closest;

    if ($closest !== null) {
        $closest = (int) $closest;
    }

    echo "\nclosest: " . $closest;

    return $closest;
}

// test examples
$set1 = "10 12 8 3 19 14 19 19 30 4 16 8";
assert(smallestNumber($set1) === 3);
$set2 = "8 12 8 -7 14 -12 19 14 19 -5 19 30 4 -4 15 16 8";
assert(smallestNumber($set2) === 4);
$set3 = "19 30 4 16 8 8 12 8 -7 14 -12 19 -2 16 31 8 -9";
assert(smallestNumber($set3) === -2);

// test examples
//$set1 = "10 12 8 3 19 14 19 19 30 4 16 8";
assert(smallestNumber2($set1) === 3);
//$set2 = "8 12 8 -7 14 -12 19 14 19 -5 19 30 4 -4 15 16 8";
assert(smallestNumber2($set2) === 4);
//$set3 = "19 30 4 16 8 8 12 8 -7 14 -12 19 -2 16 31 8 -9";
assert(smallestNumber2($set3) === -2);


// test examples
//$set1 = "10 12 8 3 19 14 19 19 30 4 16 8";
assert(smallestNumber3($set1) === 3);
//$set2 = "8 12 8 -7 14 -12 19 14 19 -5 19 30 4 -4 15 16 8";
assert(smallestNumber3($set2) === 4);
//$set3 = "19 30 4 16 8 8 12 8 -7 14 -12 19 -2 16 31 8 -9";
assert(smallestNumber3($set3) === -2);
