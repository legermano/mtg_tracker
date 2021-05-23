<?php

require_once 'app/reports/JasperGenerate.php';
require_once 'lib/adianti/widget/dialog/TMessage.php';

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Wrapper\TDBMultiSearch;
use Adianti\Wrapper\BootstrapFormBuilder;
use JasperReports\JasperGenerate;

class CardsPriceReport extends TPage
{
    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle(_t("Card's prices"));

        $criteriaSet = new TCriteria;
        $criteriaSet->add(new TFilter('isOnlineOnly', '=', 'f'));

        $sets   = new TDBMultiSearch('sets','mtg_tracker','Set','code','name','name',$criteriaSet);
        $order  = new TRadioGroup('order');
        $output = new TRadioGroup('output');

        $this->form->addFields( [new TLabel(_t('Sets'))],         [$sets]);
        $this->form->addFields( [new TLabel(_t('Order'))],         [$order]);
        $this->form->addFields( [new TLabel(_t('Output format'))],[$output]);

        $sets->setMinLength(1);

        $output->setUseButton();
        $output->addItems( ['pdf' => 'PDF', 'rtf' => 'RTF', 'xls' => 'XLS'] );
        $output->setValue( 'pdf' );
        $output->setLayout('horizontal');

        $order->setUseButton();
        $order->addItems( ['normal ASC' => _t('Normal ascendant'), 'normal DESC' => _t('Normal descendant'), 'foil ASC' => _t('Foil ascendant'), 'foil DESC' => _t('Foil descendant')] );
        $order->setValue( 'normal ASC' );
        $order->setLayout('horizontal');

        $this->form->addAction(_t('Generate'), new TAction([$this, 'onGenerate']), 'fa:download blue');

        parent::add( $this->form );
    }

    public function onGenerate()
    {
        try
        {
            $data = $this->form->getData();

            $order = "ORDER BY {$data->order}";
            $where = "";
            $sets  = "";
            if ($data->sets)
            {
                $sets  = implode(',',$data->sets);
                $set   = implode("','",$data->sets);
                $set   = "'{$set}'";
                $where = "AND card.setcode IN ({$set})";
            }

            $dbInfo = parse_ini_file('app/config/mtg_tracker.ini');

            $params = [
                'format' => [$data->output],
                'locale' => 'en',
                'params' => [
                    'order' => $order,
                    'where' => $where,
                    'sets'  => $sets,
                ],
                'db_connection' => [
                    'driver'   => 'postgres',
                    'username' => $dbInfo['user'],
                    'password' => $dbInfo['pass'],
                    'host'     => $dbInfo['host'],
                    'database' => $dbInfo['name'],
                    'port'     => $dbInfo['port']
                ]
            ];

            $reportName = 'card_prices_'.(ApplicationTranslator::getLanguage() == 'pt' ? 'ptbr' : 'en');

            $jasper = new JasperGenerate($reportName,$params);
            $output = $jasper->generate().".{$data->output}";
            if (file_exists($output))
            {
                parent::openFile($output);

                new TMessage('info', _t('Report successfully generated'));
            }
            else
            {
                throw new Exception(_t('Permission denied').': ' . $output);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}