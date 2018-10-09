<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model {

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("select * from tb_categories ORDER BY descategory");

	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]);

		Category::updateFile();

	}

	public function get($idcategory){

		$sql = new Sql();

		$results = $sql->select("Select * from tb_categories where idcategory = :idcategory", [
			':idcategory'=>$idcategory
		]);

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$sql->query("Delete from tb_categories where idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
		]);

		Category::updateFile();

	}

	public static function updateFile(){

		$categories = Category::listall();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/php/ecommerce/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."ecommerce".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('',$html) );

	}

	public function getProducts($related = true){

		$sql = new Sql();
		if($related === true){

			return $sql->select("
				select * from tb_products where idproduct in(
				select a.idproduct 
				from tb_products a 
				inner join tb_productscategories b on a.idproduct = b.idproduct 
				where b.idcategory = :idcategory
				);",[
				':idcategory'=>$this->getidcategory()
			]);

		}else{

			return $sql->select("
				select * from tb_products where idproduct not in(
				select a.idproduct 
				from tb_products a 
				inner join tb_productscategories b on a.idproduct = b.idproduct 
				where b.idcategory = :idcategory
				);",[
				':idcategory'=>$this->getidcategory()
			]);

		}
	}

	public function addProduct(Product $product){

		$sql = new Sql();

		$sql->query("insert into tb_productscategories (idcategory, idproduct) values(:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public function removeProduct(Product $product){

		$sql = new Sql();

		$sql->query("delete from tb_productscategories where idcategory = :idcategory and idproduct = :idproduct", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}
	

}

?>