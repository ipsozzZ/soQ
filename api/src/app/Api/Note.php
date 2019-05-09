<?php
namespace App\Api;

use PhalApi\Api;


use App\Common\MyStandard;
use App\Domain\Behavior\Statistics as ModelStatistics;
use App\Model\Notecategory as ModelNoteCategory;
use App\Domain\Note as DomainNote;
use App\Model\Note as Model;

/**
 * 笔记接口类
 * @author ipso
 */
class Note extends Api
{
	public function getRules()
	{
		return array(
			'cates' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
			),
			'addCate' => array(
				'Name' => array('name' => 'Name', 'require' => true,  'desc' => '分类名'),
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'Intro' => array('name' => 'Intro',  'desc' => '介绍'),
			),
			'getOne' => array(
				'Id' => array('name' => "Id", 'require' => true, 'min' => 1, 'decs' => "笔记Id")
			),
			'notesByKeys' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'NoteCategoryId'   => array('name' => 'NoteCategoryId', 'default' => 0, 'desc' => '分类Id'),
				'key' => array('name' => 'keys', 'require' => true, 'min' => 1, 'max' => 50, 'desc' => '关键字'),
			),
			'notesByCate' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'NoteCategoryId'   => array('name' => 'NoteCategoryId', 'require' => true, 'desc' => '分类Id'),
				'Number'  => array('name' => 'Number', 'default' => 5, 'desc' => '需要的数量'),
				'Page' => array('name' => 'Page', 'default' => 1, 'desc' => '题目页数')
			),
			'add' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'Headline'     => array('name' => 'Headline', 'require' => true,'desc' => '笔记标题'),
				'NoteCategoryId' => array('name' => 'NoteCategoryId', 'require' => true,'desc' => '笔记分类id'),
				'Content'      => array('name' => 'Content','require' => true, 'desc' => '笔记内容'),
			),
			'update' => array(
				'Id'           => array('name' => 'Id', 'require' => true,'desc' => '笔记id'),
				'Headline'     => array('name' => 'Headline', 'require' => true,'desc' => '笔记标题'),
				'NoteCategoryId' => array('name' => 'NoteCategoryId', 'require' => true,'desc' => '笔记分类id'),
				'Content'      => array('name' => 'Content', 'require' => true,'desc' => '笔记内容'),
			),
			'delete' => array(
				'Nid'  => array('name' => 'Id', 'require' => true, 'desc' => '笔记id'),
			),
			'count' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'desc' => '用户Id')
			),
			'deleteCategory' => array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'NoteCategoryId'   => array('name' => 'NoteCategoryId', 'require' => true, 'desc' => '分类Id'),
			),
			'updateCategory' => array(
				'NoteCategoryId'   => array('name' => 'NoteCategoryId', 'require' => true, 'desc' => '分类Id'),
				'Name' => array('name' => 'Name',  'default' => null, 'desc' => '分类名'),
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'Intro' => array('name' => 'Intro',   'default' => null, 'desc' => '介绍'),
			),
			'getList' => array(
				'Page'   => array('name' => 'Page', 'require' => true, 'desc' => '当前页'),
				'Number' => array('name' => 'Number',  'default' => null, 'desc' => '每页数量'),
			),
			'getCount' => array(
			),
			'getAllCate'  => array(),
			'getKeysByUser'=>array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'NoteCategoryId' => array('name' => 'NoteCategoryId', 'default'=>0, 'desc' => '分类Id'),
				'Number' => array('name' => 'Number',  'default' => 4, 'desc' => '获取标签数量，默认推送输入数量+最近上传笔记中最大weight一个'),
			),
			'notesByKeyIds'=>array(
				'UserId' => array('name' => 'UserId', 'require' => true, 'min' => 1, 'desc' => 'user id'),
				'NoteCategoryId' => array('name' => 'NoteCategoryId', 'default'=>0, 'desc' => '分类Id'),
				'KeyIds'=>array('name' => 'KeyIds', 'default'=>0,'default'=>"",'desc' => '关键字Id,逗号隔开,例如1,2')
			),
			'deleteByAdmin' => array(
				"Id" => array('name' => 'Id', 'require' => true, 'desc' => '需要删除的笔记Id'),
				"UId" => array('name' => 'UserId', 'require' => true, 'desc' => '需要删除的笔记的用户id'),
			),
		);
	}
	/** 得到用户笔记数量
   * 
   */
	public function count()
	{
		$kk = new DomainNote();
		$re = $kk->getCountByUserId($this->UserId);
		return MyStandard::gReturn(0, $re);
	}
	/**
     * 得到所有笔记分类信息
     */
	public function cates()
	{
		$uid = $this->UserId;
		$mc = new ModelNoteCategory();
		$re = $mc->getNotesCategoryByUserId($uid); //用户Id 需修改
		return MyStandard::gReturn(0, $re);
	}
	/**
	 * 添加笔记分类
	 */
	public function addCate()
	{
		$data = array(
			'Name' => $this->Name,
			'UserId' => $this->UserId,
			'Intro' => $this->Intro
		);
		$mc = new ModelNoteCategory();
		$re = $mc->addCategory($data);
		return MyStandard::gReturn(0, $re);
	}
	/**
   * 根据关键字搜索用户笔记
   */
	public function notesByKeys()
	{
		$dn = new DomainNote();
		$uid = $this->UserId;
		$re = $dn->getNotesByKeywords($uid, $this->NoteCategoryId, $this->key);

		return MyStandard::gReturn(0, $re);
	}
	/**
   * 根据关键字搜索用户笔记
   */
	public function notesByKeyIds()
	{
		$dn = new DomainNote();
		$uid = $this->UserId;
		$re = $dn->getNotesByKeyIds($uid, $this->NoteCategoryId, $this->KeyIds);

		return MyStandard::gReturn(0, $re);
	}



	/**
	 * 根据用户分类得到所有notes
	 */
	public function notesByCate()
	{
		$dn = new DomainNote();

		$num = $this->Number;
		$page = $this->Page;
		//分页
		$re = $dn->getNotesByCateId($this->NoteCategoryId, $this->UserId, $page, $num);
		return MyStandard::gReturn(0, $re);
	}

	/**得到某个笔记 */
	public function getOne()
	{
		$dn = new DomainNote();
		$re = $dn->getNoteById($this->Id);
		return MyStandard::gReturn(0, $re);
	}
	/**
	 * 添加一条笔记
	 */
	public function add()
	{
		$data = array(
			'UserId' => $this->UserId,
			'Headline'     => $this->Headline,
			'NoteCategoryId' => $this->NoteCategoryId,
			'Content'      => $this->Content,
		);

		$domain = new DomainNote();
		$result = $domain->add($data);
		return MyStandard::gReturn(0, $result, '添加成功');
	}

	/**
	 * 更新一条笔记
	 */
	public function update()
	{
		$data = array(
			'Id'           => $this->Id,
			'Headline'     => $this->Headline,
			'NoteCategoryId' => $this->NoteCategoryId,
			'Content'      => $this->Content,
		);

		$domain = new DomainNote();
		$result = $domain->update($data);
		return MyStandard::gReturn(0, $result, '更新成功');
	}
	/**
	 * 删除一条笔记
	 */
	public function delete()
	{
		$nid = $this->Nid;
		$domain = new DomainNote();
		$result = $domain->delete($nid);
		return MyStandard::gReturn(0, $result, '删除成功');
	}

	/**
 * 更新笔记分类
 */
	public function updateCategory()
	{
		$data = array(
			"UserId" => $this->UserId,
			"Id" => $this->NoteCategoryId
		);
		if ($this->Intro) $data["Intro"] = $this->Intro;
		if ($this->Name) $data["Name"] = $this->Name;
		$domain = new DomainNote();
		$re = $domain->updateCategory($data);
		return MyStandard::gReturn(0, $re);
	}
	/**
 * 删除笔记分类和其中内容
 */
	public function deleteCategory()
	{
		$data = array(
			"UserId" => $this->UserId,
			"Id" => $this->NoteCategoryId
		);
		$domain = new DomainNote();
		$re = $domain->deleteCategory($data);
		return MyStandard::gReturn(0, $re);
	}

	/**
	 * 获取用户或者分类笔记所有关键字信息
	 */
	public function getKeysByUser(){
		$domain = new DomainNote();
		$re = $domain->getKeysByUserNotes($this->UserId,$this->NoteCategoryId,$this->Number);
		return MyStandard::gReturn(0, $re);
	}
	/* ----------------  ipso  ---------------- */

	/**
	 * 分页获取笔记列表
	 */
	public function getList(){
		$model = new Model();
		$begin = ($this -> Page - 1) * $this -> Number;
		$list = $model -> getList($begin, $this -> Number);
		if(!$list){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $list, '获取成功');
	}


	/**
	 * 获取笔记数量
	 */
	public function getCount(){
		$model = new Model();
		$count = $model -> getCount();
		if(!$count){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $count, '获取成功');
	}

	/**
	 * 获取所有笔记分类
	 */
	public function getAllCate(){
		$model = new ModelNoteCategory();
		$cateList = $model -> getAll();
		if(!$cateList){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $cateList, '获取成功');
	}

	/**
	 * 管理员清理敏感笔记
	 */
	public function deleteByAdmin(){
		$domain = new DomainNote();
		$UId = $this -> UId;
		$Id = $this -> Id;
		$res = $domain -> deleteByAdmin($UId, $Id);
		if($res == false){
			return MyStandard::gReturn(1, '', '删除失败');
		}
		return MyStandard::gReturn(0, '', '删除成功');
	}
}
