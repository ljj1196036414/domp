<?php
namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\model\index\OrderModel;
class PayController extends Controller{
    public function aliPay(Request $request){
        $oid=$request->get('oid');
        echo "订单id:".$oid;
        //die;

        //根据订单号，查询订单信息，验证订单是否有效（未支付、未删除、未过期）
        //组合参数。调用支付接口 支付
        $param2=[
            'out_trade_no'      => $oid,     //商户订单号
            'product_code'      => 'FAST_INSTANT_TRADE_PAY',
            'total_amount'      => 0.01,    //订单总金额
            'subject'           => '2004-测试订单-'.Str::random(16),
        ];

    }
}