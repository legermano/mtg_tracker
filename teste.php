<?php
    require_once 'init.php';
    $loader = require 'vendor/autoload.php';
    $loader->register();

    use \JsonMachine\JsonMachine;

    $file = JsonMachine::fromFile("AllPrices.json","/data");
    TTransaction::open("mtg_tracker");
    echo "Começo do loop ".date('H:i:s',time());
    foreach ($file as $key => $data) {
        $arr = array();
        $paper = $data['paper'];

        foreach ($paper as $market => $prices) {
            $arr[$market]['currency'] = $prices['currency'];
            foreach ($prices as $price_type => $value) {
                if ($price_type == 'retail') {
                    $retail = $value;
                    foreach ($retail as $type => $price) {
                        $arr[$market][$type] = end($price);
                    }
                }
            }
        }

        $criteria = new TCriteria;
        $criteria->add(new TFilter('card_uuid','=',$key));

        $cardPrices = CardPrice::getObjects($criteria);
        if ($cardPrices)
        {
            foreach ($cardPrices as $cardPrice) {
                $cardPrice->prices = json_encode($arr);
                $cardPrice->store();
            }
        }
        else
        {
            $cardPrice = new CardPrice;
            $cardPrice->card_uuid = $key;
            $cardPrice->prices = json_encode($arr);
            $cardPrice->store();
        }
    }
    TTransaction::close();
    echo "Final do loop ".date('H:i:s',time());
?>