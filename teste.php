<?php
require_once 'init.php';
require_once 'app/reports/JasperGenerate.php';
require 'vendor/autoload.php';

use JasperReports\JasperGenerate;

// $input = __DIR__ .'/vendor/geekcom/phpjasper/examples/owned_cards.jasper';
// $output = __DIR__ .'/vendor/geekcom/phpjasper/examples';
// $options = [
//     'format' => ['pdf'],
//     'locale' => 'en',
//     'params' => [
//         'user_id'=>4,
//         'card_name'=>'%%',
//         'sets'=>'STA,KHM',
//         'language'=>'PTBR',
//         'user_name' => 'Lucas Germano',
//         'cards' => ''
//     ],
//     'db_connection' => [
//         'driver' => 'postgres',
//         'username' => 'mtg',
//         'password' => 'mtg',
//         'host' => '127.0.0.1',
//         'database' => 'mtg_tracker',
//         'port' => '5432'
//     ]
// ];

// $jasper = new JasperGenerate('owned_cards.jasper',$options);
// $jasper->generate();

// $jasper = new PHPJasper;
// $jasper->process($input,$output,$options)->execute();die;
// $output = $jasper->listParameters($input)->execute();
// var_dump($output);
// foreach ($output as $param) {
//     echo $param . "<br>";
// }

// try
// {
//     $jasper->process(
//         $input,
//         $output,
//         $options
//     )->execute();
// }
// catch(ErrorCommandExecutable $e)
// {
//     echo $e->getMessage();
// }

function findSum($A, $N)
{
    echo $N."\n";
    if ($N <= 0)
        return 0;
    return (findSum($A, $N - 1) +
                    $A[$N - 1]);
}

// Driver code
$A = array(1, 2, 3, 4, 5);
$N = sizeof($A);
echo findSum($A, $N);
