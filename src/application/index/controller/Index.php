<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Validate;
use think\Request;

class Index extends Controller
{

    public function index()
    {
        return view();
    }

    public function submit()
    {
        return view();
    }

    public function select(Request $request)
    {
        $post = $request->param();

        if($request->isPost()){
            $validate = Validate::make([
                "hash|MD5" => "require"
            ]);

            $error = $validate->check($post);

            if($error){
                $error = is_md5($post['hash']);
            }

            $selectWhere['ciphertext'] = $post['hash'];

            if($error){
                $data = Db::name("md5")->where($selectWhere)->find();

                if($data){
                    $json = [
                        "code" => 0,
                        "plaintext" => $data['plaintext'],
                        "ciphertext" => $data['ciphertext'],
                    ];
                }else{
                    $json = [
                        "code" => 1,
                        "message" => "未找到，数据库暂无此明密文值"
                    ];
                }

                $json = json_encode($json);

                return $json;

            }else{

                $json = [
                    "code" => 2,
                    "message" => "无法识别此密文！"
                ];

                $json = json_encode($json);

                return $json;
            }
        }else{
            die("Request Error!");
        }

    }

    public function hashSub(Request $request)
    {
        $post = $request->param();

        if($request->isPost()){
            $hash = explode("\n", $post['hash']);

            if(count($hash) <= 100){
                $data = [];
                for($i=0;$i<count($hash);$i++){
                    $exp = explode("<--:-->",$hash[$i]);
                    $ciphertext = str_replace(array("\r\n", "\r","\n"),"",$exp[0]);
                    $plaintext = str_replace(array("\r\n", "\r","\n"),"",$exp[1]);
                    $data[$i] = [
                        "plaintext" => $plaintext,
                        "ciphertext" => $ciphertext
                    ];
                }

                for($j=0;$j<count($data);$j++){
                    $status = Db::name("md5")->where("plaintext",$data[$j]['plaintext'])->find();
                    if(!$status){
                        Db::name("md5")->insert($data[$j]);
                    }
                }

                $json = [
                    "code" => 0,
                    "message" => "批量提交成功！"
                ];

                $json = json_encode($json);

                return $json;

            }else{
                $json = [
                    "code" => 1,
                    "message" => "请输入Hash值！"
                ];

                $json = json_encode($json);

                return $json;
            }
        }else{
            die("Request Error!");
        }

    }
}
