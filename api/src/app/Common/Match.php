<?php

/**
 * @author : goodtimp
 * @time : 2019-3-1
 */
namespace App\Common;

use App\Common\Cosines;
use App\Common\Tools as Tools;
use App\Model\Question\Search as ModelSearchQ;

class Match
{
  /** 
   * 字符串相似度算法
  */
  public static function levenShtein($str1, $str2)
  {
    $str1=substr($str1,0,1000);
    //计算两个字符串的长度。  
    $len1 = strlen($str1);
    $len2 = strlen($str2);
    //建立上面说的数组，比字符长度大一个空间  
    $dif = array();
    for ($a = 0; $a <= $len1; $a++) {
      $dif[$a] = array();
      $dif[$a][0] = $a;
    }
    for ($a = 0; $a <= $len2; $a++) {
      $dif[0][$a] = $a;
    }
    $temp = 0;
    for ($i = 1; $i <= $len1; $i++) {
      for ($j = 1; $j <= $len2; $j++) {
        if ($str1[$i - 1] == $str2[$j - 1]) {
          $temp = 0;
        } else {
          $temp = 1;
        }
        //取三个值中最小的  
        $dif[$i][$j] = min($dif[$i - 1][$j - 1] + $temp, $dif[$i][$j - 1] + 1, $dif[$i - 1][$j] + 1);
      }
    }
    $similarity = 1 -  $dif[$len1][$len2] * 1.0 / max(strlen($str1), strlen($str2), 1);
    return $similarity;
  }

  /** 题目相似度匹配出最大的前n个
   * @param $q 匹配的题目
   * @param $qs 待匹配的题目数组
   * @param $n 前n个相似度最大的
   * @return 前n个题目数组，若输入数组数量不够n直接输出
   */
  public static function qLevenShtein($q, $qs, $num = 3)
  {
    //排序
    $reslut = array();
    $leven = array();
    foreach ($qs as $mq) {
      $re = Match::levenShtein($q["Text"], $mq["Text"]);

      for ($i = 0; $i < count($reslut); $i++) {
        if ($leven[$i] < $re) break;
      }
      Tools::insertArray($reslut, $i, $mq);
      Tools::insertArray($leven, $i, $re); //向 $temp 的$i下标处插入$cnt数据
    }
    if (count($qs) <= $num) return $reslut;
    return array_slice($reslut, 0, $num);
  }
  /**
   * 关键字匹配，输入关键字输出为可用户数据库模糊查找的字符串
   * @param $keys 例如：质量
   * @return string 质量->%质%量%
   */
  public static function AllWordMatch($keys)
  {
    // $s = "%";
    $cid=Tools::judgeCategoryId($keys);
    $keywords=Tools::ExtractKeyWords($keys,$cid);
    foreach($keywords as $word)
    {
      $pos=0;
      while($pos!==false&&strlen($keys)>$pos)
      {
        $pos=strpos($keys,$word["Word"],$pos);//下面加了
        if($pos===false) break;
        $keys=substr_replace($keys, "%", $pos+strlen($word["Word"]), 0);//需要+
      
        $keys=substr_replace($keys, "%", $pos, 0);
        $pos=$pos+strlen($word["Word"])+2;
      }
    }
    $arr = Tools::chToArray($keys);
    $reslut="";
    if($arr[0]!="%") $reslut="%";
    $f=false;
    for ($i = 0; $i < count($arr); $i++) {
      $reslut .=  $arr[$i];
      if($arr[$i]=="%")
      {
        $f=!$f;
        continue;
      }
      if($f) continue;

      $reslut.="%";
    }
    $temp="";
    while($reslut!=$temp)
    {
      $temp=$reslut;
      $reslut=str_replace("%%","%",$reslut);
    }
    $reslut=str_replace(" ","",$reslut); //去除空格影响
    return $reslut;
  }


  /**
     * 根据关键字匹配指定大于某数量的题目（优先匹配关键字最多的 需修改按大小匹配！！）
		 * @param array keywords [{"Id":"2","Weight":"3"}...} 降序
		 * @param num 题目数量
     * @param questions 经过处理的数据库可直接操作的题目
     * @return 数据库可操作类型
     */
  public static function GetQuestionsByKeyWord($keywords, $num = 0, $questions = null,$isorm=true)
  {
    $msq=new ModelSearchQ();
    if ($questions == null) $questions = $msq->mgetAllQuestion();
    if ($num == null || $num < 1) $num = 3;
  
    if ($keywords) {
      $keyarr = Match::merageArray($keywords);
      if($isorm) $questions = $questions->where("NOT KeyWords","")->fetchAll(); //抓取所有
      
      $cos = new Cosines();
      $len=count($questions);
      
      // $questions=$questions->select("KeyWords,KeysWeight,Id,Text")->fetchAll();
      for ($i = 0; $i < $len; $i++) {
        $temp = array(
          "Keys" => $questions[$i]["KeyWords"]==""?[]:explode(",", $questions[$i]["KeyWords"]),
          "Weight" => $questions[$i]["KeysWeight"]==""?[]:explode(",", $questions[$i]["KeysWeight"])
        );
        if(count($temp["Keys"])<=0) continue; //剪枝 去掉keys为空
        
        $questions[$i]["Similarity"] = $cos->run($keyarr, $temp);
        if($questions[$i]["Similarity"]<0.1) unset($questions[$i]);
      }
      $re=Tools::GetMaxArray($questions, "Similarity", $num);
      return $re; //获取前n个记录
    }
    else {//如果传入文字未提取到关键字
      $reslut=[];
      if($isorm) $questions = $questions->where("KeyWords","")->fetchAll(); //抓取所有
      for ($i = 0; $i < count($questions); $i++) {

        if($questions[$i]["KeyWords"]==""){ 
          $questions[$i]["Similarity"] = 1;
          array_push($reslut,$questions[$i]);
        }       
        // return $questions[$i]["Similarity"];

      }
      $reslut=Tools::GetMaxArray($questions, "Similarity", $num);

      return $reslut;
    }
  }
  /**
   * 工具类
   * [{Id:1,Weight:1},{Id:2,Weight:2}]=>{Keys:[1,2],Weight:[1,2]}
   */
  private static function merageArray($arr)
  {
    $re = array(
      "Keys" => [],
      "Weight" => []
    );
    for ($i = 0; $i < count($arr); $i++) {
      $re["Keys"][$i] = $arr[$i]["Id"];
      $re["Weight"][$i] = $arr[$i]["Weight"];
    }
    return $re;
  }
}
