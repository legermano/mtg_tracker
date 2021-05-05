<?php
    require_once 'init.php';
    $loader = require 'vendor/autoload.php';
    $loader->register();

    use \JsonMachine\JsonMachine;

    $file = JsonMachine::fromFile("AllPrintings.json","/data");
    TTransaction::open("mtg_tracker");
    echo "Começo do loop ".date('H:i:s',time());
    foreach ($file as $key => $data) {
        foreach ($data['cards'] as $card) {
            $c = new Card;
            $c->fromArray($card);

            if (array_key_exists("foreignData", $card)) {
                $allNames = array();
                foreach ($card['foreignData'] as $foreignData) {
                    $allNames['names'][] = $foreignData['name'];
                    if ($foreignData['language'] == 'Portuguese (Brazil)') {
                        if (array_key_exists("flavorText", $foreignData)) {
                            $c->flavorTextPTBR   = $foreignData['flavorText'];
                        }
                        if (array_key_exists("multiverseId", $foreignData)) {
                            $c->multiverseIdPTBR = $foreignData['multiverseId'];
                        }
                        if (array_key_exists("name", $foreignData)) {
                            $c->namePTBR         = $foreignData['name'];
                        }
                        if (array_key_exists("text", $foreignData)) {
                            $c->textPTBR         = $foreignData['text'];
                        }
                        if (array_key_exists("type", $foreignData)) {
                            $c->typePTBR         = $foreignData['type'];
                        }
                    }
                }
                if ($allNames) {
                    $c->allNames = json_encode($allNames, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("availability", $card)) {
                $availability = array();
                foreach ($card['availability'] as $value) {
                    $availability['availability'][] = $value;
                }
                if ($availability) {
                    $c->availability = json_encode($availability, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("colorIdentity", $card)) {
                $c->colorIdentity = implode($card['colorIdentity']);
            }

            if (array_key_exists("colors", $card)) {
                $c->colors = implode($card['colors']);
            }

            if (array_key_exists("identifiers", $card)) {
                foreach ($card['identifiers'] as $key => $value) {
                    $c->$key = $value;
                }
            }

            if (array_key_exists("keywords", $card)) {
                $keywords = array();
                foreach ($card['keywords'] as $value) {
                    $keywords['keywords'][] = $value;
                }
                if ($keywords) {
                    $c->keywords = json_encode($keywords, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("legalities", $card)) {
                $legalities = array();
                foreach ($card['legalities'] as $key => $value) {
                    if ($value == 'Legal') {
                        $legalities['legalities'][] = $key;
                    }
                }
                if ($legalities) {
                    $c->legalities = json_encode($legalities, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("printings", $card)) {
                $printings = array();
                foreach ($card['printings'] as $value) {
                    $printings['printings'][] = $value;
                }
                if ($printings) {
                    $c->printings = json_encode($printings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("purchaseUrls", $card)) {
                $purchaseUrls = array();
                foreach ($card['purchaseUrls'] as $value) {
                    $purchaseUrls['purchaseUrls'][] = $value;
                }
                if ($purchaseUrls) {
                    $c->purchaseUrls = json_encode($purchaseUrls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("rulings", $card)) {
                $rulings = array();
                foreach ($card['rulings'] as $value) {
                    $rulings['rulings'][] = $value;
                }
                if ($rulings) {
                    $c->rulings = json_encode($rulings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("subtypes", $card)) {
                $subtypes = array();
                foreach ($card['subtypes'] as $value) {
                    $subtypes['subtypes'][] = $value;
                }
                if ($subtypes) {
                    $c->subtypes = json_encode($subtypes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("supertypes", $card)) {
                $supertypes = array();
                foreach ($card['supertypes'] as $value) {
                    $supertypes['supertypes'][] = $value;
                }
                if ($supertypes) {
                    $c->supertypes = json_encode($supertypes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("types", $card)) {
                $types = array();
                foreach ($card['types'] as $value) {
                    $types['types'][] = $value;
                }
                if ($types) {
                    $c->types = json_encode($types, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("variations", $card)) {
                $variations = array();
                foreach ($card['variations'] as $value) {
                    $variations['variations'][] = $value;
                }
                if ($variations) {
                    $c->variations = json_encode($variations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
            $c->store();
        }
    }
    TTransaction::close();
    echo "<br> Final do loop ".date('H:i:s',time());
?>