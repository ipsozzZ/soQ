<?php
/**
 * @author : goodtimp
 * @time : 2018-3-3
 */

namespace App\Domain\Question;

use App\Model\Question\Search as ModelSearchQ;
use App\Common\ModelCommon as ModelCommon;
use App\Model\KeyWord as ModelKeyWord;
use App\Common\Tools as Tools;
use App\Common\Match as CommonMatch;
use App\Model\Question\Basic as ModelAddQ;

/**
 * 上传题目相关操作
 */
class Upload
{
 
  /**
   * 通过题目Id，推荐相应题目,需修改用户Id
   * @param $q 题目{CategoryId,Content,Analysis,Type,KeyWords(文字形式.逗号隔开),SchoolId,Text}
   * @return 返回插入的Id,题目重复也将返回对应题目Id
   */
  public function upQuestion($q, $Repetition = 0.95)
  {
    $mquestion = new ModelAddQ();
    $squestion = new ModelSearchQ();
    /*-------提取关键字---------*/
    if (!array_key_exists("Text", $q) || !$q["Text"]) $q["Text"] = " ".$q["Content"]." "; //保证Text必须有文字，否则输入原题的富文本
    $key = Tools::ExtractKeyWords($q["Text"],$q["CategoryId"]); //根据 分类匹配关键字
   
    // 提取给出的关键字
    if (array_key_exists("KeyWords", $q) && strlen($q["KeyWords"])) {
      $key1 = Tools::ExtractKeyWords($q["KeyWords"]);
      for ($i = 0; $i < count($key1); $i++) {
        $key1[$i]["Weight"] = $key1[$i]["Weight"] * 2;
      } //对用户输入的关键字添加双倍权值
      $key = array_merge($key1, $key);

      Tools::mergeKeyWeight($key);
    }
    Tools::SortByKey($key, "Weight", false);
    $idarr = Tools::GetValueByKey($key, "Id"); //生成仅有Id字段的数组
    $weightarr = Tools::GetValueByKey($key, "Weight");

    $q["KeyWords"] = implode(",", $idarr);
    $q["KeysWeight"] = implode(",", $weightarr);

    /*----------end for keyword------*/
    $q["Text"] = Tools::handleQuestionText($q["Text"]); //去除不必要的TEXT
    
    $s = $squestion->checkDuplicate($q, $Repetition); //判重 若重复返回重复的Id
    if ($s != null) return $s["Id"];

    return $mquestion->AddQuestion($q);
  }



  /* -----------  ipso  -------------- */

  /**
   * 更新一道题目
   */
  public function updateQuestion($data)
  {
    $mquestion = new ModelAddQ();
    $squestion = new ModelSearchQ();

    /*-------提取关键字---------*/
    if (!array_key_exists("Text", $data) || !$data["Text"]) $data["Text"] = $data["Content"]; //保证Text必须有文字，否则输入原题的富文本
    $key = Tools::ExtractKeyWords($data["Text"]);
    // 提取给出的关键字
    if (array_key_exists("KeyWords", $data) && strlen($data["KeyWords"])) {
      $key1 = Tools::ExtractKeyWords($data["KeyWords"]);
      for ($i = 0; $i < count($key1); $i++) {
        $key1[$i]["Weight"] = $key1[$i]["Weight"] * 2;
      } //对用户输入的关键字添加双倍权值
      $key = array_merge($key1, $key);

      Tools::mergeKeyWeight($key);
    }
    Tools::SortByKey($key, "Weight", false);
    $idarr = Tools::GetValueByKey($key, "Id"); //生成仅有Id字段的数组
    $weightarr = Tools::GetValueByKey($key, "Weight");

    $data["KeyWords"] = implode(",", $idarr);
    $data["KeysWeight"] = implode(",", $weightarr);

    /*----------end for keyword------*/

    $s = $squestion->checkDuplicate($data); //判重 若重复返回重复的Id
    if ($s != null) return $s["Id"];

    if (!$mquestion->update($data)) {
      return 1;
    }

    return 0;
  }
}
