<?php
namespace App\Api;

use PhalApi\Api;

use App\Domain\Focus as DomainFocus;
use App\Common\MyStandard;
use App\Domain\CutQuestion as DomainCQ;



/**
 * 切题
 * @author: goodtimp 2019-04-02
 */
class CutQuestion extends Api
{
  private $path = 'E:/phpstudy/PHPTutorial/WWW/soQ/api';



  public function getRules()
  {
    return array(
      'cut' => array(
        'file' => array(
          'name' => 'file',
          'type' => 'file',
          'min' => 0,
          'max' => 1024 * 1024 * 1024 * 60,
          'range' => array('image/jpg', 'image/jpeg', 'image/png'),
          'ext' => array('jpg', 'jpeg', 'png')
        ),
      ),
    );
  }
  /**
     * 切题
     * @return 切题信息
     */
  public function cut()
  {
    // return $this->file["tmp_name"];
    //设置上传路径 设置方法参考3.2
    // \PhalApi\DI()->ucloud->set('save_path', "cut/" . date('Y/m/d'));
    // $name = rand(213123, 1321321);
    // //新增修改文件名设置上传的文件名称
    // \PhalApi\DI()->ucloud->set('file_name', $name . $_SERVER['REQUEST_TIME']);
    $program = $this->path . "/sdk/Python/PhalApiClient/python3.x/RelevantPictures/Process.py";

    // $rs = \PhalApi\DI()->ucloud->upfile($this->file); // 保存文件
    // $img_path = $this->path . "/public/upload/" . $rs["file"]; // 文件路径
    $img_path=$this->file["tmp_name"];
    $dcq=new DomainCQ();
    // $time=time();
    $reslut=$dcq->cutQuestion($img_path,$program);  // exce 运行命令行需要启动 3s时间较长
    // array_push( $reslut,$time);
    // $time=time();
    // array_push( $reslut,$time);
    return MyStandard::gReturn(0, $reslut);
  }
}
