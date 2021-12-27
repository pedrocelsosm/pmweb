<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class OrderItemsList extends TPage
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;

    use \Adianti\Base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('db_pmweb');
        $this->setActiveRecord('OrderItems');
        $this-> setDefaultOrder('order_id', 'asc');
        $this->setLimit(10);

        $this->addFilterField('order_id', '=', 'order_id');
        $this->addFilterField('size', 'like', 'size');
        $this->addFilterField('color', 'like', 'color');

        $this->form = new BootstrapFormBuilder('form_search_OrderItems');
        $this->form->setFormTitle('OrderItems');

        $order_id = new TEntry('order_id');
        $order_date = new TEntry('order_date');
        $product_sku = new TEntry('produto_sku');
        $size = new TEntry('size');
        $color = new TEntry('color');
        $quantity = new TEntry('quantity');
        $price = new TEntry('price');

        $this->form->addFields([ new TLabel('Order ID') ], [ $order_id]);
        $this->form->addFields([ new TLabel('Order Date') ], [ $order_date]);
        $this->form->addFields([ new TLabel('Product Sku') ], [ $product_sku ]);
        $this->form->addFields([ new TLabel('Size') ], [ $size ]);
        $this->form->addFields([ new TLabel('Color') ], [ $color ]);
        $this->form->addFields([ new TLabel('Quantity') ], [ $quantity ]);

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data_') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['OrderItemsForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';

        //Criar as colunas
        $column_order_id = new TDataGridColumn('order_id', 'Order Id', 'center', '10%');
        $column_order_date = new TDataGridColumn('order_date', 'Order Date', 'left');
        $column_product_sku = new TDataGridColumn('product_sku', 'Produto Sku', 'left');
        $column_size = new TDataGridColumn('size', 'Size', 'left');
        $column_color = new TDataGridColumn('color', 'Color', 'left');
        $column_quantity = new TDataGridColumn('quantity', 'Quantity', 'right');
        $column_price = new TDataGridColumn('price', 'Price', 'right');

        //Faz a soma das colunas price e quantity
        $column_price->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        $column_quantity->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        $this->datagrid->addColumn($column_order_id);
        $this->datagrid->addColumn($column_order_date);
        $this->datagrid->addColumn($column_product_sku);
        $this->datagrid->addColumn($column_size);
        $this->datagrid->addColumn($column_color);
        $this->datagrid->addColumn($column_quantity);
        $this->datagrid->addColumn($column_price);

        //Formata price na datagrid
        $format_value = function($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_price->setTransformer( $format_value );

        $column_order_id->setAction(new TAction([$this, 'onReload']), ['order' => 'order_id']);
        $column_order_date->setAction(new TAction([$this, 'onReload']), ['order' => 'order_date']);
        $column_product_sku->setAction(new TAction([$this, 'onReload']), ['order' => 'product_sku']);

        //Convert data inicio no datagrids
        $column_order_date  ->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        $action1 = new TDataGridAction(['OrderItemsForm', 'onEdit'], ['order_id' => '{order_id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['order_id' => '{order_id}']);

        $this->datagrid->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->datagrid->addAction($action2, _t('Delete'), 'fa:trash red');

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table blue');
        $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf red');
        $panel->addHeaderWidget($dropdown);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }
}