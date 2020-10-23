<?php
namespace App\Http\Controllers\Index;
use App\Http\Controllers\Controller;
use App\model\index\CartModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Redis;
use App\model\index\GoodsModel;
use Encore\Admin\Middleware\Session;
use GuzzleHttp\Client;
use App\model\index\OrderModel;
use Illuminate\Support\Str;
use App\model\index\OrderGoodsModel;
class GoodsController extends Controller
{
    //首页
    public function index(){
        return view('index/goods/index');
    }
    //列表页
    public function search(){
        return view('index/goods/search');
    }
    public function detail(Request $request){
        $goods_id=$request->get('id');
        $goods=GoodsModel::find($goods_id);
        if(empty($goods)){
            return view('goods.404');//商品不存在
        }
        if($goods->is_delete==1){
            return view('goods.delete');//商品已下架
        }
    }
    //详情页
    public function item(){
        return view('index/goods/item');
    }
    //商品信息存入redis
    public function iteminfo(Request $request){
        $goods_id=$request->get('goods_id');
        //查询缓存
        //h:goods_info: 缓存名称
        //.$g['goods_id'] 缓存值  是id值
        $key='h:goods_info:'.$goods_id;
        $g=Redis::hGetAll($key);
        if($g){
            echo "有缓存，执行Redis";
        }else{
            echo "无缓存，查询数据库";
            //查询数据库
            //根据id查询一条数据
            $goods_info=GoodsModel::find($goods_id);
            if(empty($goods_info)){
                echo '商品不存在';
                die;
            }
            //hMset c存缓存(支持数组);
            $g=$goods_info->toArray();
            Redis::hMset($key,$g);
            echo "数据存入Redis中";
        }

        //dd($g);
        //dd(session('userInfo'));
        return view('index/goods/item',['data'=>$g]);
    }
    //ajax加入购物车
    public function car(Request $request){
        $goods_id=$request->get('goods_id');

        $goods_num=GoodsModel::find($goods_id)->value('goods_number');



        //购物车保存商品
        $uid=session('user.uid');
        //dd($uid);
        if(empty($uid)){
            $data=[
                'erron'=>4001,
                'msg'=>'请先登录'
            ];
            echo json_encode($data);die;
        }
        //dd($uid);
        $cart_info=[
            'goods_id'=>$goods_id,
            'uid'=>$uid,
            'goods_num'=>$goods_num,
            'add_time'=>time()
        ];
        //dd($cart_info);
        $data=[
            'erron'=>0,
            'msg'=>'成功加入购物车'
        ];

        $res=CartModel::insertGetId($cart_info);
        if($res>0){
            $data=[
                'erron'=>0,
                'msg'=>'成功加入购物车'
            ];
            echo json_encode($data);
        }else{
            $data=[
                'erron'=>50001,
                'msg'=>'加入购物车失败'
            ];
            echo json_encode($data);
        }

    }
    public function cart(){
        $uid=session('user.uid');
        if(empty($uid)){
          return redirect('goods/cart');
        }
        $list=CartModel::where(['uid'=>$uid])->get()->toArray();
        foreach ($list as $k=>$v){
            $goods[]=GoodsModel::find($v['goods_id'])->toArray();
        }
        //dd($goods);
        return view('index/goods/cart',['data'=>$goods]);

    }
    //生成订单
    public function add(){
        //TODO 获取购物车中的商品（根据当前用户id）
        $uid=session('user.uid');
        //dd($uid);
        $cart_goods=CartModel::where(['uid'=>$uid])->get();
       //echo '<pre>';print_r($cart_goods->toArray());echo '</pre>';
        if(empty($cart_goods)){}//购物车为空
        $cart_goods_arr=$cart_goods->toArray();
        //TODO 生成订单号 计算订单总价 记录订单信息（订单表orders）
        $total=0;
        foreach($cart_goods_arr as $k=>$v){
            $g=GoodsModel::find($v['goods_id']);
            $total+=$g->shop_price;
            $v['goods_price']=$g->shop_price;
            $v['goods_name']=$g->goods_name;
            $order_goods[]=$v;
        }
        $order_data=[
            'order_sn' => strtolower(Str::random(20)),
            'user_id' =>$uid,
            'order_amount' => $total,
            'add_time' => time(),
        ];
        $oid=OrderModel::insertGetId($order_data);//订单入库
       // echo '<pre>';print_r($order_goods);echo '</pre>';die;
        foreach ($order_goods as $k=>$v){
            $goods=[
                'order_id' => $oid,
                'goods_id' => $v['goods_id'],
                'goods_name' => $v['goods_name'],
                'goods_price' => $v['goods_price']
            ];
            OrderGoodsModel::insertGetId($goods);
        }
       //清空购物车
        CartModel::where(['uid'=>$uid])->delete();
        //跳转支付
        return redirect('pay/ali?oid='.$oid);
    }
        //guzzlelesr 城市天气
    public function guzzleIesr1(){
        $data = 'https://devapi.qweather.com/v7/weather/now?location=101010100&key=bcc955219ba14e92875bf1c76516ea3e&gzip=n';
        $client=new Client();
        $res=$client->request('GET',$data,['verify'=>false]);
        $body=$res->getBody();
        echo $body;
        $datas=json_decode($body,true);
        echo '<pre>';print_r($datas);echo '</pre>';
    }
    public function order(){

    }
    public function c(){
        $goods_id=request()->goods_id;
        $user=session("user.uid");
        //dd($user);
        $where=[
            ['goods_id','=',$goods_id],
            ['uid','=',$user],
        ];
        $res=OrderModel::select('shop_price','is_real')->where($where)->get();
        //dd($res);
        $many=0;
        foreach($res as $k=>$v){
            $many +=$v["shop_price"]*$v['is_real'];
        }
        echo $many;
    }
}
