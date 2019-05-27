<?php
namespace App\Api;

use PhalApi\Api;

use App\Domain\Question\Upload as DomainUpload;
use App\Common\MyStandard;
use App\Domain\Question\Basic as DomainBasic;
use App\Domain\Question\Recommend as DomainRecommend;
use App\Common\Tools;
use App\Model\Question\Search as ModelSearchQ;
/**
 * 题目的基本操作示例
 * @author goodtimp 20190313
 */

class Question extends Api
{

    public function getRules()
    {
        return array(
            'insert' => array(
                'Content' => array('name' => 'Content', 'require' => true,  'desc' => '题目'),
                'CategoryId' => array('name' => 'CategoryId', 'type' => 'int',  'desc' => '分类'),
                'KeyWords' => array('name' => 'KeyWords',  'default' => "", 'desc' => '关键字'),
                'Analysis' => array('name' => 'Analysis',  'desc' => '解析'),
                'Text' => array('name' => 'Text',  'max' => 10000, 'desc' => '文本'),
                'Type' => array('name' => 'Type',  'max' => 50, 'desc' => '题目类型'),
                'Repetition'=>array('name' => 'Repetition','type'=>'float',
                'default'=>0.95,'max'=>1,'min'=>0, 'desc' => '判重率，数据库内题目相似度大于该值则不添加'),
                'AnswerVideo' => array('name' => 'AnswerVideo',"default"=>"0",  'desc' => '视频地址'),
                'VideoCost' => array('name' => 'VideoCost', 'min'=>0, 'max' => 200,"default"=>0, 'desc' => '视频付费'),
            ),
            'update' => array(
                'Id' => array('name' => 'Id', 'require' => true,  'desc' => '题目ID'),
                'Content' => array('name' => 'Content', 'require' => true,  'desc' => '题目'),
                'CategoryId' => array('name' => 'CategoryId', 'type' => 'int',  'desc' => '分类'),
                'Analysis' => array('name' => 'Analysis',  'desc' => '解析'),
                'Text' => array('name' => 'Text',  'max' => 10000, 'desc' => '文本'),
                'Type' => array('name' => 'Type',  'max' => 50, 'desc' => '题目类型'),
                'AnswerVideo' => array('name' => 'AnswerVideo',"default"=>"0",  'desc' => '视频地址'),
                'VideoCost' => array('name' => 'VideoCost', 'min'=>0, 'max' => 200,"default"=>0, 'desc' => '视频付费'),
            ),
            'search' => array(
                'Text' => array('name' => 'Text', 'require' => true, 'max' => 10000, 'desc' => '文本'),
                'Num' => array('name' => 'Num', 'default' => 3, 'desc' => '匹配n个')
            ),
            'searchs'=>array(
                'Texts' => array('name' => 'Texts', 'type' => 'array','require' => true,'format' => 'json', 'desc' => '文本数组'),
            ),
            'getById' => array(
                'UserId' => array('name' => 'UserId', 'default' => 0, 'require' => false, 'desc' => "用户id"),
                'Id' => array('name' => 'Id', 'require' => true, 'min' => 1, 'desc' => '题目Id'),
            ),
            'getByKeys' => array(
                'Keys' => array('name' => 'Keys', 'max' => 200, 'desc' => '关键字'),
                'CategoryId' => array('name' => 'CategoryId', 'default' => 0, 'desc' => '分类Id'),
                'Number'  => array('name' => 'Number', 'default' => 10, 'desc' => '需要的数量'),
                'Page' => array('name' => 'Page', 'default' => 1, 'desc' => '题目页数'),
            ),
            'getRecommendByQId' => array(
                'UserId' => array('name' => 'UserId', 'default' => 0, 'desc' => "用户id"),
                'Id' => array('name' => 'Id', 'require' => true, 'min' => 1, 'desc' => '题目Id'),
                'Number' => array('name' => 'Number', 'default' => 3, 'min' => 1, 'desc' => "题目数量")
            ),
            'getQuestionsByText' => array(
                'UserId' => array('name' => 'UserId', 'require' => true, 'desc' => "用户id"),
                'Text' => array('name' => 'Text', 'require' => true, 'max' => 10000, 'desc' => '文本'),
                'Number'  => array('name' => 'Number', 'default' => 4, 'desc' => '需要的数量'),
            ),
            'deleteQuestionByIds' => array(
                'Ids' => array('name' => 'Ids', 'require' => true, 'desc' => '逗号隔开的Id，例如：1,2,3')
            ),
            'getCount' => array(
                'CategoryId' => array('name' => 'CategoryId', 'default' => 0, 'desc' => '分类Id'),
            ),
            'delete' => array(
                'Id' => array('name' => 'Id', 'require' => true, 'desc' => "题目id"),
            ),
            'getCollection'=>array(),
            'getLike'=>array(),
        );
    }

