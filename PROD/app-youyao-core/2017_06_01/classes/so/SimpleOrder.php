<?php
/**
 * Created by PhpStorm.
 * User: victorruan
 * Date: 16/3/7
 * Time: 下午5:11
 */

class So_SimpleOrder{
    public $orderId;
    protected $base_info;
    public $bll;


    /**
     * So_Order constructor.
     */
    public function __construct($orderId)
    {
        $this->bll = new Bll_Order_OrderInfo();
        $this->base_info = $this->bll->order_load($orderId);
    }
    public function getHotelId(){
        return $this->base_info->uid;
    }

}