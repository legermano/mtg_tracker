<?php
    require_once 'init.php';
    $loader = require 'vendor/autoload.php';
    $loader->register();

    use \JsonMachine\JsonMachine;
    use Adianti\Database\TTransaction;

    //Update the database
    exec('rm -f AllPrintings.json');
    exec('wget https://mtgjson.com/api/v5/AllPrintings.json');

    //Update the JSON of prices
    exec('rm -f AllPrices.json');
    exec('wget https://mtgjson.com/api/v5/AllPrices.json');

    TTransaction::open("mtg_tracker");

    $cards  = JsonMachine::fromFile("AllPrintings.json","/data");
    echo "Começo do loop ".date('H:i:s',time());

    foreach ($cards as $key => $data)
    {
        $s = new Set;
        foreach ($data as $key => $value)
        {
            $key = strtolower($key);
            $s->$key = $value;
        }
        $s->isfoilonly       = $s->isfoilonly       ?? FALSE;
        $s->isforeignonly    = $s->isforeignonly    ?? FALSE;
        $s->isnonfoilonly    = $s->isnonfoilonly    ?? FALSE;
        $s->isonlineonly     = $s->isonlineonly     ?? FALSE;
        $s->ispartialpreview = $s->ispartialpreview ?? FALSE;
        $s->store();
        foreach ($data['cards'] as $card)
        {
            $c = new Card;
            foreach ($card as $key => $value)
            {
                $key = strtolower($key);
                $c->$key = $value;
            }
            $c->setname                 = $s->name;
            $c->originalname            = $c->facename ?? $c->name;
            $c->hasalternativedecklimit = $c->hasalternativedecklimit ?? FALSE;
            $c->hascontentwarning       = $c->hascontentwarning       ?? FALSE;
            $c->hasfoil                 = $c->hasfoil                 ?? FALSE;
            $c->hasnonfoil              = $c->hasnonfoil              ?? FALSE;
            $c->isalternative           = $c->isalternative           ?? FALSE;
            $c->isfullart               = $c->isfullart               ?? FALSE;
            $c->isonlineonly            = $c->isonlineonly            ?? FALSE;
            $c->isoversized             = $c->isoversized             ?? FALSE;
            $c->ispromo                 = $c->ispromo                 ?? FALSE;
            $c->isreprint               = $c->isreprint               ?? FALSE;
            $c->isreserved              = $c->isreserved              ?? FALSE;
            $c->isstarter               = $c->isstarter               ?? FALSE;
            $c->isstoryspotlight        = $c->isstoryspotlight        ?? FALSE;
            $c->istextless              = $c->istextless              ?? FALSE;
            $c->istimeshifted           = $c->istimeshifted           ?? FALSE;
            $c->number                  = preg_replace('/[^0-9]/','', $c->number);

            if (array_key_exists("foreignData", $card))
            {
                $allNames = array();
                foreach ($card['foreignData'] as $foreignData)
                {
                    $allNames['names'][] = $foreignData['name'];
                    if ($foreignData['language'] == 'Portuguese (Brazil)')
                    {
                        if (array_key_exists("flavorText", $foreignData))
                        {
                            $c->flavortextptbr = $foreignData['flavorText'];
                        }
                        if (array_key_exists("multiverseId", $foreignData))
                        {
                            $c->multiverseidptbr = $foreignData['multiverseId'];
                        }
                        if (array_key_exists("name", $foreignData))
                        {
                            $c->nameptbr = $foreignData['name'];
                        }
                        if (array_key_exists("faceName", $foreignData))
                        {
                            $c->facenameptbr = $foreignData['faceName'];
                        }
                        if (array_key_exists("text", $foreignData))
                        {
                            $c->textptbr = $foreignData['text'];
                        }
                        if (array_key_exists("type", $foreignData))
                        {
                            $c->typeptbr = $foreignData['type'];
                        }
                    }
                }
                if ($allNames)
                {
                    $c->allnames = json_encode($allNames, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("availability", $card))
            {
                $availability = array();
                foreach ($card['availability'] as $value)
                {
                    $availability['availability'][] = $value;
                }
                if ($availability)
                {
                    $c->availability = json_encode($availability, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("colorIdentity", $card))
            {
                sort($card['colorIdentity']);
                $coloridentity = implode('","',$card['colorIdentity']);
                if (!empty($coloridentity))
                {
                    $coloridentity = '{"'.$coloridentity.'"}';
                }
                $c->coloridentity = $coloridentity;
            }

            if (array_key_exists("colors", $card))
            {
                $c->colors = implode($card['colors']);
            }

            if (array_key_exists("identifiers", $card))
            {
                foreach ($card['identifiers'] as $key => $value)
                {
                    $key = strtolower($key);
                    $c->$key = $value;
                }
            }

            if (array_key_exists("keywords", $card))
            {
                $keywords = array();
                foreach ($card['keywords'] as $value)
                {
                    $keywords['keywords'][] = $value;
                }
                if ($keywords)
                {
                    $c->keywords = json_encode($keywords, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("legalities", $card))
            {
                $legalities = array();
                foreach ($card['legalities'] as $key => $value)
                {
                    if ($value == 'Legal') {
                        $legalities['legalities'][] = $key;
                    }
                }
                if ($legalities)
                {
                    $c->legalities = json_encode($legalities, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("printings", $card))
            {
                $printings = array();
                foreach ($card['printings'] as $value)
                {
                    $printings['printings'][] = $value;
                }
                if ($printings)
                {
                    $c->printings = json_encode($printings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("purchaseUrls", $card))
            {
                $purchaseUrls = array();
                foreach ($card['purchaseUrls'] as $value)
                {
                    $purchaseUrls['purchaseUrls'][] = $value;
                }
                if ($purchaseUrls)
                {
                    $c->purchaseurls = json_encode($purchaseUrls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("rulings", $card))
            {
                $rulings = array();
                foreach ($card['rulings'] as $value)
                {
                    $rulings['rulings'][] = $value;
                }
                if ($rulings)
                {
                    $c->rulings = json_encode($rulings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("subtypes", $card))
            {
                $subtypes = array();
                foreach ($card['subtypes'] as $value)
                {
                    $subtypes['subtypes'][] = $value;
                }
                if ($subtypes)
                {
                    $c->subtypes = json_encode($subtypes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("supertypes", $card))
            {
                $supertypes = array();
                foreach ($card['supertypes'] as $value)
                {
                    $supertypes['supertypes'][] = $value;
                }
                if ($supertypes)
                {
                    $c->supertypes = json_encode($supertypes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("types", $card))
            {
                $types = array();
                foreach ($card['types'] as $value)
                {
                    $types['types'][] = $value;
                }
                if ($types)
                {
                    $c->types = json_encode($types, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists("variations", $card))
            {
                $variations = array();
                foreach ($card['variations'] as $value)
                {
                    $variations['variations'][] = $value;
                }
                if ($variations)
                {
                    $c->variations = json_encode($variations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
            $c->store();
        }
    }
    TTransaction::close();

    //Get the prices
    $prices = JsonMachine::fromFile("AllPrices.json","/data");
    TTransaction::open("mtg_tracker");
    foreach ($prices as $key => $data)
    {
        try
        {
            $card = new Card($key);
        }
        catch (Exception $e)
        {
            echo "Card not found - UUID {$key} \n\r";
            continue;
        }

        if ($card)
        {
            $save = false;
            $arr = array();

            foreach ($data as $format => $data_value)
            {
                if ($format == 'paper')
                {
                    $paper = $data[$format];

                    foreach ($paper as $market => $prices)
                    {
                        $arr[$market]['currency'] = $prices['currency'];
                        foreach ($prices as $price_type => $value)
                        {
                            if ($price_type == 'retail')
                            {
                                $retail = $value;
                                foreach ($retail as $type => $price)
                                {
                                    $arr[$market][$type] = end($price);
                                    $save = true;
                                }
                            }
                        }
                    }

                    if ($save)
                    {
                        $card->prices = json_encode($arr);
                        $card->store();
                    }
                }
            }
        }
    }
    TTransaction::close();

    echo "\n\r Final do loop ".date('H:i:s',time());
?>