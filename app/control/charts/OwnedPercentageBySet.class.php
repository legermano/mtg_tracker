<?php

use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Template\THtmlRenderer;

class OwnedPercentageBySet extends TPage
{
    public function __construct($system_user_id)
    {
        parent::__construct();

        $this->html = new THtmlRenderer('app/resources/google_bar_chart.html');

        $sql = "SELECT set.name, set.totalsetsize, count(distinct(card.number)) as quantity_owned
                  FROM owned_card
            INNER JOIN card ON (owned_card.card_uuid = card.uuid)
            INNER JOIN set  ON (card.setcode = set.code)
                 WHERE owned_card.system_user_id = {$system_user_id}
                   AND (owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
              GROUP BY set.code
              ORDER BY set.releaseDate ASC";

        TTransaction::open('mtg_tracker');
        $conn = TTransaction::get();
        $sth  = $conn->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll();
        TTransaction::close();

        $data = [];
        $data[] = ['Set',_t('Owned'),_t('Not owned')];
        $numRows = count($results);
        $height  = $numRows * 80;
        foreach ($results as $result) {
            $data[] = [$result['name'],$result['quantity_owned'], $result['totalsetsize']];
        }

        $this->html->enableSection(
            'main',
            [
             'data'    => json_encode($data),
             'width'   => '100%',
             'height'  => $height.'px',
             'stacked' => 'percent',
             'title'   => _t('Owned cards percentage'),
             'xtitle'  => '',
             'ytitle'  => _t('Sets'),
             'uniqid'  => uniqid()
            ]
        );

        parent::add($this->html);
    }
}