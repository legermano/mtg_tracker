<?php

use Adianti\Base\TStandardForm;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Form\TEntry;
use Adianti\Wrapper\BootstrapNotebookWrapper;
use Adianti\Widget\Container\TNotebook;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TImage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
class CardForm extends TStandardForm
{
    protected $form;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

        //Create the form
        $this->form = new BootstrapFormBuilder('form_Card');
        $this->form->setFormTitle(_t('Card'));

        //Defines the database
        parent::setDatabase('mtg_tracker');

        //Defines the active record
        parent::setActiveRecord('Card');

        //Create the form fields
        $id      = new TEntry('id');
        $name    = new TEntry('name');
        $setCode = new TEntry('setCode');

        $id->setEditable(false);
        $name->setEditable(false);
        $setCode->setEditable(false);

        //Create the notebook
        $notebook = new BootstrapNotebookWrapper( new TNotebook );
        $notebook->setTabsDirection('left');

        TTransaction::open($this->database);

        $cards = Card::getCard($param['originalname'],$param['setcode']);
        $buttonsArray = array();

        foreach ($cards as $key => $card)
        {
            $num         = $key+1;
            $pageMaster  = 'pageM'.$num;
            $$pageMaster = new TTable;
            $pageImage   = 'pageI'.$num;
            $$pageImage  = new TTable;
            $pageDescr   = 'pageD'.$num;
            $$pageDescr = new TTable;

            $notebook->appendPage("<i class='ss ss-fw ss-{$card->keyrunecode} ss-3x ss-{$card->rarity} id='{$num}'></i><b>{$card->setname}</b>",$$pageMaster);

            $image = new TImage($card->image);
            $image->style = 'max-width: 265px;max-height:370px;';

            $row = $$pageImage->addRow();
            $row->addCell($image);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Card Name') . ':'));
            $row->addCell($card->name);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Mana Cost').':'));
            $row->addCell(Card::putSymbols($card->manacost ?? "N/A"));

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Converted Mana Cost').':'));
            $row->addCell( $card->manacost ? number_format(floatval($card->convertedManaCost),0) : 'N/A' );

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Type').':'));
            $row->addCell($card->type);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Card Text').':'));
            $row->addCell(Card::putSymbols(str_replace("\n","</br>",$card->text)));

            if (!empty($card->loyalty))
            {
                $row = $$pageDescr->addRow();
                $row->addCell(new TLabel(_t('Loyalty').':'));
                $row->addCell($card->loyalty);
            }

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Rarity').':'));
            $row->addCell(_t(ucfirst($card->rarity)));

            if ($card->legalities)
            {
                $card->legalities = json_decode($card->legalities, true);
                $card->legalities = array_map('ucfirst',$card->legalities);
                $row = $$pageDescr->addRow();
                $row->addCell(new TLabel(_t('Formats').':'));
                $row->addCell((implode(', ',$card->legalities)));
            }

            if (!empty($card->flavortext))
            {
                $row = $$pageDescr->addRow();
                $row->addCell(new TLabel(_t('Flavor Text').':'));
                $row->addCell("<i>{$card->flavortext}</i>");
            }

            if ($card->artist)
            {
                $row = $$pageDescr->addRow();
                $row->addCell(new TLabel(_t('Artist').':'));
                $row->addCell($card->artist);
            }

            //Normal quantity button
            $btnInc  = 'btnInc'.$num;
            $btnDec  = 'btnDec'.$num;
            $$btnInc = new TButton('increment'.$num);
            $$btnInc->setImage('fas:plus');
            $$btnInc->setAction(new TAction(array($this,'incrementQuantity'),['id'=>$card->uuid,'foil'=>false]));
            $buttonsArray[] = $$btnInc;
            $$btnDec = new TButton('decrement'.$num);
            $$btnDec->setImage('fas:minus');
            $$btnDec->setAction(new TAction(array($this,'decrementQuantity'),['id'=>$card->uuid,'foil'=>false]));
            $buttonsArray[] = $$btnDec;
            $quantity_owned  = 'qnt_owned'.$num;
            $$quantity_owned = new TEntry('qnt_owned');
            $$quantity_owned->style = "text-align:center;width: 100px";
            $$quantity_owned->setId($card->uuid);
            $$quantity_owned->setEditable(false);
            $$quantity_owned->setValue($card->quantity);

            $normalQuantity = new TElement('a');
            $normalQuantity->add($$btnDec);
            $normalQuantity->add($$quantity_owned);
            $normalQuantity->add($$btnInc);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Quantity').':'));
            $row->addCell($normalQuantity);

            //Foil quantity button
            $btnInc  = 'btnIncFoil'.$num;
            $btnDec  = 'btnDecFoil'.$num;
            $$btnInc = new TButton('incrementFoil'.$num);
            $$btnInc->setImage('fas:plus');
            $$btnInc->setAction(new TAction(array($this,'incrementQuantity'),['id'=>$card->uuid,'foil'=>true]));
            $buttonsArray[] = $$btnInc;
            $$btnDec = new TButton('decrementFoil'.$num);
            $$btnDec->setImage('fas:minus');
            $$btnDec->setAction(new TAction(array($this,'decrementQuantity'),['id'=>$card->uuid,'foil'=>true]));
            $buttonsArray[] = $$btnDec;
            $quantity_owned  = 'qnt_owned_foil'.$num;
            $$quantity_owned = new TEntry('qnt_owned');
            $$quantity_owned->style = "text-align:center;width: 100px";
            $$quantity_owned->setId($card->uuid.'_foil');
            $$quantity_owned->setEditable(false);
            $$quantity_owned->setValue($card->quantity_foil);

            $foilQuantity = new TElement('a');
            $foilQuantity->add($$btnDec);
            $foilQuantity->add($$quantity_owned);
            $foilQuantity->add($$btnInc);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Quantity').' Foil:'));
            $row->addCell($foilQuantity);

            //Prices table
            $col_market       = new TDataGridColumn('market',_t('Market'),'center','40%');
            $col_normal_price = new TDataGridColumn('normal','Normal','center','30%');
            $col_foil_price   = new TDataGridColumn('foil','Foil','center','30%');

            $tblPrices  = 'tblPrices'.$num;
            $$tblPrices = new BootstrapDatagridWrapper(new TDataGrid);
            $$tblPrices->width = '50%';
            $$tblPrices->addColumn( $col_market );
            $$tblPrices->addColumn( $col_normal_price );
            $$tblPrices->addColumn( $col_foil_price );
            $$tblPrices->createModel();

            if ($card->prices)
            {
                $card->prices = json_decode($card->prices,true);
                foreach ($card->prices as $market => $prices)
                {
                    $item = new stdClass;
                    $item->market = ucfirst($market);
                    $currency = $prices['currency'];
                    foreach ($prices as $price_type => $price)
                    {
                        if (ApplicationTranslator::getLanguage() == 'pt')
                        {
                            $price = number_format(floatval($price),2,',','.');
                        }
                        else
                        {
                            $price = number_format(floatval($price),2,'.',',');
                        }
                        if ($price_type == 'normal')
                        {
                            $item->normal = "{$price} {$currency}";
                        }
                        else if ($price_type == 'foil')
                        {
                            $item->foil = "{$price} {$currency}";
                        }
                    }

                    $item->normal = $item->normal ?? 'N/A';
                    $item->foil   = $item->foil   ?? 'N/A';

                    $$tblPrices->addItem($item);
                }
            }

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Prices').':'));
            $row->addCell($$tblPrices);

            $row = $$pageMaster->addRow();
            $row->addCell($$pageImage,"vertical-align:top");
            $row->addCell($$pageDescr,"vertical-align:top");

        }
        TTransaction::close();

