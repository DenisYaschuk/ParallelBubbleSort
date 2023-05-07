<?php
function parallelOddEvenSort(&$array, $threaNum) {
    // Define the sorting function
    $oddEvenSortFunc = function ($subarray) {
        $sorted = false;
        $n = count($subarray);
        while (!$sorted) {
            $sorted = true;
            // Odd phase
            for ($i = 1; $i < $n - 1; $i += 2) {
                if ($subarray[$i] > $subarray[$i + 1]) {
                    // Swap elements
                    list($subarray[$i], $subarray[$i + 1]) = array($subarray[$i + 1], $subarray[$i]);
                    $sorted = false;
                }
            }
            // Even phase
            for ($i = 0; $i < $n - 1; $i += 2) {
                if ($subarray[$i] > $subarray[$i + 1]) {
                    // Swap elements
                    list($subarray[$i], $subarray[$i + 1]) = array($subarray[$i + 1], $subarray[$i]);
                    $sorted = false;
                }
            }
        }
        return $subarray;
    };
    // Divide the array into sub-arrays and send them to the input channel
    $subarrays = array_chunk($array, ceil(count($array) / $threaNum));
    // Start a worker for each sub-array to sort them in parallel
    $workers = array();
    for ($i = 0; $i < $threaNum; $i++) {
        // Create a parallel context
        $workers[] = \parallel\run($oddEvenSortFunc, array($subarrays[$i]));
    }
    // Wait for each worker to finish and collect the sorted sub-arrays
    $sortedSubarrays = array();
    foreach ($workers as $worker) {
        $sortedSubarrays[] = $worker->value();
    }
    // Merge the sorted sub-arrays
    $array = array();
    while (!empty($sortedSubarrays)) {
        // Find the smallest element among the first elements of each sub-array
        $minVal = PHP_INT_MAX;
        $minIndex = -1;
        foreach ($sortedSubarrays as $index => $subarray) {
            if ($subarray[0] < $minVal) {
                $minVal = $subarray[0];
                $minIndex = $index;
            }
        }
        // Remove the smallest element from its sub-array and add it to the final sorted array
        $array[] = array_shift($sortedSubarrays[$minIndex]);
        // If the sub-array is now empty, remove it from the list of sub-arrays
        if (empty($sortedSubarrays[$minIndex])) {
            unset($sortedSubarrays[$minIndex]);
        }
    }
}
// Define the sorting function
function bubbleSort(&$array) {
    // Perform the bubble sort on the sub-array
    $n = count($array);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($array[$j] > $array[$j + 1]) {
                // Swap elements
                list($array[$j], $array[$j + 1]) = array($array[$j + 1], $array[$j]);
            }
        }
    }
};
$sizesArr = [1000, 5000, 7000, 10000, 20000, 50000, 100000];
foreach($sizesArr as $size){
    print_r("Amount of elements: ". $size . "\n");
    $array = array();
    $array2 = array();
    for ($i = 0; $i < $size; $i++) {
        $num = rand(0, $size);
        $array[] = $num;
        $array2[] = $num;
    }
    $start = hrtime(true);
    parallelOddEvenSort($array, log($size, 10)*4);
    $timeElapsedSecsParalellOddEven = hrtime(true) - $start;
    print_r("Parallel Odd-Even sort: " . $timeElapsedSecsParalellOddEven/1e+6. " ms"."\n");
    $start = hrtime(true);
    bubbleSort($array2);
    $timeElapsedSecsRegularBubble = hrtime(true) - $start;
    print_r("Regular Bubble sort: " . $timeElapsedSecsRegularBubble/1e+6 . " ms\n\n\n");
}
