<?php

/**
 * @author : goodtimp
 * @time : 2019-3-2
 */
namespace App\Common;

use App\Model\KeyWord as KeyWord;
use App\Model\User as userModel;
class  Tools
{
  /** 
     * 关键字提取并根据出现次数和权值排序 静态方法 "::"调用
     * @param text 待提取的文字
     * @param cid 分类Id 物理为10，英语为20 .默认为物理
     * @return array 关键字Id、Word、权重数组（从大到小排序后）
     */
  public static function ExtractKeyWords($text,$cid=10)
  {

    $kw = new KeyWord();
    // 数据库查询语句，判断题目中存在的关键字
    $keyarr = $kw->gesKeyWordsByWords($text,$cid);
    
    for ($i = 0; $i < count($keyarr); $i++) {
      $cnt = substr_count(strtolower($text), strtolower($keyarr[$i]['Word']));
      $cnt=$cnt>0?$cnt:1;
      $keyarr[$i]["Weight"] = $keyarr[$i]["Weight"] * $cnt;
    }

    Tools::SortByKey($keyarr, "Weight", false);
    return $keyarr;
  }

  /**
   * 默认从小到大排序，第三个参数为false则为从大到小
   * @param $arr 数组
   * @param $key 排序的键值
   * @param $f
   */
  public static function SortByKey(&$arr, $key, $f=true)
  {
    $reslut = array();
    foreach ($arr as $item) {
        for ($i = 0; $i < count($reslut); $i++) {
            if (!$f) {
                if ($item[$key] > $reslut[$i][$key]) break;
              } else {
              if ($item[$key] < $reslut[$i][$key]) break;
            }
          }
        Tools::insertArray($reslut, $i, $item);
      }
    $arr = $reslut;
  }

  /**
   * 根据某一字段获取最大前n个的数组元素
   */
  public static function GetMaxArray($arr,$key,$num=3)
  {
    $re=[];
    foreach ($arr as $item) {
      $len=(count($re)>$num?$num:count($re));
    
      if(!array_key_exists($key,$item)) continue;
      $re[$len]=$item; 
      for($i=$len;$i>0;$i--)
      {
        if($re[$i-1][$key]<$re[$i][$key])
        {
          $temp=$re[$i];
          $re[$i]=$re[$i-1];
          $re[$i-1]=$temp;
        }
        else break;
      }
    }
    unset($re[count($re)-1]);
    return $re;
  }
  /**
   * 得到Array内指定的键，并生成相应的数组
   */
  public static function GetValueByKey($arr, $key)
  {
    try {
      $reslut = array();
      foreach ($arr as $temp) {
          if (array_key_exists($key, $temp))
            array_push($reslut, $temp[$key]);
        }
      return $reslut;
    } catch (Exception $e) {
      return [];
    }
  }
  /**
   * 向输入内指定位置插入元素
   * @param $pos 插入位置的下标，-2为插入count($arr)-1
   */
  public static function insertArray(&$arr, $pos, $item)
  {
    $len = count($arr);
    if ($pos < 0) $pos = $len + $pos + 1;
    $temp = $item;
    for ($i = $pos; $i < $len; $i++) {
        $t = $arr[$i];
        $arr[$i] = $temp;
        $temp = $t;
      }
    $arr[$i] = $temp;
    return $arr;
  }

  /** 针对于Keyword操作合并相同的Key，并相加Weight */
  public static function mergeKeyWeight(&$arr)
  {
    $reslut = array();

    foreach ($arr as $item) {
        for ($i = 0; $i < count($reslut); $i++) {
            if ($reslut[$i]["Id"] == $item["Id"]) break;
          }
        if ($i == count($reslut))
          Tools::insertArray($reslut, 0, $item);
        else
          $reslut[$i]["Weight"] = $reslut[$i]["Weight"] + $item["Weight"];
      }
    $arr = $reslut;
    return $reslut;
  }

  /**懒加载计算数据查找条数范围 
   * @param pag 页数
   * @param num 每一页数量
  */
  public static function getPageRange($pag, $num)
  {
    return ($pag - 1) * $num;
  }
  /**中文字符串转数组 */
  public static function chToArray($str)
  {
    $length = mb_strlen($str, 'utf-8');
    $array = [];
    for ($i = 0; $i < $length; $i++)
      $array[] = mb_substr($str, $i, 1, 'utf-8');
    return $array;
  }
  /**
   * 处理题目Text字段，去除不必要的符号 
   * @param str 处理文字
   * @param encoding 编码方案
   * */
  public static function handleQuestionText($str,$encoding='utf8')
  {
    $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
    preg_match_all($pattern,$str,$result);
    $temp =join('',$result[0]);
    return $temp;
  }
  /**
   * 更新用户金币
   * @param coin >0
   * @return 修改后用户进步数量
   */
  public static function updateUserCoin($uid,$coin)
	{
    if($coin<0) return -1;
    $model=new userModel();
    $data=array(
      "Id"=>$uid,
      "Coin"=>$coin
    );
    $sql=$model->updateOne($data);
    if($sql) 
      return $sql["Coin"];
    else return -1;
  }
  /**
   * 去除二维数组内某个键
   */
  public static function unsetKeys($arr,$key)
  {
    for ($i = 0; $i < count($arr); $i++) {
      if(array_key_exists($key,$arr[$i]))
      {
        unset($arr[$i][$key]);
      }
    }
    return $arr;
  }
  /**
   * 判断是否为英文题目
   * @param ratio 英语字符占比（数字、英语字符、中文字符）默认达到0.8便为英语题目 
   */
  public static function judgeEnglish($text,$ratio=0.8)
  {
    $arrNum=array();
    $arrCh=array();
    $arrAl=array();
    preg_match_all("/[0-9]{1}/",$text,$arrNum);
    preg_match_all("/[a-zA-Z]{1}/",$text,$arrAl);
    preg_match_all("/([\x{4e00}-\x{9fa5}]){1}/u",$text,$arrCh);
    $data=array(
      "num"=>Tools::Count2Array($arrNum),
      "eng"=>Tools::Count2Array($arrAl),
      "ch"=>Tools::Count2Array($arrCh),
      'cht'=>$arrCh,
      'engt'=>$arrAl
    );
   
    return $data["eng"]/($data["eng"]+$data["ch"]+$data["num"]) > $ratio;
  }
  /**
   * 统计二维数组内元素个数 针对与 judgeEnglish方法
   */
  public static function Count2Array($arr)
  {
    $cnt=0;
    for($i=0;$i<count($arr);$i++)
    {
      $t=count($arr[$i]);
      $cnt=$t>$cnt?$t:$cnt;
    }
    return $cnt;
  }
   /**
   * 判断文本分类
   */
  public static function judgeCategoryId($text)
  {
    return Tools::judgeEnglish($text) ? 20 : 10;
  }
}
