<?php
/**
 * RelacaoList Listing
 * @author  <your name here>
 */

class RelatorioForm extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    private $relatorioButton;
    
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/estiloformcampo.css');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Relacao');
        $this->form->setFormTitle('Relatório');
        

        // create the form fields
        $id = new TDBCombo('id', 'procon_com', 'Relacao', 'id', 'nome');
        $pesquisa_id = new TDBCombo('pesquisa_id', 'procon_com', 'Pesquisa', 'id', 'nome', NULL , NULL , FALSE);
        $estabelecimento_id = new TDBCombo('estabelecimento_id', 'procon_com', 'Estabelecimento', 'id', 'nome');
        $data = new TDate('data_criacao');
        
        $pesquisa_id->setDefaultOption('Selecione uma pesquisa');    
    
        //Clear Section
        TSession::setValue('Relatorio_filter_data',   NULL);
        
        // add the fields
        $this->form->addFields( [ new TLabel('Pesquisa') ], [ $pesquisa_id ] );

        // set sizes
        $pesquisa_id->setSize('70%');
        //$estabelecimento_id->setSize('70%');
        //$data->setSize('70%');
        
        //$data->setMask('dd/mm/yyyy');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Relatorio_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center', '5%' );
        $column_pesquisa = new TDataGridColumn('pesquisa->nome', 'Pesquisa', 'left', '40%');
        $column_estabelecimento = new TDataGridColumn('estabelecimento->nome', 'Estabelecimento', 'left', '40%');
        $column_data = new TDataGridColumn('data_criacao', 'Data', 'left', '15%');
        
        $column_data->setTransformer( function($value, $object, $row) {
            if ($value)
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
            return $value;
        });
    
    
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_pesquisa);
        $this->datagrid->addColumn($column_estabelecimento);
        $this->datagrid->addColumn($column_data);


        // creates the datagrid column actions
        $column_data->setAction(new TAction([$this, 'onReload']), ['order' => 'data_criacao']);
             
       
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        //$this->datagrid->disableDefaultClick();
        
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);    
        
        $this->relatorioButton = new TButton('relatorio_button'); 
        $this->relatorioButton->setAction(new TAction([$this, 'onGerarRelatorio']), 'Gerar Relatorio');
        $this->relatorioButton->setImage('fa:clipboard blue');
        $this->datagrid->style = 'width: 100%; border-bottom: 1px solid rgba(0, 0, 0, 0.2)'; 
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        //$this->datagrid->disableDefaultClick();
        
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);    
        
        $this->relatorioButton = new TButton('relatorio_button'); 
        $this->relatorioButton->setAction(new TAction([$this, 'onGerarRelatorio']), 'Gerar Relatorio');
        $this->relatorioButton->setImage('fa:clipboard black');
        $this->relatorioButton->style = 'padding: 5px 50px;';
        $this->relatorioButton->class = 'btn btn-sm btn-primary';
        $this->formgrid->addField($this->relatorioButton);
    
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
            
        $gridpack->add($this->relatorioButton)->style = 'text-align: center; margin: 15px; font-size: 1em;';
        
        $this->transformCallback = array($this, 'onBeforeLoad');
        
        $this->datagrid->clear();
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        //carregar somente depois do pesquisa
        
        //TSession::setValue('RelatorioForm_filter_pesquisa_id',   NULL);
        //if($this->loaded = TRUE)
       // {
           // $container->add(TPanelGroup::pack('Relações', $gridpack, $this->pageNavigation));
        //}'
        if(TSession::getValue('RelatorioForm_filter_pesquisa_id')){
            $container->add(TPanelGroup::pack('Relações', $gridpack, $this->pageNavigation));
        }
        parent::add($container);
    }
    function onGerarRelatorio()
    {
        try
        {
            $relacoes = $this->getRelacoes();
            $estabelecimentos = $this->getEstabelecimentos($relacoes);
            echo '<pre>', var_dump($estabelecimentos), '</pre>';
            if ($relacoes) {
                $widths = array(80, 30, 50); //410 maxsize larguras de coluna
                $pdf = new FPDF('L', 'mm', 'A3');
                $pdf->AddFont('Verdana', '', 'Verdana.php'); //adiciona fonte n é padrão
                $pdf->SetFont('Verdana', '', 8);
                $pdf->Open();
                $pdf->relacoes = $relacoes; //seleciona o tipo de header na classe FPDF
                $pdf->AddPage();
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetWidths($widths);
                $pdf->SetFont('Verdana', '', 8);
                //define o caminho e imprime o PDF

                TTransaction::open('procon_com');
                foreach ($relacoes as $relacao) {
                    $pdf->Row(array(
                        utf8_decode($relacao->pesquisa->nome),
                        utf8_decode($relacao->estabelecimento->nome),
                        utf8_decode($relacao->data_criacao)
                    ));
                }
                TTransaction::close();
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);
                $pdf->cell(185,5,"asdasd",1,1,'L',0);

                $file_path = 'app/output/procon.pdf';
                $this->imprimePdf($pdf, $file_path);
            }
        } catch (Exception $e)
        {
            new TMessage('error', '<b>Erro!</b><br>' . $e->getMessage());
            TTransaction::rollback();
        }
    }


    function imprimePdf($pdf, $file_path){
        $file = $file_path;
        if (!file_exists($file) OR is_writable($file))
        {
            $pdf->Output($file);
            parent::openFile($file);
        }
        else
        {
            throw new Exception('<b>Arquivo não gerado!</b><br>' . $file);
        }
    }

    public function getEstabelecimentos($relacoes){
        $estabelecimentos = array();

        TTransaction::open('procon_com');
        foreach($relacoes as $relacao){
            $id = $relacao->estabelecimento_id;
            $obj = new Estabelecimento($id);
            array_push($estabelecimentos, $obj);
        }
        TTransaction::close();
        return $estabelecimentos;
    }

    function getRelacoes(){
        $relacoes = array();
        
        $data = $this->formgrid->getData(); // get selected records from datagrid
        $this->formgrid->setData($data); // keep form filled
        
        TTransaction::open('procon_com');
        if ($data)
        {            
            // get the record id's
            foreach ($data as $index => $check)
            {
                if ($check == 'on')
                {                    
                    $relacao = new Relacao(substr($index,5));
                    array_push($relacoes, $relacao);                   
                }
            }
        }
        TTransaction::close();
        return $relacoes;
    }

    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('RelatorioForm_filter_pesquisa_id',   NULL);

        if (isset($data->pesquisa_id) AND ($data->pesquisa_id)) {
            $filter = new TFilter('pesquisa_id', '=', "$data->pesquisa_id"); // create the filter
            TSession::setValue('RelatorioForm_filter_pesquisa_id',   $filter); // stores the filter in the session
        }

        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Relatorio_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'procon_com'
            TTransaction::open('procon_com');
            
            // creates a repository for Relacao
            $repository = new TRepository('Relacao');
            $limit = 100;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('RelatorioForm_filter_pesquisa_id')) {
                $criteria->add(TSession::getValue('RelatorioForm_filter_pesquisa_id')); // add the session filter
            }

            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();

          

            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    

    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page   
        
        foreach ($objects as $object)
            {
            $object->check = new TCheckButton('check' . $object->id);
            $object->check->setIndexValue('on');
            $gridfields[] = $object->check; // important
        }
        
        $this->formgrid->setFields($gridfields);
    }

    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
