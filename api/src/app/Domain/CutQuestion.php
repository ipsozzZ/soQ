<?php
namespace App\Domain;
use App\Model\Collection as ModelCollection;
use App\Model\Mistake as ModelMistake;
use App\Model\Behavior\Basic as ModelBehavior;
use App\Model\Question\Basic as ModelQuestion;
use App\Common\Tools;
use App\Model\Interest as ModelInterest;

class CutQuestion
{

  private function handlePyCmdPrint($str)
  {
    $arr=explode(", '",$str);

    $cnt=count($arr);
    $re=array(
      'left'=>intval(substr($arr[0],strpos($str," ")+1)),
      'top'=>intval(substr($arr[2],strpos($arr[2]," ")+1)),
      'width'=>intval(substr($arr[$cnt-2],strpos($arr[$cnt-2]," ")+1)),
      'height'=>intval(substr($arr[$cnt-1],strpos($arr[$cnt-1]," ")+1,$cnt-strpos($str," "))),
      'words'=>(substr($arr[4],strpos($arr[4]," ")+2))
    );
    for ($i=5; $i<$cnt-2; $i++) {
      $re['words']=$re['words'].$arr[$i];
    }

    return $re;
  }
	/**
   *  切题
   * @param path 文件路径
   */
	public function cutQuestion($img_path,$py_path)
	{

    $cmd = "python " .  $py_path . " {$img_path}";
    $reslut = [];
  
    exec($cmd, $reslut);// exce 运行命令行需要启动 3s时间较长

    $re=[];
    for ($i=0; $i<count($reslut); $i++) {
      $temp=$this->handlePyCmdPrint($reslut[$i]);
      array_push($re,$temp);
    } 

		return $re;
	}
}