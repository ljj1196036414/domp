<?php
namespace App\Http\Controllers\Index;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\model\index\UserModel;
use Illuminate\Validation\Validator;
class UserController extends Controller
{
    public function register(){
        return view('index/register');
    }
    public function registerinfo(Request $request){
        $Validator=$request->Validator([
            'uname'=>'required|unique:user',
            'password'=>'required',
            'passwords'=>'required',
            'tel'=>'requeired|unique:user',
        ],[
            'uname.required'=>'用户名名称必填',
            'uname.unique'=>'用户名称已存在',
            'password.required'=>'密码必填',
            'passwords.required'=>'确认密码必填',
            'tel.required'=>'手机号码必填',
            'tel.unique'=>'手机号已存在',
        ]);
        //die;
        if($Validator->fails()){
            return redirect('UserController/register')
            ->withErrors($Validator)
                ->withInput();
        }
//        $data=$request->except('_token');
//        if($data['password']!=$data['passwords']){
//            return redirect('user/create')->with('msg','两次密码不一样');
//        }
//        $data['password']=password_hash($data['password'],PASSWORD_DEFAULT);
//        unset($data['passwords']);
//        //dd($data);
//        $res=UserModel::insert($data);
//        if($res){
//            return redirect('user/login');
//        }

    }
}
