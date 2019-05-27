<?php
/**
 * @author : goodtimp
 * @time : 2018-3-2
 */

namespace App\Domain\Question;

use App\Model\Question\Search as ModelSearchQ;
use App\Domain\Behavior\Statistics;
use App\Common\Cosines;
use App\Common\Tools;
use App\Common\Match;

class GeneratieTest
{


  /**
 * 得到测试题目
 * @param uid 用户id
 * @param cid 分类id
 * @param date 时间 天数
 * @param num 获取数量 
 */
  public function getTest($uid, $cid = 0, $date = 30, $num = 10)
  {
    $ds = new Statistics();
    $keys = $ds->getStatisticsBehavior($uid, $cid, $date); //统计最近用户行为题目的keys
   
    // $mquestion = new ModelSearchQ();
    $qs = QTools::deleteQuestionsForUser($uid,null,$cid); //去除用户已经操作（收藏、错题整理等）部分
    $questions = Match::GetQuestionsByKeyWord($keys, $num, $qs); //关键字匹配相应题目
    return array_slice($questions,0,$num);
  }
}
