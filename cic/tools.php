<?php
# данный модуль для mysql
# для другого DBMS надо переписать
include("config.php");

class DataBasePDO{
	var $pdo;
	var $error;
	
	public function __construct($param) {
        $this->Connect($param);
    }
	
	function Connect($dbms){
		if (!$dbms){$dbms = DBMS_DEFAULT;}
		$dbhost = DB_HOST;
		$dbname = DB_NAME;
		$dbuser = DB_USER;
		$dbpass = DB_PASS;
		$err = "";
		switch ($dbms){ // Для уникальных подключений добавьте ветку case как в случае с SQLite
			case "sqlite": // SQLite
				try {
					$pdo = new PDO($sqlite_path); 
				}catch(PDOException $e){
					$err = $e->getMessage();  
				}
				break;
			default:  // mysql - MуSQL, pgsql - PostgreSQL, sybase - Sybase, mssql - MS SQL
				try {
					$pdo = new PDO("$dbms:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
				}catch(PDOException $e){
					$err = $e->getMessage();  
				}
				break;
		}
		if ($pdo){
			// Отключаем режим эмуляции, потому что в теге sql пишем переменные с кавичками 
			// например: <sql>SELECT * FROM database WHERE user_name='[*POST.name*]' LIMIT [*GET.count*]</sql>
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			//$pdo->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, false);
		}
		$this->error = $err;
		$this->pdo = $pdo;
	}
	
	function GetTabFromSQL($pdo_query, $pdo_array){
		$tab = null;
		if ($this->pdo){
			$stmt = $this->pdo->prepare($pdo_query);
			$stmt->execute($pdo_array);
			if (!$stmt){
				$err = $this->pdo->errorInfo();
			}else{
				$rowCount = $stmt->rowCount();
				$tab = $stmt->fetchAll(PDO::FETCH_CLASS);
				//print_r($tab);exit();
				//if ($rowCount>1) {print_r($tab);exit();}
				
				$err ="";
			}
			$this->error = $err;
		}
		return $tab;
	}
	
	function ExecSQL($pdo_query, $pdo_array){
		$tab = null;
		if ($this->pdo){
			$stmt = $this->pdo->prepare($pdo_query);
			$stmt->execute($pdo_array);
			if (!$stmt){
				$err = $this->pdo->errorInfo();
			}else{
				$err ="";
			}
			$this->error = $err;
		}
		return $tab;
	}
	
	function NewPage($name, $note){
		$pdo_query = "INSERT INTO `_cic_pages` (`name`, `note`) VALUES (:name, :note)";
		$pdo_array = array('name' => $name, 'note' => $note);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function RenamePage($page, $newname){
		$pdo_query = "UPDATE `_cic_pages` SET `name` = :newname WHERE `name` = :page";
		$pdo_array = array('page' => $page, 'newname' => $newname);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function DeletePage($page){// добавить удаление всех контейнеров
		$pdo_query = "DELETE FROM `_cic_pages` WHERE `name` = :page";
		$pdo_array = array('page' => $page);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetPageNote($page){
		$pdo_query = "SELECT `note` FROM `_cic_pages`  WHERE `name`= :page";
		$pdo_array = array('page' => $page);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->note;}
		return $res;
	}
	function SetPageNote($page, $note){
		$pdo_query = "UPDATE `_cic_pages` SET `note` = :note WHERE `_cic_pages`.`name` = :page";
		$pdo_array = array('page' => $page, 'note' => $note);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetPageId($page){
		$pdo_query = "SELECT `id` FROM `_cic_pages`  WHERE `name`= :page";
		$pdo_array = array('page' => $page);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->id;}
		return $res;
	}
	function GetPageInsDate($page){
		$pdo_query = "SELECT `insdate` FROM `_cic_pages`  WHERE `name`= :page";
		$pdo_array = array('page' => $page);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->insdate;}
		return $res;
	}
	function GetPageUpDate($page){
		$pdo_query = "SELECT `update` FROM `_cic_pages`  WHERE `name`= :page";
		$pdo_array = array('page' => $page);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->update;}
		return $res;
	}

	function NewContainer($page, $name, $note){
		$pageID = $this->GetPageId($page);
		$pdo_query = "INSERT INTO `_cic_container` (`page`, `name`, `note`, `content`) VALUES (:page, :name, :note, '')";
		$pdo_array = array('name' => $name, 'page' => (int)$pageID, 'note' => $note);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function RenameContainer($page, $container, $newname){
		$pdo_query = "UPDATE `_cic_container` AS `cont`, `_cic_pages` AS `page` SET `cont`.`name` = :newname WHERE `cont`.`name` = :container AND `page`.`name` = :page AND `cont`.`page` = `page`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container, 'newname' => $newname);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function DeleteContainer($page, $container){
		$pdo_query = "DELETE FROM `_cic_container` WHERE `name`=:container and `page` IN (SELECT `id` as `page` FROM `_cic_pages` WHERE `name`=:page)";
		$pdo_array = array('page' => $page, 'container' => $container);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetContainerNote($page, $container){
		$pdo_query = "SELECT `c`.`note` FROM `_cic_container` AS `c`, `_cic_pages` AS `p` WHERE `p`.`name`= :page AND `c`.`name`=:container AND `c`.`page`=`p`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->note;}
		return $res;
	}
	function SetContainerNote($page, $container, $note){
		$pdo_query = "UPDATE `_cic_container` AS `cont`, `_cic_pages` AS `page` SET `cont`.`note` = :note WHERE `cont`.`name` = :container AND `page`.`name` = :page AND `cont`.`page` = `page`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container, 'note' => $note);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function SetContainerPage($page, $container, $newpage){
		$id = $this->GetPageId($newpage);
		$pdo_query = "UPDATE `_cic_container` AS `cont`, `_cic_pages` AS `page` SET `cont`.`page` = :newpageid WHERE `cont`.`name` = :container AND `page`.`name` = :page AND `cont`.`page` = `page`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container, 'newpageid' => $id);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetContainerId($page, $container){
		$pdo_query = "SELECT `c`.`id` FROM `_cic_container` AS `c`, `_cic_pages` AS `p` WHERE `p`.`name`= :page AND `c`.`name`=:container AND `c`.`page`=`p`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->id;}
		return $res;
	}
	function GetContainerInsDate($page, $container){
		$pdo_query = "SELECT `c`.`insdate` FROM `_cic_container` AS `c`, `_cic_pages` AS `p` WHERE `p`.`name`= :page AND `c`.`name`=:container AND `c`.`page`=`p`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->insdate;}
		return $res;
	}
	function GetContainerUpDate($page, $container){
		$pdo_query = "SELECT `c`.`update` FROM `_cic_container` AS `c`, `_cic_pages` AS `p` WHERE `p`.`name`= :page AND `c`.`name`=:container AND `c`.`page`=`p`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->update;}
		return $res;
	}
	function GetContainerContent($page, $container){
		$pdo_query = "SELECT `content` FROM `_cic_container` as `c`, `_cic_pages` as `p` WHERE `c`.`page`=`p`.`id` and `p`.`name`=:page and `c`.`name`=:cont";
		$pdo_array = array('page' => $page, 'cont' => $container);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->content;}
		return $res;
	}
	function SetContainerContent($page, $container, $content){
		$pdo_query = "UPDATE `_cic_container` AS `cont`, `_cic_pages` AS `page` SET `cont`.`content` = :content WHERE `cont`.`name` = :container AND `page`.`name` = :page AND `cont`.`page` = `page`.`id`";
		$pdo_array = array('page' => $page, 'container' => $container, 'content' => $content);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}

	function NewConfigKey($key, $value){
		$pdo_query = "INSERT INTO `_cic_config` (`key`, `value`) VALUES (:key, :value)";
		$pdo_array = array('key' => $key, 'value' => $value);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigKey($key){
		$pdo_query = "SELECT `key` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->key;}
		return $res;
	}
	function RenameConfigKey($key, $newname){
		$pdo_query = "UPDATE `_cic_config` SET `key` = :newname WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'newname' => $newname);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function DeleteConfigKey($key){
		$pdo_query = "DELETE FROM `_cic_config` WHERE `key` = :key";
		$pdo_array = array('key' => $key);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigValue($key){
		$pdo_query = "SELECT `value` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->value;}
		return $res;
	}
	function SetConfigValue($key, $val){
		$pdo_query = "UPDATE `_cic_config` SET `value` = :val WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'val' => $val);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function SetConfigDescription($key, $val){
		$pdo_query = "UPDATE `_cic_config` SET `description` = :val WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'val' => $val);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigDescription($key){
		$pdo_query = "SELECT `description` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->description;}
		return $res;
	}
	function SetConfigOpt1($key, $val){
		$pdo_query = "UPDATE `_cic_config` SET `opt1` = :val WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'val' => $val);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigOpt1($key){
		$pdo_query = "SELECT `opt1` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->opt1;}
		return $res;
	}
	function SetConfigOpt2($key, $val){
		$pdo_query = "UPDATE `_cic_config` SET `opt2` = :val WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'val' => $val);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigOpt2($key){
		$pdo_query = "SELECT `opt2` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->opt2;}
		return $res;
	}
	function SetConfigGroup($key, $val){
		$pdo_query = "UPDATE `_cic_config` SET `group` = :val WHERE `key` = :key";
		$pdo_array = array('key' => $key, 'val' => $val);
		$this->pdo->prepare($pdo_query)->execute($pdo_array);
	}
	function GetConfigGroup($key){
		$pdo_query = "SELECT `group` FROM `_cic_config` WHERE `key`= :key";
		$pdo_array = array('key' => $key);
		$tab = $this->GetTabFromSQL($pdo_query, $pdo_array);
		if ($this->error == "" and $tab){$res = $tab[0]->group;}
		return $res;
	}
}
?>