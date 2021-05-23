<?php

namespace JasperReports;

use Adianti\Registry\TSession;
use PHPJasper\PHPJasper;

class JasperGenerate
{
    private $reportName;
    private $params;

    /**
     * Class constructor
     *
     * @param String $reportName Name of the report
     * @param Array  $params     Parameters to generate the report
     *
     * @author Lucas Germano <lucas.germano@univates.br>
     */
    public function __construct($reportName, $params)
    {
        // store the properties
        $this->reportName = $reportName;
        $this->params = $params;
    }

    /**
     * Generate the report
     *
     * @return String File output
     *
     * @author Lucas Germano <lucas.germano@univates.br>
     */
    public function generate()
    {
        $input  = __DIR__."/{$this->reportName}";
        $output = "/var/www/html/mtg_tracker/app/output/{$this->reportName}_".TSession::getValue('userid');

        $jasper = new PHPJasper;
        $command = $jasper->process($input,$output,$this->params);
        $command->execute();

        return $output;
    }
}