<?php

define('ERR_InvalidName', 'ERR_InvalidName'); //'Invalid name!');
define('ERR_ExistName', 'ERR_ExistName'); //'Exist name');
define('ERR_NoneName', 'ERR_NoneName'); //'None name');
define('ERR_NoneEmpty', 'ERR_NoneEmpty'); //'None empty');
define('ERR_NotLogin', 'ERR_NotLogin'); //'Not login');
define('ERR_Password', 'ERR_Password'); //'Wrong password');
define('ERR_PassRep', 'ERR_PassRep'); //'Passwords dont match');

define('MSG_SettingSuccess', 'MSG_SettingSuccess'); //'Setting Success');

define('COOKIE_ADMIN', 'admin_name');
define('COOKIE_EXP_TIME', 60*60*24*10); // 10 day


include("main.php");
include("tools.php");

function LogOut($db){
	$query = "UPDATE `_cic_config` SET `opt1`=0, `opt2`='' WHERE `group`=1";
	$tab = $db->GetTabFromSQL($query, $array);
	setcookie(COOKIE_ADMIN,	"", 0, "/");
}
function ValidName($str){
	if (preg_match("/^[a-z0-9_]+$/i", $str)) {return true;}else{return false;}
}
function PrintJSON($var){
	header('Content-Type: application/json; charset=utf-8');
	echo (json_encode($var));
}
$db = new DataBasePDO(null);

$action = $_GET["action"];
$res = "";
$err = "";

// login
if ($action == "login") {
	$query = "SELECT `key` FROM `_cic_config` WHERE `group`=1 AND `key`=:admin_name AND `value`=sha(:passw)";
	$array = array('admin_name' => $_POST['admin_name'], 'passw' => $_POST['password']);
	$tab = $db->GetTabFromSQL($query, $array);
	$loc = $_SERVER['HTTP_REFERER'];
	if ($tab){// если key вернул то:
		$query = "UPDATE `_cic_config` SET `opt1`=UNIX_TIMESTAMP(NOW())+:exp_time, `opt2`=md5(:ssn) WHERE `group`=1 AND `key`=:admin_name";
		$ssn = $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'];
		$array = array('admin_name' => $_POST['admin_name'], 'exp_time'=>COOKIE_EXP_TIME, 'ssn' => $ssn);
		$tab = $db->pdo->prepare($query)->execute($array);
		$exptime = time() + COOKIE_EXP_TIME;
		setcookie("admin_name",	md5($_POST['admin_name']), $exptime, "/");
		header("location: $loc");
	}else{ //если key не вернул то logout with error:
		header("location: $loc");
	}
	exit();
}


//проверка авторизации: проверяем admin_name и ssn, и время
$key = $_COOKIE[COOKIE_ADMIN];
$ssn = $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'];
$query = "SELECT `key` FROM `_cic_config` WHERE md5(`key`)='$key' AND md5('$ssn') = `opt2` AND UNIX_TIMESTAMP(NOW())<`opt1`";
$array = array();
$tab = $db->GetTabFromSQL($query, $array);
if (!$tab){
	LogOut($db);
	$res = array('err'=>ERR_NotLogin, 'obj'=>'', 'res'=>'');
	PrintJSON($res);
	exit();
}

