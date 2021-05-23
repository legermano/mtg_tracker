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

class FormatsReport extends TPage
{
    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle(("Formats"));
        $output  = new TRadioGroup('output');

        $this->form->addFields( [new TLabel(_t('Output format'))], [$output]);

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

            $dbInfo = parse_ini_file('app/config/mtg_tracker.ini');

            $params = [
                'format' => [$data->output],
                'locale' => 'en',
                'params' => [],
                'db_connection' => [
                    'driver'   => 'postgres',
                    'username' => $dbInfo['user'],
                    'password' => $dbInfo['pass'],
                    'host'     => $dbInfo['host'],
                    'database' => $dbInfo['name'],
                    'port'     => $dbInfo['port']
                ]
            ];

            $reportName = 'formats_'.(ApplicationTranslator::getLanguage() == 'pt' ? 'ptbr' : 'en');

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