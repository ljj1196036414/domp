<?php
namespace App\Http\Controllers\Index;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\model\index\UserModel;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\model\index\GithubUserModel;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{
    public function register(){
        return view('index/register');
    }
    public function registerinfo(Request $request){
        $Validator=$request->validate([
            'uname'=>'required',
            'password'=>'required',
            'passwords'=>'required',
            'tel'=>'required',
        ],[
            'uname.required'=>'名称必填',
            'user_name.unique'=>'名称已存在',
            'password.required'=>'密码必填',
            'tel.unique'=>'手机号已存在',
            'tel.required'=>'手机号必填',
            'passwords.required'=>'确认密码必填',
        ]);

        $data=$request->except('_token');
        //dd($data);
        if($data['password']!=$data['passwords']){
            return redirect('user/register')->with('msg','两次密码不一样');
        }
        $data['password']=password_hash($data['password'],PASSWORD_DEFAULT);
        unset($data['passwords']);
        //dd($data);
        $user_model=new UserModel();
        //dd($data);
        $res = $this->sendEmail($data['user_email']);
        if($res){
            Redis::hMset($res,$data);
            return view('user/enroll');
        }else{
            return redirect('user/register')->with('msg','注册失败');
        }

    }
    //发送邮箱
    public function sendEmail($user_email){
        $key = Str::random(18);
        $text ="
    亲爱的用户：
    您好
    您于".date('Y-m-d H:i')."注册品优购,点击以下链接，即可激活该帐号：
    ".env('APP_URL')."/login/enroll/".$key."
    (如果您无法点击此链接，请将它复制到浏览器地址栏后访问)
    1、为了保障您帐号的安全性，请在 半小时内完成激活，此链接将在您激活过一次后失效！
    2、请尽快完成激活，否则过期，即 ".date('Y-m-d H:i',time()+60*30)." 后品优购将有权收回该帐号。
    品优购";
        $res=[
            'user_email'=>$user_email,
            'text'=>$text,
            'font'=>$key,
        ];
        $flag=Mail::raw($res['text'],function($message) use($res){
            $to = $res['user_email'];
            $message->to($to)->subject('品优购注册激活');
        });
        if(!$flag){
            return $key;
        }else{
            return false;
        }

    }
    public function enroll($key){
        $regInfo = Redis::hgetall($key);
        if(empty($regInfo)){
            return redirect('/');
        }
        $userinfo = UserModel::where(['tel'=>$regInfo['tel']])
            ->orwhere(['uname'=>$regInfo['uname']])
            ->first();
        if(!empty($userinfo)){
            return redirect('user/login');
        }
        $res = UserModel::insert($regInfo);
        Redis::del($key);
        if($res){
            return redirect('user/login');
        }else{
            return redirect('user/register');
        }
    }
    public function login(){
        return view('index/login');
    }
    public function loginInfo(Request $request){
        $data=$request->except('_token');
        //dd($data);
        $user_model=new UserModel();
        $res=UserModel::where('uname',$data['account'])
            ->orwhere('tel',$data['account'])
            ->first();
        if(empty($res)){
            echo "用户不存在";
            return redirect('user/login')->with(['msg'=>'用户不存在']);
        }
        //定义一个key
        $key='login:count:'.$res->uid;
        //取出一个key
        $count=Redis::get($key);
        if($count>=5){
            //锁定时间
            Redis::expire($key,60);
            return redirect('user/login')->with(['msg'=>'用户已被锁定']);
        }
        if(!empty($res)){
//            dd($res);
            if(password_verify($data['password'],$res->password)){
//                dd(123);
                Redis::del($key);
                $user=['uid'=>$res['uid'],'uname'=>$res['uname']];
               session(['user'=>$user]);
                //$a=session('user.uid');
                //dd($a);
//                session('user','123');
//                dd(session('user'));
                echo "登录成功，正在跳转";
                $keys='login:time:'.$res->uid;
                //dd($keys);die;
                Redis::rpush($keys,time());
                //return redirect('index/goods');


            }else{
                Redis::incr($key);
                if($count>0){
                    Redis::expire($key,600);
                    return redirect('user/login')->with(['msg'=>'错误次数'.$count]);die;
                }
                return redirect('user/login')->with('msg','账号或者密码错误');
            }
        }
    }
    public function sign(Request $request){
        $request->session()->forget('user');
        return redirect('user/login');
    }
    public function githod(Request $request){
        // 接收code
        $code = $_GET['code'];

        //换取access_token
        $token = $this->getAccessToken($code);
        //获取用户信息
        $git_user = $this->getGithubUserInfo($token);
        $u = GithubUserModel::where(['guid'=>$git_user['id']])->first();
        if($u)          //存在
        {
            $this->webLogin($u->uid);

        }else {          //不存在

            //在 用户主表中创建新用户  获取 uid
            $new_user = [
                'uname' => Str::random(10)              //生成随机用户名，用户有一次修改机会
            ];
            $uid = UserModel::insertGetId($new_user);
            $info = [
                'uid'                   => $uid,       //作为本站新用户
                'guid'                  => $git_user['id'],         //github用户id
                'avatar'                =>  $git_user['avatar_url'],
                'github_url'            =>  $git_user['html_url'],
                'github_username'       =>  $git_user['name'],
                'github_email'          =>  $git_user['email'],
                'add_time'              =>  time()
            ];
            $guid = GithubUserModel::insertGetId($info);        //插入新纪录
             $this->webLogin($guid);
        }


        return redirect('/goods/index');
    }
    public function getAccessToken($code){
        $url='https://github.com/login/oauth/access_token';
        $client=new Client();
        $response = $client->request('POST',$url,[
            'verify'    => false,
            'form_params'   => [
                'client_id'         => 'fc8edd088ec11c0bd3cc',
                'client_secret'     => '2b563c5597b7c4f49b5e2a81d23a8c8524fc373f',
                'code'              => $code
            ]
        ]);
        parse_str($response->getBody(),$str); // 返回字符串 access_token=59a8a45407f1c01126f98b5db256f078e54f6d18&scope=&token_type=bearer
       //dd($str);
        return $str['access_token'];
    }
    protected function getGithubUserInfo($token)
    {
        $url = 'https://api.github.com/user';
        //GET 请求接口
        $client = new Client();
        $response = $client->request('GET',$url,[
            'verify'    => false,
            'headers'   => [
                'Authorization' => "token $token"
            ]
        ]);
        return json_decode($response->getBody(),true);
    }
    //登录添加redis时间
//    public function hello(){
//        $keys='login:time:3';
//        $arr=Redis::lrange($keys,0,-1);
//        dd($arr);
//    }
    public function webLogin($uid){
        session(['uid'=>$uid]);
    }
}
