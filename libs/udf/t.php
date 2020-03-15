<?php

$cUdf = FFI::cdef(
<<<EOF
double ArraySum(double numbers[], int size);
double ArrayAvg(double numbers[], int size);
double ArrayMin(double numbers[], int size);
double ArrayMax(double numbers[], int size);
EOF, __DIR__ . '/libcudf.so'
);

$goUdf = FFI::cdef(
    <<<EOF
double ArraySum(double numbers[], int size);
double ArrayAvg(double numbers[], int size);
EOF, __DIR__ . '/libudf.so'
);

$cArr = FFI::new('double[100000000]');
for ($i = 0; $i < 100000000; ++$i) {
    $cArr[$i] = $i;
}

$arr = [];
for ($i = 0; $i < 100000000; ++$i) {
    $arr[$i] = $i;
}

//Test Sum
$start = microtime(true);
echo 'Calculate sum of 10 billion doubles using C', PHP_EOL;
var_dump($cUdf->ArraySum($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate sum of 10 billion doubles using Go', PHP_EOL;
var_dump($goUdf->ArraySum($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate sum of 10 billion doubles using PHP', PHP_EOL;
var_dump(array_sum($arr));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

//Test Avg
$start = microtime(true);
echo 'Calculate avg of 10 billion doubles using C', PHP_EOL;
var_dump($cUdf->ArrayAvg($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate avg of 10 billion doubles using Go', PHP_EOL;
var_dump($goUdf->ArrayAvg($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate avg of 10 billion doubles using PHP', PHP_EOL;
var_dump(array_sum($arr) / 100000000);
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

//Test Min
$start = microtime(true);
echo 'Calculate min of 10 billion doubles using C', PHP_EOL;
var_dump($cUdf->ArrayMin($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate min of 10 billion doubles using PHP', PHP_EOL;
var_dump(min($arr));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

//Test Max
$start = microtime(true);
echo 'Calculate max of 10 billion doubles using C', PHP_EOL;
var_dump($cUdf->ArrayMax($cArr, 100000000));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;

$start = microtime(true);
echo 'Calculate max of 10 billion doubles using PHP', PHP_EOL;
var_dump(max($arr));
echo 'Usage time:', (microtime(true) - $start) * 1000, 'ms', PHP_EOL;
