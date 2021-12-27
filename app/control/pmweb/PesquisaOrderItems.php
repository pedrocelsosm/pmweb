<?php

class PesquisaOrderItems extends TPage
{
    private $form; // form
    protected $data;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PesquisaOrderItems_report');
        $this->form->setFormTitle( 'Pesquisa Order Items' );
        
        $data_inicio = new TDate('data_inicio');
        $data_fim = new TDate('data_fim');
        
                      
        // create the form fields
        $this->form->addFields( [ new TLabel('Data Início', 'red') ], [ $data_inicio] ,
                                [ new TLabel('Data Fim', 'red') ], [ $data_fim ] ); 
                                
        //set Mask
        $data_inicio->setMask('dd/mm/yyyy');
        $data_fim->setMask('dd/mm/yyyy');
        
        $output_type  = new TRadioGroup('output_type');
        $this->form->addFields( [new TLabel('Mostrar em:')],   [$output_type] );
        
        // define field properties
        $output_type->setUseButton();
        $options = ['html' =>'HTML', 'pdf' =>'PDF', 'rtf' =>'RTF', 'xls' =>'XLS'];
        $output_type->addItems($options);
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        
        $this->form->addAction( 'Gerar Relatório', new TAction(array($this, 'onGenerate')), 'fa:download blue');
                
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        
        parent::add($vbox);      
}

    /**
     * method onGenerate()
     * Executed whenever the user clicks at the generate button
     */
    function onGenerate()
    {
        try
        {
            // get the form data into an active record Customer
            $this->data = $this->form->getData();
            $this->form->setData($this->data);
            
            $format = $this->data->output_type;
            
            // open a transaction with database ''
            $source = TTransaction::open('db_pmweb');
                        
            // define the query
            $query = 'SELECT order_items.order_id, order_items.order_date, order_items.product_sku, order_items.size, order_items.color, order_items.quantity, order_items.price
                      FROM order_items
                      WHERE order_items.order_date BETWEEN :data_inicio AND :data_fim ';
                                         
            if ( !empty($this->data->order_date) )
            {
                $query .= " and order_items.order_id = {$this->data->order_date}";
            }
            
            $filters = [];
            $filters['data_inicio'] = TDate::date2us($this->data->data_inicio);
            $filters['data_fim'] = TDate::date2us($this->data->data_fim);
                        
            $rows = TDatabase::getData($source, $query, null, $filters );
            
            if ($rows)
            {
                $widths = [200,270,150,80,140,100,100];
                
                switch ($format)
                {
                    case 'html':
                        $table = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $table = new TTableWriterPDF($widths);
                        break;
                    case 'rtf':
                        $table = new TTableWriterRTF($widths);
                        break;
                    case 'xls':
                        $table = new TTableWriterXLS($widths);
                        break;
                }
                
                if (!empty($table))
                {
                    // create the document styles
                    $table->addStyle('header', 'Helvetica', '16', 'B', '#ffffff', '#4B8E57');
                    $table->addStyle('title',  'Helvetica', '10', 'B', '#ffffff', '#6CC361');
                    $table->addStyle('datap',  'Helvetica', '10', '',  '#000000', '#E3E3E3', 'LR');
                    $table->addStyle('datai',  'Helvetica', '10', '',  '#000000', '#ffffff', 'LR');
                    $table->addStyle('footer', 'Helvetica', '10', '',  '#2B2B2B', '#B5FFB4');
                    
                    $table->setHeaderCallback( function($table) {
                        $table->addRow();
                        $table->addCell('Pesquisa Order Items', 'center', 'header', 7);
                        // Pega data inicio e da fim imprimindo no relatório 
                        $table->addRow();
                        $table->addCell('Data Início: ' . $this->data->data_inicio . ' - Data Fim: ' . $this->data->data_fim, 'center','title',7);
                       
                        $table->addRow();
                        $table->addCell('Order Id', 'center', 'title');
                        $table->addCell('Order Date', 'center', 'title');
                        $table->addCell('Product Sku', 'center', 'title');
                        $table->addCell('Size', 'center', 'title');
                        $table->addCell('Color', 'center', 'title');
                        $table->addCell('Quantity', 'center', 'title');
                        $table->addCell('Price', 'center', 'title');
                    });
                    
                    $table->setFooterCallback( function($table) {                        
                        $table->addRow();                                            
                        $table->addCell(date('d/m/Y h:i:s'), 'center', 'footer', 7);                        
                    });                    
                    
                    // controls the background filling
                    $colour= FALSE;                    
                    //Iniciada variável ValorTotal igual a zero
                    $price = 0;
                    $quantity = 0;
                    // data rows
                    foreach ($rows as $row)
                    {                       
                        $style = $colour ? 'datap' : 'datai';
                        // Para converter data_vencimento no relatório
                        $row['order_date'] = TDate::date2br($row['order_date']);
                        
                        $table->addRow();
                        $table->addCell($row['order_id'], 'left', $style);
                        $table->addCell($row['order_date'], 'center', $style);
                        $table->addCell($row['product_sku'], 'center', $style);
                        $table->addCell($row['size'], 'center', $style);
                        $table->addCell($row['color'], 'left', $style);
                        $table->addCell($row['quantity'], 'center', $style);
                        $table->addCell($row['price'], 'rigth', $style);
                        
                        $quantity += $row['quantity'];
                        $price += $row['price'];
                        
                        $colour = !$colour;
                    }
                    
                    $table->addRow();
                    $table->addCell('Total Price: ', 'left', 'footer', 1);                    
                    $table->addCell($quantity, 'rigth', 'footer', 5 );
                    $table->addCell(number_format($price,2,',','.'), 'rigth', 'footer', 7);
                    
                    $output = "app/output/tabular.{$format}";
                    
                    // stores the file
                    if (!file_exists($output) OR is_writable($output))
                    {
                        $table->save($output);
                        parent::openFile($output);
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . $output);
                    }
                    
                    // shows the success message
                    new TMessage('info', 'Relatório gerado. Por favor, ative popups no navegador.');
                }
            }
            else
            {
                new TMessage('error', 'Registros não encontrado');
            }
    
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
