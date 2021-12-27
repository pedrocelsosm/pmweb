<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class OrderItemsForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['OrderItemsList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_pmweb');
        $this->setActiveRecord('OrderItems');

        $this->form = new BootstrapFormBuilder('form_OrderItems');
        $this->form->setFormTitle('OrderItems');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $order_id = new TEntry('order_id');
        $order_date = new TDateTime('order_date');
        $product_sku = new TEntry('product_sku');
        $size = new TEntry('size');
        $color = new TEntry('color');
        $quantity = new TEntry('quantity');
        $price = new TEntry('price');

        $this->form->addFields([ new TLabel('Order Id')], [$order_id]);
        $this->form->addFields([ new TLabel('Order Date')], [$order_date]);
        $this->form->addFields([ new TLabel('Product Sku')], [$product_sku]);
        $this->form->addFields([ new TLabel('Size')], [$size]);
        $this->form->addFields([ new TLabel('Color')], [$color]);
        $this->form->addFields([ new TLabel('Quantity')], [$quantity]);
        $this->form->addFields([ new TLabel('Price')], [$price]);

        $order_date->setMask('dd/mm/yyyy');
        $order_date->setDatabaseMask('yyyy-mm-dd');

        $product_sku->addValidation('Product Sku', new TRequiredValidator);
        $color->addValidation('Color', new TRequiredValidator);

        $order_id->setSize('100%');
        $order_date->setSize('100%');
        $product_sku->setSize('100%');
        $size->setSize('100%');
        $color->setSize('100%');
        $quantity->setSize('100%');
        $price->setSize('100%');

        $order_id->setEditable(FALSE);

        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');

        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);

    }

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}