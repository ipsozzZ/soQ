<?php

/**
 * @author : goodtimp
 * @time : 2019-3-1
 */

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class KeyWord extends NotORM
{

  protected function getTableName($id)
  {
    return 'keyword';
  }

  /**
   * 查找所有KeyWords
   * @return 返回数据库可操作类型
   */
  public function gesAllKeyWord()
  {
    return $this->getORM()
      ->select('*');
  }


  /**
   * 根据关键字Id得到关键字信息
   * @param string类型，逗号分隔类似"1,2,3,"
   * @return Array 关键字文字Array
   */
  public function gesKeyWordsByIds($idarr)
  {
    if ($idarr == null || $idarr == "") return [];

    $arr = explode(",", $idarr);

    return $this->getORM()->where("Id", $arr)->fetchAll();
  }
  /**
   * 根据KeyWord字段得到关键字信息
   * @param string类型，逗号分隔类似"三角函数,二元一次,"
   * @return 关键字Id Array
   */
  public function gesKeyWordsByWords($words, $cid = 10)
  {
    if ($words == null || $words == "") return [];

    // 数据库查询语句，判断题目中存在的关键字
    if ($cid == 0) $command = 'select Id,Word,Weight from keyword where :words like concat("%",Word,"%")';
    else $command = 'select Id,Word,Weight from keyword where CategoryId=:cid and :words like concat("%",Word,"%")';
    $params = array(':words' => $words, ':cid' => $cid);

    return  $this->getORM()->queryAll($command, $params);
  }

  /**
   * 添加关键字
   */
  public function addKeyword($data)
  {
    $model = $this->getORM();
    $model->insert($data);
    return $model->insert_id();
  }

  /* ---------------  ipso  ---------------- */

  public function updateKeyword($Id, $data)
  {
    $model = $this->getORM();
    return $model->where('Id', $Id)->update($data);
  }

  public function deleteOne($Id)
  {
    $model = $this->getORM();
    return $model->where('Id', $Id)->delete();
  }

  public function getCount()
  {
    $model = $this->getORM();
    return $model->count('Id');
  }

  public function getList($begin = 1, $num = 10)
  {
    $model = $this->getORM();
    return $model->limit($begin, $num)->fetchAll();
  }

  public function getCountByWord($word)
  {
    $model = $this->getORM();
    return $model->where("Word", $word)->count();
  }

  public function getByWord($word, $begin = 0, $num = 10)
  {
    $model = $this->getORM();
    return $model->where('Word', $word)->limit($begin, $num)->fetchAll();
  }
}