    /**
     * 插入数据
     * @desc 向数据库插入一条纪录数据
     * @return int id 新增的ID
     */
    public function insert()
    {
        $rs = array();

        $newData = array(
            'Content' => $this->Content,
            'CategoryId' => $this->CategoryId,
            'KeyWords' => $this->KeyWords,
            'Analysis' => $this->Analysis,
            'Text' => " ".$this->Text." ",
            'Type' => $this->Type,
            "AnswerVideo"=>$this->AnswerVideo,
            "VideoCost"=>$this->VideoCost
        );
     
        $domain = new DomainUpload();
        $id = $domain->upQuestion($newData,$this->Repetition);
        
        $rs['Id'] = $id;
        return MyStandard::gReturn(0, $rs);
    }

    /**
     * 文字搜索题目
     * 
     */
    public function getByKeys()
    {
        $newKey = $this->Keys;
        if ($newKey == null) {
            $newKey = '';
        }
        $domain = new DomainBasic();
        $re = $domain->getByKeys($newKey, $this->CategoryId, $this->Page, $this->Number);
        return MyStandard::gReturn(0, $re);
    }

    /**
     * 拍照搜索题目
     * @desc 匹配题目
     * @return 题目信息
     */
    public function search()
    {
        $q = array('Text' => $this->Text);
        
        $reslut = DomainBasic::searchQuestion($q, $this->Num); //查找前三个
        return MyStandard::gReturn(0, $reslut);
    }
    /**
     *  查找多个题目
     * @desc 查找多个题目
     * @return 返回题目列表
     */
    public function searchs(){
        $ts=$this->Texts; 
        // $qm=new ModelSearchQ();
        // return $qm->getQuestionsByCategoryId($this->Id);
	
        $reslut=DomainBasic::searchQuestions($ts);
        return MyStandard::gReturn(0, $reslut);
    }
    /**
     * 通过Id得到题目
     * @desc 通过Id得到相应题目信息
     */
    public function getById()
    {
        $mq = new DomainBasic();

        $re = $mq::findQuestionById($this->Id, $this->UserId);
        return MyStandard::gReturn(0, $re);
    }
    /**
     * 通过Id得到相似题目推荐
     */
    public function getRecommendByQId()
    {
        $dq = new DomainRecommend();
        $re = $dq->recommendByQId($this->Id, $this->UserId, $this->Number);
        return MyStandard::gReturn(0, $re);
    }
    /**
     * 根据文字信息模糊匹配，用户笔记、错题匹配
     */
    public function getQuestionsByText()
    {
        $reslut = DomainBasic::matchQuestion($this->Text, $this->UserId, $this->Number); //查找前三个
        return MyStandard::gReturn(0, $reslut);
    }
    /**
     * 根据题目Id删除题目
     * @return 返回受影响行数
     */
    public function deleteQuestionByIds()
    {
        $ids = explode(",", $this->Ids);
        $domain = new DomainBasic();
        $re = $domain->deleteQuestions($ids);
        return MyStandard::gReturn(0, $re);
    }
    /**
     * 题目数量
     */
    public function getCount()
    {
        $domain = new DomainBasic();

        $re = $domain->countQuestions($this->CategoryId);
        return MyStandard::gReturn(0, $re);
    }

    

    /* -------------   ipso  -------------- */


    /**
		 * 删除题目
		 */
    public function delete()
    {
        $data = $this->Id;
        $domain = new DomainBasic();
        $res = $domain->delete($data);
        if ($res == 1) {
            return MyStandard::gReturn(1, '', '删除失败');
        }
        return MyStandard::gReturn(0, '', '删除成功');
    }

    /**
		 * 根据Id更新一道题目
		 */
    public function update()
    {
        $Id = $this->Id;
        $newData = array(
            'Content'    => $this->Content,
            'CategoryId' => $this->CategoryId,
            'Analysis'   => $this->Analysis,
            'Text'       => $this->Text,
            'Type'       => $this->Type,
            'AnswerVideo' => $this->AnswerVideo,
            'VideoCost' => $this->VideoCost
        );
        $domain = new DomainBasic();
        $res = $domain->updateQuestion($Id, $newData);

        if ($res == 1) {
            return MyStandard::gReturn(1, '', '更新失败');
        }

        return MyStandard::gReturn(0, '', '更新成功');
	}
		
	/**
	 * 获取题目的收藏前10
	 */
	public function getCollection(){
		$domain = new DomainBasic();
		$Collections = $domain -> getCollection();
		return MyStandard::gReturn(0,$Collections);
	}

	/**
	 * 获取题题目热度前十 
	 */
	public function getLike(){
		$domain = new DomainBasic();
		$Likes = $domain -> getLike();
		return MyStandard::gReturn(0,$Likes);
	}
}
