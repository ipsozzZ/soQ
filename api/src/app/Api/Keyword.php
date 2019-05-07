<?php
namespace App\Api;

use PhalApi\Api;

use App\Model\KeyWord as ModelKeyword;
use App\Common\MyStandard;

/**
 * 关键字
 * @author: goodtimp 2019-03-13
 */
class Keyword extends Api
{
  public function getRules()
  {
    return array(
      'add' => array(
        'Word' => array('name' => 'Word', 'require' => true, 'desc' => '关键字'),
        'Weight' => array('name' => 'Weight', 'require' => true,  'desc' => '权重'),  
        'CategoryId' => array('name' => 'CategoryId', 'default'=>10,  'desc' => '分类id'),  
			),
			'update' => array(
				'Id' => array('name' => 'Id', 'require' => true, 'desc' => '关键字Id'),
				'Word' => array('name' => 'Word', 'desc' => '关键字'),
        'Weight' => array('name' => 'Weight', 'desc' => '权重'),  
        'CategoryId' => array('name' => 'CategoryId', 'default'=>10,  'desc' => '分类id'),
			),
			'deleteKeys' => array(
				'Id' => array('name' => 'Id', 'require' => true, 'desc' => '关键字Id'),
			),
			'getList' => array(
				'Page' => array('name' => 'Page', 'desc' => ''),
				'Number' => array('name' => 'Number', 'desc' => '关键字Id'),
			),
			'getCount' => array(
			), 
			'getCountByWord' => array(
				'Word' => array('name' => 'Word', 'desc' => '关键字'),
			), 
			'getByWord' => array(
				'Word' => array('name' => 'Word', 'desc' => '关键字'),
				'Page' => array('name' => 'Page', 'desc' => ''),
				'Number' => array('name' => 'Number', 'desc' => '关键字Id'),
			),
		
    );
  }
  /**
     * 添加关键字信息
     * @return id，-1为已经关注,
     */
  public function add()
  {
    $data=array(
      'Word'=>$this->Word,
      'Weight'=>$this->Weight,
      'CategoryId'=>$this->CategoryId
    );
    $dm = new ModelKeyword();
    $re = $dm->addKeyword($data);
    return MyStandard::gReturn(0, $re);
	}

	

	/* --------------   ipso   ----------------- */

	/**
	 * 更新关键字
	 */
	public function update(){
		$Id = $this -> Id;
		$data=array(
      'Word'=>$this->Word,
      'Weight'=>$this->Weight,
      'CategoryId'=>$this->CategoryId
		);
		$dm = new ModelKeyword();
    $re = $dm->updateKeyword($Id, $data);
    return MyStandard::gReturn(0, $re);
	}

	/**
	 * 删除一条或多条关键字（英文逗号隔开）
	 */
	public function deleteKeys(){
		$dm = new ModelKeyword();
		$Id = $this -> Id;
		$Ids = explode(',', $Id);
		$flag = true;
		$mark = '';
		$count = count($Ids);
		for($i = 0; $i < $count; $i++){
			$res = $dm -> deleteOne($Ids[$i]);
			if(!$res){
				$flag = false;
				$mark = $Ids[$i];
				break;
			}
		}
		if($flag == false){
			return MyStandard::gReturn(1, '', '删除失败,'.$mark.'不存在');
		}
		return MyStandard::gReturn(0, '', '删除成功');
	}

	/**
	 * 获取关键字数量
	 */
	public function getCount(){
		$dm = new ModelKeyword();
		$count = $dm -> getCount();
		return MyStandard::gReturn(0, $count, '获取成功');
	}

	/**
	 * 获取关键字列表
	 */
	public function getList(){
		$dm = new ModelKeyword();
		$begin = ($this -> Page - 1) * $this -> Number;
		$list = $dm -> getList($begin, $this -> Number);
		if(!$list){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $list, '获取成功');
	}

	/**
	 * 更具word获取数据库记录数
	 */
	public function getCountByWord(){
		$dm = new ModelKeyword();
		$word = $this -> Word;
		$count = $dm -> getCountByWord($word);
		return MyStandard::gReturn(0, $count, '获取成功');
	}

	/**
	 * 根据关键字获取所有记录
	 */
	public function getByWord(){
		$dm = new ModelKeyword();
		$begin = ($this -> Page - 1) * $this -> Number;
		$word = $this -> Word;
		$list = $dm -> getByWord($word, $begin, $this -> Number);
		if(!$list){
			return MyStandard::gReturn(1, '', '获取失败');
		}
		return MyStandard::gReturn(0, $list, '获取成功');
	}
}
