<?php
namespace App\Domain;

use App\Model\Behavior\Basic as ModelBehavior;
use App\Model\Mistake as ModelMistake;
use App\Model\MistakeCategory as ModelMCategory;
use App\Model\Question\Basic as ModelQBasic;
use App\Model\Collection as ModelCollection;
use App\Model\Like as ModelLike;
use App\Model\Interest as ModelInterest;
use App\Model\Notice;

use App\Common\Tools;
use App\Model\User as ModelUser;

class Mistake
{
  /**根据错题关键字查找错题 */
  public function getByKeywords($uid, $cid, $keys)
  {
    $mm = new ModelMistake();
    $re=$mm->getMistakesByKeywords($uid, $cid, $keys);
    $qm = new ModelQBasic;
    $qm->replaceQuestionId($re);
    return $re;
  }

  /**得到用户所有分类信息 */
  public function getCategory($uid)
  {
    $mmc = new ModelMCategory();
    return $mmc->getCategoryByUserId($uid);
  }
  /**添加mistake，并添加相应的behavior数据 */
  public function addMistake($data, $standtime = 0)
  {
    $mm = new ModelMistake();
    $id = $mm->insertMistake($data);
    if ($id > 0) {
        $mb = new ModelBehavior();
        $be = array(
          "UserId" => $data["UserId"],
          "Type" => 3,
          "QuestionId" => $data["QuestionId"],
          "MistakeId" => $id,
          "StandTime" => $standtime
        );

        $mb->addBehavior($be);
      }

    //添加感兴趣度
    if ($data["QuestionId"] > 0) {
        $qd = array(
          "UserId" => $data["UserId"],
          "Behavior" => "Mistake",
          "QuestionId" => $data["QuestionId"],
        );
        $im = new ModelInterest();
        $im->addInterest($qd);
      }


    return $id;
  }
  /**更新数据 */
  public function updateMistake($data)
  {
    $mm = new ModelMistake();
    return $mm->updateMistake($data);
  }

  /**错题Id */
  public function getById($id, $uid)
  {
    $mm = new ModelMistake();
    $data = $mm->getMistakeById($id);

    if ($data["UserId"] == $uid) {
        $mc = new ModelCollection();
        $ml = new ModelLike();
        $data["Like"] = $ml->judgeUserLikeMistake($uid, $id);
        $data["Collection"] = $mc->judgeUserCollectionMistake($uid, $id);
      }
    if ($data["QuestionId"] > 0) {
        $qm = new ModelQBasic();
        $data["Question"] = $qm->getQuestionById($data["QuestionId"]);
      }
    if ($data["UserId"] > 0) {
        $qm = new ModelUser;
        $data["User"] = $qm->getUserById($data["UserId"]);
      }
    return $data;
  }

  /**根据题目Id查找错题（按热度排序）
   * @param num 数量
   */
  public function getMistakeByQuestionId($qid, $page = 1, $num = 5)
  {
    $mm = new ModelMistake();
    $min = Tools::getPageRange($page, $num);

    $re=$mm->getMistakeByQId($qid, $min, $num);
    $um=new ModelUser();
    $um->replaceUserId($re);
    return $re;
  }
  /**根据用户Id查找所有错题（按时间排序）
   * @param num 数量
   */
  public function getMistakeByUserId($uid, $cateid = 0, $page = 1, $num = 5)
  {
    $mm = new ModelMistake();
    $min = Tools::getPageRange($page, $num);
    $re = $mm->getMistakeByCId($uid, $cateid, $min, $num);
    $qm = new ModelQBasic;
    $qm->replaceQuestionId($re);
    return $re;
  }
  /**点赞
 * @param data {"MistakeId","UserId","QuestionId"}
 * @return 0失败 1成功
 */

