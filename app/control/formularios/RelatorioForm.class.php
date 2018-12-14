<?php
/**
 * RelacaoList Listing
 * @author  <your name here>
 */

class RelatorioForm extends TPage
{
    private $form; // form
    private $formConfig;
    private $fieldlist;
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    private $relatorioButton;
    private $exibeTotal;
    private $first = 0;
    private $gridpack;
    protected $larguraColuna = 0;
    protected $larguraColunaStats = 0;
    
    
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
        
        TSession::setValue('relatorio_existe', 1);
        // create the form fields
        $id = new TDBCombo('id', 'procon_com', 'Relacao', 'id', 'nome');
        $pesquisa_id = new TDBCombo('pesquisa_id', 'procon_com', 'Pesquisa', 'id', 'nome', NULL , NULL , FALSE);
        $estabelecimento_id = new TDBCombo('estabelecimento_id', 'procon_com', 'Estabelecimento', 'id', 'nome');
        $data = new TDate('data_criacao');
        $total = new TCheckButton('exibeTotal');
        
        $pesquisa_id->setDefaultOption('Selecione uma pesquisa');    
    

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

        $this->datagrid->style = 'width: 100%; border-bottom: 1px solid rgba(0, 0, 0, 0.2)';

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        //$this->datagrid->disableDefaultClick();
        

        
        
        // put datagrid inside a form
        $this->formgrid = new TForm('grid');
        $this->formgrid->add($this->datagrid);
        
        $this->relatorioButton = new TButton('relatorio_button');
        $this->relatorioButton->setAction(new TAction([$this, 'onGerarRelatorio']), 'Gerar Relatorio');
        $this->relatorioButton->setImage('fa:clipboard black');
        $this->relatorioButton->style = 'padding: 5px 50px;';
        $this->relatorioButton->class = 'btn btn-sm btn-primary';

        $total = new TCheckButton('exibeTotalConfig');

        $table = new TTable;
        $table->width = '100%';
        $table->style = 'padding: 5px';
        $table->style = 'text-align: center; ';

        $row = $table->addRow();
        $row->style = "border: 1px solid";
        $row->addCell(new TLabel('Exibir Total'))->style = "width: 53%; text-align: right; padding-right: 15px; padding-top: 25px; padding-bottom: 15px;";
        $row->addCell($total)->style = "width: 47%; text-align: left; padding-top: 20px; padding-bottom: 15px;";


        $table1 = new TTable;
        $table1->width = '100%';
        $table1->style = 'padding: 5px';
        $table1->style = 'text-align: center; ';
        $row = $table1->addRow();

        $row->addCell($this->relatorioButton)->style = "width: 100%; content-align: center";

//
        $this->formgrid->add($table);
        $this->formgrid->add($table1);
        $this->formgrid->setFields(array($total, $this->relatorioButton));


        $this->gridpack = new TVBox;
        $this->gridpack->style = 'width: 100%';
        $this->gridpack->add($this->formgrid);


        //$this->gridpack->add($this->relatorioButton)->style = 'text-align: center; margin: 15px; font-size: 1em;';


        $this->transformCallback = array($this, 'onBeforeLoad');
        
        $this->datagrid->clear();
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        //carregar somente depois do pesquisa
        
        //TSession::setValue('RelatorioForm_filter_pesquisa_id',   NULL);
        //if($this->loaded = TRUE)
       // {
           // $container->add(TPanelGroup::pack('Relações', $gridpack, $this->pageNavigation));
        //}'

        $container->add(TPanelGroup::pack('Relações', $this->gridpack, $this->pageNavigation));

