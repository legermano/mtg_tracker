<?php

require_once 'app/reports/JasperGenerate.php';
require_once 'lib/adianti/widget/dialog/TMessage.php';

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Wrapper\TDBMultiSearch;
use Adianti\Wrapper\BootstrapFormBuilder;
use JasperReports\JasperGenerate;

class DecksReport extends TPage
{
    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle(("Decks"));

        $criteriaDeck = new TCriteria;
        $criteriaDeck->add(new TFilter('system_user_id', '=', TSession::getValue('userid')));

        $decks   = new TDBMultiSearch('decks','mtg_tracker','Deck','id','name','name');
        $this->decks = $decks;
        $formats = new TDBMultiSearch('formats','mtg_tracker','Format','id','name','name');
        $this->formats = $formats;
        $output  = new TRadioGroup('output');

        $this->form->addFields( [new TLabel(('Decks'))],           [$decks]);
        $this->form->addFields( [new TLabel(_t('Formats'))],       [$formats]);
        $this->form->addFields( [new TLabel(_t('Output format'))], [$output]);

        $decks->setMinLength(0);
        $formats->setMinLength(0);

        $output->setUseButton();
        $output->addItems( ['pdf' => 'PDF', 'rtf' => 'RTF', 'xls' => 'XLS'] );
        $output->setValue( 'pdf' );
        $output->setLayout('horizontal');

        $this->form->addAction(_t('Generate'), new TAction([$this, 'onGenerate']), 'fa:download blue');

        parent::add( $this->form );
    }

    public function onGenerate()
    {
        try
        {
            $data = $this->form->getData();
            $this->form->setData($data);

            $user_id   = TSession::getValue('userid');
            $user_name = TSession::getValue('username');

            $where = "";
            $decks = "";
            if ($data->decks)
            {
                $decks = implode(',',$data->decks);
                $where = "AND deck.id IN ({$decks}) ";
                $decks = implode(',',$this->decks->getItems());
            }

            $formats = "";
            if ($data->formats)
            {
                $formats = implode(',',$data->formats);
                $where   .= "AND deck.format_id IN ({$formats}) ";
                $formats = implode(',',$this->formats->getItems());
            }

            $dbInfo = parse_ini_file('app/config/mtg_tracker.ini');

            $params = [
                'format' => [$data->output],
                'locale' => 'en',
                'params' => [
                    'user_id'    => $user_id,
                    'user_name'  => $user_name,
                    'where_deck' => $where,
                    'decks'      => $decks,
                    'formats'    => $formats
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

            $reportName = 'decks_'.(ApplicationTranslator::getLanguage() == 'pt' ? 'ptbr' : 'en');

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