switch ($action) {
	case 'register': break;
	case 'login': break;
	case 'logout': 
		logOut($db); 
		$loc = $_SERVER['HTTP_REFERER']; 
		header("location: $loc"); 
		exit();
		break;
	
	case 'GetPages':
		$query = 'select * from `_cic_pages` where `parent`=:parent order by `name`' ;
		$array = array('parent' => 0);
		$tab = $db->GetTabFromSQL($query, $array);
		$res = array('err'=>$db->error, 'obj'=>'', 'res'=>$tab);
		PrintJSON($res);
		break;
		
	case 'GetContainers':
		$query = "select c.* from `_cic_container` as c, `_cic_pages` as p where p.`name`=:page and p.`id`=c.`page`";
		$array = array('page' => $_GET['page']);
		$tab = $db->GetTabFromSQL($query, $array);
		$res = array('err'=>$db->error, 'obj'=>'', 'res'=>$tab); 
		PrintJSON($res);
		break;
	
	case 'GetPageInfo':
		$query = "select * from `_cic_pages` where `name`=:page";
		$array = array('page' => $_GET['page']);
		$tab = $db->GetTabFromSQL($query, $array);
		$res = array('err'=>$db->error, 'obj'=>'', 'res'=>$tab[0]); 
		PrintJSON($res);
		break;
	
	case 'GetContainerInfo':
		$query = "select c.* from `_cic_container` as c, `_cic_pages` as p where p.`name`=:page and c.`name`= :container and p.`id`=c.`page`";
		$array = array('page' => $_GET['page'], 'container' => $_GET['container']);
		$tab = $db->GetTabFromSQL($query, $array);
		$res = array('err'=>$db->error, 'obj'=>'', 'res'=>$tab[0]); 
		PrintJSON($res);
		break;
		
	case 'NewPage': 
		$name = $_POST['name'];
		$note = $_POST['note'];
		if (ValidName($name)){
			if (!$db->GetPageId($name)){
				$db->NewPage($name, $note);
				$err = $db->error;
				$res = 'ok';
			}else{$err = ERR_ExistName;}
		}else{$err = ERR_InvalidName;}
		$res = array('err'=>$err, 'obj'=>$name, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'RenamePage': 
		$name = $_POST['newname'];
		$page = $_POST['page'];
		if ($db->GetPageId($page)){
			if (ValidName($name)){
				if (!$db->GetPageId($name)){
					$db->RenamePage($page, $name);
					$err = $db->error;
					$res = 'ok';
				}else{$err = ERR_ExistName; $obj = $name;}
			}else{$err = ERR_InvalidName; $obj = $name;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'DeletePage': 
		$page = $_POST['page'];
		
		if ($db->GetPageId($page)){			
			$db->DeletePage($page);
			$err = $db->error;
			$res = 'ok';
		}else{$err = ERR_NoneName; }
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'ClonePage': 
		$page = $_POST['page'];
		$name = $_POST['name'];
		$note = $_POST['note'];
		if (ValidName($name)){
			if (!$db->GetPageId($name)){
				$db->NewPage($name, $note);
				$err = $db->error;
				$res = 'ok';
				$query = "INSERT INTO `_cic_container` (`page`, `name`, `order`, `content`, `note`) SELECT :newpageID, `name`, `order`, `content`, `note` FROM `_cic_container` WHERE `page`=:pageID";
				$pageID = $db->GetPageId($page);
				$newpageID = $db->GetPageId($name);
				$array = array('pageID' => $pageID, 'newpageID' => $newpageID);
				$tab = $db->GetTabFromSQL($query, $array);
			}else{$err = ERR_ExistName;}
		}else{$err = ERR_InvalidName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetPageNote': 
		$page = $_GET['page'];
		if ($db->GetPageId($page)){
			$res = $db->GetPageNote($page);
			$err = $db->error;
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetPageNote': 
		$page = $_POST['page'];
		$note = $_POST['note'];
		if ($db->GetPageId($page)){
			$db->SetPageNote($page, $note);
			$err = $db->error;
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetPageId': 
		$page = $_GET['page'];
		if ($db->GetPageId($page)){
			$res = $db->GetPageId($page);
			$err = $db->error;
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetPageInsDate': 
		$page = $_GET['page'];
		if ($db->GetPageId($page)){
			$res = $db->GetPageInsDate($page);
			$err = $db->error;
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetPageUpDate': 
		$page = $_GET['page'];
		if ($db->GetPageId($page)){
			$res = $db->GetPageUpDate($page);
			$err = $db->error;
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$page, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'NewContainer':
		$page = $_POST['page'];
		$name = $_POST['name'];
		$note = $_POST['note'];
		if (ValidName($name)){
			if (!$db->GetContainerId($page, $name)){
				$db->NewContainer($page, $name, $note);
				$err = $db->error;
				$res = $page;
			}else{$err = ERR_ExistName;}
		}else{$err = ERR_InvalidName;}
		$res = array('err'=>$err, 'obj'=>$name, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'RenameContainer':
		$page = $_POST['page'];
		$container = $_POST['container'];
		$name = $_POST['name'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				if (ValidName($name)){
					if (!$db->GetContainerId($page, $name)){	
						$db->RenameContainer($page, $container, $name);
						$err = $db->error;
						$res = $name;
					}else{$err = ERR_ExistName; $obj = $name;}
				}else{$err = ERR_InvalidName; $obj = $name;}
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'DeleteContainer': 
		$page = $_POST['page'];
		$container = $_POST['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$db->DeleteContainer($page, $container);
				$err = $db->error;
				$res = $page;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'CopyContainer': 
		$page = $_POST['page'];
		$container = $_POST['name'];
		$newpage = $_POST['newpage'];
		$newname = $_POST['newname'];
		$note = $_POST['note'];
		if ($db->GetPageId($page)){
			if ($db->GetPageId($newpage)){
				if ($db->GetContainerId($page, $container)){
					if (ValidName($newname)){
						if (!$db->GetContainerId($newpage, $newname)){
							$db->NewContainer($newpage, $name, $note);
							$err = $db->error;
							$res = 'ok';
							$query = "INSERT INTO `_cic_container`(`page`, `name`, `order`, `content`, `note`) 
SELECT :newpageID, :newname, c.`order`, c.`content`, :note FROM `_cic_container` AS `c`, `_cic_pages` AS `p` WHERE `c`.`name`=:container AND `c`.`page`=`p`.`id` AND `p`.`name`=:page";
							$newpageID = $db->GetPageId($newpage);
							$array = array('newpageID' => $newpageID, 'newname' => $newname, 'container' => $container, 'page' => $page, 'note' => $note);
							$tab = $db->GetTabFromSQL($query, $array);
							$res=$newpage;
						}else{$err = ERR_ExistName; $obj = "$newpage - $newname";}
					}else{$err = ERR_InvalidName; $obj = $newname;}
				}else{$err = ERR_NoneName; $obj = "$page - $container";}
			}else{$err = ERR_NoneName; $obj = $newpage;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res);
		PrintJSON($res);
		break;
		
	case 'GetContainerNote': 
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$res = $db->GetContainerNote($page, $container);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetContainerNote': 
		$page = $_POST['page'];
		$container = $_POST['container'];
		$note = $_POST['note'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$db->SetContainerNote($page, $container, $note);
				$err = $db->error;
				$res = $page;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetContainerPage': 
		$page = $_GET['page'];
		$newpage = $_GET['newpage'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				if ($db->GetPageId($newpage)){
					$db->SetContainerPage($page, $container, $newpage);
					$err = $db->error;
					$res = 'ok';
				}else{$err = ERR_NoneName; $obj = $newpage;}
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetContainerId':
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$res = $db->GetContainerId($page, $container);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetContainerInsDate': 
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$res = $db->GetContainerInsDate($page, $container);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetContainerUpDate': 
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$res = $db->GetContainerUpDate($page, $container);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName ; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetContainerContent': 
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$res = $db->GetContainerContent($page, $container);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'SetContainerContent': 
		$page = $_POST['page'];
		$container = $_POST['container'];
		$content = $_POST['content'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$db->SetContainerContent($page, $container, $content);
				$err = $db->error;
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetContainerVar': 
		$page = $_GET['page'];
		$container = $_GET['container'];
		if ($db->GetPageId($page)){
			if ($db->GetContainerId($page, $container)){
				$content = $db->GetContainerContent($page, $container);
				$err = $db->error;
				$doc = new DOMDocument();
				$content = str_replace("&amp;", "&", $content);
				$content = str_replace("&", "&amp;", $content);
				
				if ($doc->loadXML("<" . TAG_IGNOR . ">" . $content. "</" . TAG_IGNOR . ">")){
					
					$cic = new CicNodeXml;
					$html = $cic->Exect($doc->documentElement, $container, null, $db);
					$res = $cic->GetVars();
					//$res = json_encode($cic->GetVars());
					
				}else{
					foreach (libxml_get_errors() as $error) {
						$err = $err . $error->message . ". in page: <b>$page[p]</b>, container: <b>$container</b>.<br/>"; 
					}
					libxml_clear_errors();
				}
			}else{$err = ERR_NoneName; $obj = $container;}
		}else{$err = ERR_NoneName; $obj = $page;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'NewConfigKey': 
		$key = $_POST['key'];
		$value = $_POST['value'];
		if (ValidName($key)){
			if (!$db->GetConfigKey($key)){
				$db->NewConfigKey($key, $value);
				$err = $db->error;
			}else{$err = ERR_ExistName;}
		}else{$err = ERR_InvalidName;}
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'RenameConfigKey':
		$key = $_GET['key'];
		$newname = $_GET['newname'];
		if ($db->GetConfigKey($key)){
			if (ValidName($newname)){
				if (!$db->GetConfigKey($newname)){
					$db->RenameConfigKey($key, $newname);
					$err = $db->error;
				}else{$err = ERR_ExistName; $obj = $newname;}
			}else{$err = ERR_InvalidName; $obj = $newname;}
		}else{$err = ERR_NoneName; $obj = $key;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'DeleteConfigKey':
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$db->DeleteConfigKey($key);
			$err = $db->error;
			$res = 'ok';
		}else{$err = ERR_NoneName;}
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetConfigValue': 
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$res = $db->GetConfigValue($key);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetConfigValue': 
		$key = $_GET['key'];
		$val = $_POST['value'];
		if ($db->GetConfigKey($key)){
			$db->SetConfigValue($key, $val);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetConfigDescription': 
		$key = $_GET['key'];
		$val = $_POST['description'];
		if ($db->GetConfigKey($key)){
			$db->SetConfigDescription($key, $val);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetConfigDescription': 
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$res = $db->GetConfigDescription($key);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetConfigOpt1': 
		$key = $_GET['key'];
		$opt = $_GET['opt'];
		if ($db->GetConfigKey($key)){
			if (preg_match("/^[0-9_]+$/i", $opt)){
				$db->SetConfigOpt1($key, $opt);
				$err = $db->error;
			}else{$err = ERR_InvalidValue; $obj = $opt;}
		}else{$err = ERR_NoneName; $obj = $key;}
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetConfigOpt1': 
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$res = $db->GetConfigOpt1($key);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'SetConfigOpt2': 
		$key = $_GET['key'];
		$val = $_POST['opt'];
		if ($db->GetConfigKey($key)){
			$db->SetConfigOpt2($key, $val);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetConfigOpt2': 
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$res = $db->GetConfigOpt2($key);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		
	case 'SetConfigGroup': 
		$key = $_GET['key'];
		$gr = $_GET['group'];
		if ($db->GetConfigKey($key)){
			if (preg_match("/^[0-9_]+$/i", $gr)){
				$db->SetConfigGroup($key, $gr);
				$err = $db->error;
			}else{$err = ERR_InvalidValue; $obj = $gr;}
		}else{$err = ERR_NoneName; $obj = $key;}
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
		
	case 'GetConfigGroup': 
		$key = $_GET['key'];
		if ($db->GetConfigKey($key)){
			$res = $db->GetConfigGroup($key);
			$err = $db->error;
		}else{$err = ERR_NoneName;}	
		$res = array('err'=>$err, 'obj'=>$key, 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'GetUserName': 
		$query = "SELECT `key` FROM `_cic_config` WHERE `group`=1 AND md5(`key`)=:admin_name";		
		$array = array('admin_name' => $_COOKIE[COOKIE_ADMIN]);
		
		$tab = $db->GetTabFromSQL($query, $array);
		$err = $db->error;
		if ($tab){// если key вернул то:
			$res = $tab[0]->key;
		}else{ 
			$res = "";
		}
		$res = array('err'=>$err, 'obj'=>'admin name', 'res'=>$res); 
		PrintJSON($res);
		break;
	
	case 'setting':
		$res = "";
		$err = "";
		$obj = "";
		// Проверка на правильность имени (ERR_InvalidName)
		if (ValidName($_POST['newname'])){
			// проверка newpassword и newpasswordrep (ERR_PassRep)
			if($_POST['newpassword']==$_POST['newpasswordrep']){
				// проверка username и password (ERR_Password)
				$query = "SELECT `key` FROM `_cic_config` WHERE `group`=1 AND `key`=:admin_name AND `value`=sha(:passw)";
				$array = array('admin_name' => $_POST['username'], 'passw' => $_POST['pass']);
				$tab = $db->GetTabFromSQL($query, $array);
				if ($tab){
					if($_POST['newpassword']==""){
						$pass = $_POST['pass'];
					}else{
						$pass = $_POST['newpassword'];
					}
					$query = "UPDATE `_cic_config` SET `key`=:new_name, `value`=sha(:pass) WHERE `group`=1 AND `key`=:admin_name";
					$array = array('admin_name' => $_POST['username'], 'new_name'=>$_POST['newname'], 'pass' => $pass);
					$tab = $db->pdo->prepare($query)->execute($array);
					//$err = $db->error;
					$res = MSG_SettingSuccess;
				}else{ 
					$err = ERR_Password; 
					$obj = $_POST['pass'];
				}
			}else{
				$err = ERR_PassRep; 
				$obj = $_POST['newpassword'];
			}
		}else{
			$err = ERR_InvalidName; 
			$obj = $_POST['newname'];
		}			
		$res = array('err'=>$err, 'obj'=>$obj, 'res'=>$res); 
		PrintJSON($res);
		break;

	default: break;
}
?>