        $btnBack = new TButton('back');
        //Workaround to get back to the rigth page
        if (array_key_exists('returnLink', $param))
        {
            $btnBack->setAction(new TAction(array($param['returnLink'],'onEdit'),array('key' => $param['setcode'])),_t('Back'));
        }
        else
        {
            $btnBack->setAction(new TAction(array('CardList','onReload')),_t('Back'));
        }
        $btnBack->setImage('far:arrow-alt-circle-left blue');
        $buttonsArray[] = $btnBack;

        $this->form->setFields($buttonsArray);

        $panel = new TPanelGroup();
        $panel->add($notebook);
        $panel->addFooter($btnBack);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml','CardList'));
        $container->add($panel);

        //Add the container to the page
        parent::add($container);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button in the datagrid
     * @param $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
    }

    public static function incrementQuantity($param)
    {
        $elementId = $param['id'];
        if ($param['foil']) {
            $elementId .= '_foil';
        }

        TScript::create("document.getElementById('{$elementId}').value = parseInt(document.getElementById('{$elementId}').value) + 1");
        OwnedCard::incrementQuantity($param['id'],$param['foil']);
    }

    public static function decrementQuantity($param)
    {
        $elementId = $param['id'];
        if ($param['foil']) {
            $elementId .= '_foil';
        }

        TScript::create(
            "val = parseInt(document.getElementById('{$elementId}').value);
             if (val > 0)
             {
                document.getElementById('{$elementId}').value = val - 1;
             }"
        );
        OwnedCard::decrementQuantity($param['id'],$param['foil']);
    }
}