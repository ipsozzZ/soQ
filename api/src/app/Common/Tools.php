<?php

/**
 * @author : goodtimp
 * @time : 2019-3-2
 */
namespace App\Common;

use App\Model\KeyWord as KeyWord;

class  Tools
{
  /** 
     * 关键字提取并根据出现次数和权值排序 静态方法 "::"调用
     * @param $text 待提取的文字
     * @return array 关键字Id、Word、权重数组（从大到小排序后）
     */
  public static function ExtractKeyWords($text)
  {
    $kw = new KeyWord();

    // 数据库查询语句，判断题目中存在的关键字
    //$command = "select Id,Word from keyword where '" . $text . "' like concat('%',Word,'%')";
    $keyarr = $kw->gesKeyWordsByWords($text);
 
    for ($i=0;$i<count($keyarr);$i++) {
      $cnt = substr_count($text, $keyarr[$i]['Word']);
      $keyarr[$i]["Weight"]=$keyarr[$i]["Weight"]*$cnt;
    }
    
    Tools::SortByKey($keyarr,"Weight",false);
    return $keyarr;
  }

  /**
   * 默认从小到大排序，第三个参数为false则为从大到小
   * @param $arr 数组
   * @param $key 排序的键值
   * @param $f
   */
  public static function SortByKey(&$arr,$key,$f)
  {
    $reslut=array();
    foreach($arr as $item)
    {
      for($i=0;$i<count($reslut);$i++)
      {
        if(!$f)
        {
          if($item[$key]>$reslut[$i][$key]) break;
          
        }
        else {
          if($item[$key]<$reslut[$i][$key]) break;
        }
      }
      Tools::insertArray($reslut,$i,$item);
    }
    $arr=$reslut;
  } 
  /**
   * 得到Array内指定的键，并生成相应的数组
   */
  public static function GetValueByKey($arr,$key)
  {
    try{
      $reslut=array();
      foreach($arr as $temp)
      {
        if(array_key_exists($key,$temp))
          array_push($reslut,$temp[$key]);
      }
      return $reslut;
    }
    catch(Exception $e){
      return [];
    }
  }
  /**
   * 向输入内指定位置插入元素
   * @param $pos 插入位置的下标，-2为插入count($arr)-1
   */
  public static function insertArray(&$arr,$pos,$item)
  {
    $len=count($arr);
    if($pos<0) $pos=$len+$pos+1;
    $temp=$item;
    for($i=$pos;$i<$len;$i++)
    {
      $t=$arr[$i];
      $arr[$i]=$temp;
      $temp=$t;
    }
    $arr[$i]=$temp;
    return $arr;
  }

  /** 针对于Keyword操作合并相同的Key，并相加Weight */
  public static function mergeKeyWeight(&$arr)
  {
    $reslut=array();
    
    foreach($arr as $item)
    {
      for($i=0;$i<count($reslut);$i++)
      {
        if($reslut[$i]["Id"]==$item["Id"]) break;
      }
      if($i==count($reslut))
        Tools::insertArray($reslut,0,$item);
      else
        $reslut[$i]["Weight"]= $reslut[$i]["Weight"]+$item["Weight"];
    }
    $arr=$reslut;
    return $reslut;
  }
  
  /**懒加载计算数据查找条数范围 
   * @param pag 页数
   * @param num 每一页数量
  */
  public static function getPageRange($pag,$num)
  {
    return ($pag-1)*$num;
  }
}