  public function addLike($data, $standtime = 0)
  {
    $mm = new ModelMistake();
    $bm = new ModelBehavior();

    // return $bm->judgeUserLike($data["UserId"],$data["MistakeId"],$data["QuestionId"]);
    if ($bm->judgeUserLike($data["UserId"], $data["MistakeId"], $data["QuestionId"])) return 0;

    if ($mm->likeMistake($data["MistakeId"]) > 0) {
        $mb = new ModelBehavior();
        $be = array(
          "UserId" => $data["UserId"],
          "Type" => 2,
          "QuestionId" => $data["QuestionId"],
          "MistakeId" => $data["MistakeId"],
          "StandTime" => $standtime
        );
        $mb->addBehavior($be);
        return 1;
      }
    return 0;
  }
  /**删除错题
   * 
   */
  public function deleteMistake($uid, $mid)
  {
    $mm = new ModelMistake();
    $data = $mm->deleteMistake($uid, $mid);
    if ($data["QuestionId"] > 0) {
        //减少某题兴趣度
        $qd = array(
          "UserId" => $data["UserId"],
          "Behavior" => "Mistake",
          "QuestionId" => $data["QuestionId"],
        );
        $im = new ModelInterest();
        $im->reduceInterest($qd);
      }
    return $data;
  }

  /**
   * 添加错题分类 
   */
  public function addCategory($data)
  {
    $mmc=new ModelMCategory();
    return $mmc->addCategory($data);
  }

  /**
   * 更新错题分类
   */
  public function updateCategory($data){
    $mmc=new ModelMCategory();
    return $mmc->updateCategory($data);
  }

  public function deleteCategory($data)
  {
    $mmc=new ModelMCategory();
    $mmc->deleteCategory($data);
    $mm=new ModelMistake();
    
    return $mm->deleteMistakeByCid($data["UserId"],$data["Id"]);
  }

	/**
	 * 获取题目收藏数前10
	 */
	public function getCollection(){
		$model = new ModelMistake();
		$myType = 'CollectNumber';
		$list = $model -> getByMyType($myType, 10);
		if(!$list){
			return 1;
		}
		return $list;
	}

	/**
	 * 获取题目热度前10 
	 * (获取收藏数前100的题目，并求每道题目的收藏数和点赞数之和，取前10作为热度前十题目)
	 */
	public function getLike(){
		$model = new ModelMistake();
		$myType = 'CollectNumber';
		$list = $model -> getByMyType($myType, 100);
		if(!$list) return null;
		$count = count($list);
		for($i = 0; $i < $count; $i++){
			$list[$i]['sum'] = $list[$i]['CollectNumber'] + $list[$i]['LikeNumber'];
		}
		$newList = $this -> getMaximum($list);
		return $newList;
	}

	/**
	 * 快速排序获取前十
	 */
	private function getMaximum($data){
		$length = count($data);
		$newArr = '';
		// 对数组按$data[key]['sum']排序
		for($i = 0; $i < $length; ++$i){
			$k = $i;
			for($j = $i + 1; $j < $length; ++$j){
				if($data[$j]['sum'] > $data[$k]['sum']){
					$k = $j;
				}
			}
			if($k != $i){
				$arr = $data[$i];
				$data[$i] = $data[$k];
				$data[$k] = $arr;
			}
		}

		// 取出排序好数组的前10位放入数组newArr中
		for($i = 0; $i < 10; $i++){
			$newArr[$i] = $data[$i];
		}

		return $newArr;
	}

	/* -------------   ipso   ---------------- */

	/**
	 * 管理员清除敏感错题
	 * @param userId 错题所属的用户Id
	 * @param Id     错题Id
	 */
	public function deleteByAdmin($userId = 1, $Id = 1){
		$model = new ModelMistake();
		$notice = array(
			'Status'   => 0,
			'Title'    => "违规错题",
			'Content'  => "出现违规痕迹,请重新整理你的错题",
			'Author'   => "Admin",
			'Ctime'    => date('Y-m-d H:i:s'),
			'AcceptId' => $userId,
		);
		$sql = $model -> deleteOne($Id);
		if(!$sql){
			return false;
		}
		$NModel = new Notice();
		$nsql = $NModel -> insertOne($notice);
		return true;
	}
}

