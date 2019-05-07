<?php
namespace App\Api;
use App\Domain\Userelation as Domain;
use PhalApi\Api;
use App\Common\MyStandard;
use App\Model\Userelation as Model;

/**
 * 班级类接口
 */
class Userelation extends Api{
	
	public function getRules(){
		return array(
			'add' => array(
				'Tid'  => array('name' => 'Tid', 'require' => true, 'desc' => '教师Id，必须'),
				'Sid'  => array('name' => 'Sid', 'desc' => '学生Id的字符串形式，英文逗号隔开'),
				'Cid' => array('name' => 'Cid', 'desc' => '科目Id'),
				'Endtime' => array('name' => 'Endtime', 'desc' => '班级结束时间'),
				'Intor' => array('name' => 'Intor',  'require' => true, 'desc' => '班级名称'),
			),
			'getCount' => array(
			),
			'getListTid'=>array(
				'Tid'  => array('name' => 'Tid', 'require' => true, 'desc' => '教师Id，必须'),
				'Page'  => array('name' => 'Page', 'default'=>1, 'desc' => '当前页'),
				'Number' => array('name' => 'Number', 'default'=>5,'desc' => '每页数量'),
			),
			'getListUid'=>array(
				'Uid'  => array('name' => 'Uid', 'require' => true, 'desc' => '学生Id，必须'),
				'Page'  => array('name' => 'Page',  'default'=>1,'desc' => '当前页'),
				'Number' => array('name' => 'Number','default'=>5, 'desc' => '每页数量'),
			),
			'getList' => array(
				'Page'  => array('name' => 'Page',  'default'=>1,'desc' => '当前页'),
				'Number' => array('name' => 'Number','default'=>5, 'desc' => '每页数量'),
			),
			'update' => array(
				'Id' => array('name' => 'Id',  'require' => true,'desc' => '班级Id'),
				'Tid'  => array('name' => 'Tid', 'require' => true, 'desc' => '教师Id'),
				'Sid'  => array('name' => 'Sid', 'desc' => '学生Id的字符串形式，英文逗号隔开'),
				'Cid' => array('name' => 'Cid', 'desc' => '科目Id'),
				'Endtime' => array('name' => 'Endtime', 'desc' => '班级结束时间'),
				'Intor' => array('name' => 'Intor', 'desc' => '简介'),
			),
			'delete' => array(
				'Id'  => array('name' => 'Id', 'require' => true, 'desc' => '要删除记录的Id'),
			),
			'addSid'=>array(
				'Id' => array('name' => 'Id',  'require' => true,'desc' => '班级Id'),
				'Sid'  => array('name' => 'Sid',  'require' => true,'desc' => '学生Id的字符串形式，英文逗号隔开'),
			),
			'getUsersById'=>array(
				'Id' => array('name' => 'Id',  'require' => true,'desc' => '班级Id'),
			)
		);
	}

	/**
	 * 添加一条记录
	 */
	public function add(){
		$data = array(
			'Tid'     => $this -> Tid,
			'Cid'     => $this -> Cid,
			'Endtime' => $this -> Endtime,
			'Intor'   => $this -> Intor,
		);
		if($this->Sid===null) $data["Sid"]="0,";
		if(!$this->Endtime) {
			$data["Endtime"] = date("Y-m-d", strtotime("+1 years", strtotime("now")));
		}
		$model = new Model();
		$sql = $model -> insertOne($data);
		if(!$sql){
			return MyStandard::gReturn(1, '', '添加失败');
		}
		return MyStandard::gReturn(0, $sql, '添加成功');
	}

	/**
	 * 更新一条记录， 新增学生也属于更新记录
	 */
	public function update(){
		$data = array(
			'Tid'     => $this -> Tid,
			'Sid'     => $this -> Sid,
			'Cid'     => $this -> Cid,
			'Endtime' => $this -> Endtime,
			'Intor'   => $this -> Intor,
			'Ctime'   => date('Y-m-d H:i:s'),
		);

		if($data["Tid"]==null) unset($data["Tid"]);
		if($data["Sid"]==null) unset($data["Sid"]);
		if($data["Cid"]==null) unset($data["Cid"]);
		if($data["Endtime"]==null) unset($data["Endtime"]);
		if($data["Intor"]==null) unset($data["Intor"]);


		$Id = $this -> Id;

		$model = new Model();		
		$sql = $model -> updateOne($Id, $data);

		if($sql==false){
			return MyStandard::gReturn(1, '', '更新失败，记录不存在');
		}
		if($sql==0){
			return MyStandard::gReturn(1, '', '更新失败');
		}
		return MyStandard::gReturn(0, '', '更新成功');
	}

	/**
	 * 获取班级数量
	 */
	public function getCount(){
		$model = new Model();
		$count = $model -> getCount();
		return MyStandard::gReturn(0, $count, '获取成功');
	}

	/**
	 * 获取班级列表
	 */
	public function getList(){
		$num = $this -> Number;
		$begin = ($this -> Page - 1) * 	$num ;
		$model = new Model();

		$list = $model -> getList($begin, $num);
		if(!$list){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $list, '获取成功');
	}
	/**
	 * 通过老师Id 获取班课列表
	 */
	public function getListTid(){
		$tid=$this->Tid;
		$num = $this -> Number;
		$begin = ($this -> Page - 1) * $num ;
		$model=new model();
		$list=$model->getByTid($tid,$begin,$num);
		if($list===false){
			return MyStandard::gReturn(1, [], '错误');
		}
		else if(!$list)return MyStandard::gReturn(0, [], '没有班级');
		return MyStandard::gReturn(0, $list, '获取成功');
	}
		/**
	 * 通过学生Id 获取班课列表
	 */
	public function getListUid(){
		$uid=$this->Uid;
		$num = $this -> Number;
		$begin = ($this -> Page - 1) * $num ;
		$model=new model();
		$list=$model->getByUid($uid,$begin,$num);
		if($list===false){
			return MyStandard::gReturn(1, [], '错误');
		}
		else if(!$list)return MyStandard::gReturn(0, [], '没有班级');
		return MyStandard::gReturn(0, $list, '获取成功');
	}
	/**
	 * 班级添加学生
	 */
	public function addSid(){
		$id=$this->Id;
		$Sid=$this->Sid;
		$domain=new Domain();
		$re=$domain->addSid($id,$Sid);
		if($re===false){
			return MyStandard::gReturn(1, '', '失败');
		}
		return MyStandard::gReturn(0, $re, '添加成功');
	}
	/**
	 * 根据班级Id获取学生列表
	 */
	public function getUsersById()
	{
		$id=$this->Id;
		$domain=new Domain();
		$res=$domain->getUserById($id);
		if($res===false) return MyStandard::gReturn(1, [], '失败');
		return MyStandard::gReturn(0, $res, '成功');
	}
}
