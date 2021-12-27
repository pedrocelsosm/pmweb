<?php

class OrderItemsDashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        
        $div = new TElement('div');
        $div->class = "row";
        
        try
        {
            
            TTransaction::open('db_pmweb');
            
            $price          = OrderItems::where('price','>',0)->sumBy('price');
            $quantity       = OrderItems::where('quantity','>',0)->sumBy('quantity');
            $ticket         = OrderItems::where('order_id','>',0)->countBy('order_id');
            $average_price  = OrderItems::where('price','>',0)->avgBy('price');

            $average_ticket = $price / $quantity;
          
            TTransaction::close();
            
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/info-box.html');
            $indicator4 = new THtmlRenderer('app/resources/info-box.html');
            $indicator5 = new THtmlRenderer('app/resources/info-box.html');
            
            $indicator1->enableSection('main', ['title' => 'Price', 'icon' => 'money-bill', 'background' => 'blue',
                                                'value' => 'R$ ' . number_format($price, 2,',','.') ] );
                                                
            $indicator2->enableSection('main', ['title' => 'Total Product       ', 'icon' => 'money-bill', 'background' => 'green',
                                               'value'  => $quantity ] );
            
            $indicator3->enableSection('main', ['title' => 'Quantity', 'icon' => 'money-bill', 'background' => 'orange',
                                                'value' => $ticket ] );                                    
            
            $indicator4->enableSection('main', ['title' => 'Average Price', 'icon' => 'money-bill', 'background' => 'red',
                                               'value'  => 'R$ ' . number_format($average_price,2,',','.') ] );
            
            $indicator5->enableSection('main', ['title' => 'Average Ticket', 'icon' => 'money-bill', 'background' => 'pink',
                                               'value'  => 'R$ ' . number_format($average_ticket,2,',','.') ] );
            
            
            $div->add( TElement::tag('div', $indicator1, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $indicator2, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $indicator3, ['class' => 'col-sm-6']) );
            $div->add( TElement::tag('div', $indicator4, ['class' => 'col-sm-6']) ); 
            $div->add( TElement::tag('div', $indicator5, ['class' => 'col-sm-6']) );     
                       
            $table1 = TTable::create( [ 'class' => 'table table-striped table-hover', 'style' => 'border-collapse:collapse' ] );
            $table1->addSection('thead');
            $table1->addRowSet(' Order Id', 'Price', 'Quantity' );
            
            if ($price)
            {
               
                $table1->addSection('tbody');
                
                $table1->addSection('tfoot')->style = 'color:blue';
                $row = $table1->addRow();
                $row->addCell( 'Total' );
                $row->addCell('R$&nbsp;' . number_format($price, 2,',','.'))->style = 'text-align:left';
                $row->addCell($quantity)->style = 'text-align:left';
            }
            $div->add( TElement::tag('div', TPanelGroup::pack('Price Total', $table1), ['class' => 'col-sm-6']) );
            
            $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $vbox->add($div);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
        parent::add($vbox);
    }
}
