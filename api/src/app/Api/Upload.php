<?php
/*
 * +----------------------------------------------------------------------
 * | 上传接口
 * +----------------------------------------------------------------------
 * | Copyright (c) 2015 summer All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: summer <aer_c@qq.com> <qq7579476>
 * +----------------------------------------------------------------------
 * | This is not a free software, unauthorized no use and dissemination.
 * +----------------------------------------------------------------------
 * | Date
 * +----------------------------------------------------------------------
 */
namespace App\Api;

use PhalApi\Api;
use App\Common\MyStandard;
use PhalApi\Exception;

/**
 * 文件上传 
 */
class Upload extends Api
{

    /**
     * 获取参数
     * @return array 参数信息
     */
    public function getRules()
    {
        return array(
            'upload' => array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 60,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'uploadNote'=> array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 20,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'uploadMistake'=> array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 20,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'uploadUser'=> array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 20,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'uploadQuestion'=> array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 20,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'aliUpload' => array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 20,
                    'range' => array('image/jpg', 'image/jpeg', 'image/png'),
                    'ext' => array('jpg', 'jpeg', 'png')
                ),
            ),
            'uploadVideo' => array(
                'file' => array(
                    'name' => 'file',
                    'type' => 'file',
                    'min' => 0,
                    'max' => 1024 * 1024 * 1024 * 200,
                    'range' => array('video/eot-mp4','video/mp4'),
                    'ext' => array('mp4', 'avi'),
                    'desc'=>"视频文件"
                ),
            ),
        );
    }

    /**
     * 上传文件
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function upload()
    {
        //设置上传路径 设置方法参考3.2
        \PhalApi\DI()->ucloud->set('save_path', "demo/" . date('Y/m/d'));
        $name = rand(213123, 1321321);
        //新增修改文件名设置上传的文件名称
        \PhalApi\DI()->ucloud->set('file_name', $name . $_SERVER['REQUEST_TIME']);

        $rs = \PhalApi\DI()->ucloud->upfile($this->file);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = "http://1975386453.38haotyhn.duihuanche.com/upload/" . $rs["file"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
       /**
     * 上传笔记图片
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function uploadNote()
    {
        
        //设置上传路径 设置方法参考3.2
        $file=$this->file;
        $filePath=$file["tmp_name"]; //获取路径信息
        $filetype=substr($file["name"],strripos($file["name"],"."));
        $filename  = "notes/".date('Y/m/d').rand(213123, 1321321).$filetype; //设置上传路径
        
        $bucket = "goodtimp-vnote"; 

        //新增修改文件名设置上传的文件名称
        $re=\PhalApi\DI()->aliyunOss->uploadFile($bucket, $filename, $filePath);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = $re["info"]["url"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
     /**
     * 上传错题整理图片
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function uploadMistake()
    {
        
        //设置上传路径 设置方法参考3.2
        $file=$this->file;
        $filePath=$file["tmp_name"]; //获取路径信息
        $filetype=substr($file["name"],strripos($file["name"],"."));
        $filename  = "mistakes/".date('Y/m/d').rand(213123, 1321321).$filetype; //设置上传路径
        
        $bucket = "goodtimp-vnote"; 

        //新增修改文件名设置上传的文件名称
        $re=\PhalApi\DI()->aliyunOss->uploadFile($bucket, $filename, $filePath);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = $re["info"]["url"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
      /**
     * 上传用户图片
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function uploadUser()
    {
        
        //设置上传路径 设置方法参考3.2
        $file=$this->file;
        $filePath=$file["tmp_name"]; //获取路径信息
        $filetype=substr($file["name"],strripos($file["name"],"."));
        $filename  = "users/".date('Y/m/d').rand(213123, 1321321).$filetype; //设置上传路径
        
        $bucket = "goodtimp-vnote"; 

        //新增修改文件名设置上传的文件名称
        $re=\PhalApi\DI()->aliyunOss->uploadFile($bucket, $filename, $filePath);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = $re["info"]["url"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
      /**
     * 上传题目图片
     * @return string $url 绝对路径
     * @return string $file 相对路径，用于保存至数据库，按项目情况自己决定吧
     */
    public function uploadQuestion()
    {
        //设置上传路径 设置方法参考3.2
        $file=$this->file;
        $filePath=$file["tmp_name"]; //获取路径信息
        $filetype=substr($file["name"],strripos($file["name"],"."));
        $filename  = "questions/".date('Y/m/d').rand(213123, 1321321).$filetype; //设置上传路径
        
        $bucket = "goodtimp-vnote"; 

        //新增修改文件名设置上传的文件名称
        $re=\PhalApi\DI()->aliyunOss->uploadFile($bucket, $filename, $filePath);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = $re["info"]["url"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
    public function uploadVideo(){
        //设置上传路径 设置方法参考3.2
     
        $file=$this->file;
        $filePath=$file["tmp_name"]; //获取路径信息
        $filetype=substr($file["name"],strripos($file["name"],"."));
        $filename  = "videos/".date('Y/m/d').rand(213123, 1321321).$filetype; //设置上传路径
        
        $bucket = "goodtimp-vnote"; 

        //新增修改文件名设置上传的文件名称
        $re=\PhalApi\DI()->aliyunOss->uploadFile($bucket, $filename, $filePath);
        try {
            $rs["errno"] = 0;
            $rs["data"] = [];
            $rs["data"][0] = $re["info"]["url"];
            unset($rs["file"]);

            return MyStandard::gReturn(0, $rs);
        } catch (Exception $e) {
            $rs["data"] = [];
            $rs["errno"] = 1;
            $rs["data"] = [];
            return MyStandard::gReturn(1, $rs);
        }
    }
}