        parent::add($container);
    }

    function filtra()
    {
        //executa somente no primeiro load
        $this->first = 1;
        TSession::setValue('Relatorio_filter_data', NULL);

        $this->onSearch();
    }

    public function calculaLarguraColuna($relacoes){
        if(sizeof($relacoes) < 6){
            $this->larguraColuna = 180/(sizeof($relacoes));
        } elseif(sizeof($relacoes) < 11){
            $this->larguraColuna = 200/(sizeof($relacoes));
        } else {
            $this->larguraColuna = 220/(sizeof($relacoes));
        }
    }

    public function transformaReal($valor){
        $preco_transformer = number_format($valor, 2, '', '');
        $preco_transformer = number_format($preco_transformer/100,2,",",".");
        return $preco_transformer;
    }

    function onGerarRelatorio($param)
    {

        try {
            $relacoes = $this->getRelacoes();
            if(!($relacoes)){
                new TMessage('error', 'Selecione pelo menos uma relação.');
                return 0;
            }
            $estabelecimentos = $this->getEstabelecimentos($relacoes);
            $widths = array(50, 10, 20);
            if ($relacoes) {
//                foreach($relacoes as $relacao){
//                    array_push($widths, 15);
//                }
                $this->calculaLarguraColuna($relacoes);

                //tamanho das colunas de acordo com N de relacoes
                for($i = 0; $i < sizeof($relacoes); $i++){
                    array_push($widths, $this->larguraColuna);
                }

                for($i = 0; $i < 4; $i++){
                    array_push($widths, 20);
                }
                 //410 maxsize larguras de coluna
                $pdf = new FPDF('L', 'mm', 'A3');
                $pdf->AddFont('Verdana', '', 'Verdana.php'); //adiciona fonte n é padrão
                $pdf->SetFont('Verdana', '', 8);
                $pdf->Open();
                $pdf->larguraColuna = $this->larguraColuna;
                $pdf->relacoes = $relacoes; //envia para a classe FPDF
                $pdf->AddPage();
                $pdf->SetFillColor(240, 240, 240);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetWidths($widths);
                $pdf->SetFont('Verdana', '', 8);
                //define o caminho e imprime o PDF

                TTransaction::open('procon_com');
                $items = $relacoes[0]->pesquisa->getItems();
                TTransaction::close();

                TTransaction::open('procon_com');
                $repository = new TRepository('RelacaoItem');

                //calcular posição inicial do texto com base no N de relações
                $multiplicador = 2;
                if(sizeof($relacoes)<6){
                    $multiplicador = 4;
                } elseif(sizeof($relacoes)<11) {
                    $multiplicador = 3;
                }
                $posIni = 10*$multiplicador;


                foreach ($items as $item) {
                    $precos = array();
                    $linha = array(
                        utf8_decode($item->nome),
                        utf8_decode($item->quantidade),
                        utf8_decode($item->unidadeMedida->nome)
                        );

                    foreach($relacoes as $relacao){
                        $criteria = new TCriteria;
                        $criteria->add(new TFilter('relacao_id', '=', $relacao->id), TExpression::AND_OPERATOR);
                        $criteria->add(new TFilter('item_id',    '=', $item->id),    TExpression::AND_OPERATOR);
                        $result = $repository->load($criteria);

                        //Transformação de preço (alteração de ponto do BD para virgula no relatorio
                        $valorFormatado = $this->transformaReal($result[0]->preco);

                        //$preco_transformer->number
                        array_push($linha, $valorFormatado);

                        //guardando no array de precos para calculo estatistico
                        array_push($precos, $result[0]->preco);

                        //se for o ultimo, push os dados
                        if(!next($relacoes)) {
                            $max = max($precos);
                            $min = min($precos);
                            $media = $this->transformaReal(array_sum($precos)/count($precos));
                            if($min != 0)
                                $variacao = ($max - $min) / $min;

                            if($min == 0){
                                array_push($linha, '0,00');
                                array_push($linha, $max);
                                array_push($linha, utf8_decode('Nulo'));
                                array_push($linha, $media);
                            } else {
                                array_push($linha, $min);
                                array_push($linha, $max);
                                array_push($linha, round($variacao*100) . '%');
                                array_push($linha, $media);
                            }
                        }
                    }
                    $pdf->SetX($posIni);
                    $pdf->Row($linha);
                }
                TTransaction::close();

                if(key_exists('exibeTotalConfig', $param)){
                    //gera o total de cada relação
                    $totais = $this->geraTotal($relacoes);
                    //preenche as 3 primeiras celulas da linha do total
                    array_unshift($totais, 'R$');
                    array_unshift($totais, '');
                    array_unshift($totais, 'Total');

                    $pdf->SetFont('Verdana', '', 9);
                    $pdf->SetTextColor(40, 100, 20);
                    $pdf->Row($totais);
                }

                $file_path = 'app/output/procon.pdf';
                $this->imprimePdf($pdf, $file_path);
            }
        } catch (Exception $e)
        {
            new TMessage('error', '<b>Erro!</b><br>' . $e->getMessage());
            TTransaction::rollback();
        }
    }

    function geraTotal($relacoes)
    {
        $repository = new TRepository('RelacaoItem');
        TTransaction::open('procon_com');

        $totais = array();
        foreach($relacoes as $relacao)
        {
            $criteria = new TCriteria;
            $criteria->add(new TFilter('relacao_id', '=', $relacao->id));
            $result = $repository->load($criteria);

            $total = 0;
            foreach($result as $obj){
                $total += $obj->preco;
            }
            $totalFormatado = number_format($total, 2, '', '');
            $totalFormatado = number_format($totalFormatado/100,2,",",".");

            array_push($totais, $totalFormatado);
        }
        return $totais;
        TTransaction::close();
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
        } else {
            //ELSE existe para que não sejam exibidos valores no datagrid (para nao misturar pesquisas diferentes)
            $filter = new TFilter('pesquisa_id', '=', "99999999"); // create the filter
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
            //verifica se primeiro load

            
            if(((TSession::getValue('Relatorio_filter_data')) == NULL) and $this->first == 0)
               $this->filtra();

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
        if($objects){
            $this->formgrid->setFields($gridfields);
        }
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
