<?php

use BufferPipeline\BufferPipeline;

require '../vendor/autoload.php';

$sum = 0;

(new BufferPipeline(function ($max) { // generate numbers using generator
    for ($i = 0; $i < $max; $i++) {
        yield $i;
    }
}))
// (new BufferPipeline(function ($max) { // generate numbers with function
//     static $i = null;
//     if ($i === null) {
//         $i = 0;
//     } else {
//         $i++;
//     }
//     if ($i < $max) {
//         return [$i];
//     } else {
//         return null;
//     }
// }))
    ->buffer(100, 1) // 100 items chunk
    ->pipe(function (array $inputs) { // filter array
        return array_values(array_filter($inputs, function ($item) {
            return $item % 10 === 0;
        }));
        // not using functional programming
        // $outputs = [];
        // foreach ($inputs as $input) {
        //     if (($input % 10) === 0) {
        //         $outputs[] = $input;
        //     }
        // }
        // return $outputs;
    })
    ->buffer(15, 1) // 15 items chunk
    ->pipe(function (array $inputs) { // filter array
        if (count($inputs) >= 2) {
            return [$inputs[0], $inputs[1]];
        }
        return [];
    })
    ->buffer(10000, 1) // 15 items chunk
    ->pipe(function (array $inputs) { // map data
        return array_map(function ($item) {
            return $item * 10;
        }, $inputs);
    })
    ->pipe(function (array $inputs) use (&$sum) { // aggregate data
        static $counter = 0;
        $counter += count($inputs);
        $sum += array_sum($inputs);
        echo $counter, PHP_EOL;
    })
    ->exec(1000000000); // pass arguments to generator and execute

echo 'Sum: ', $sum, PHP_EOL;
