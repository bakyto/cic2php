<?php

include_once("cic/main.php");
include_once("cic/tools.php");

// Render of container
function Render(){///RAW///RAW///RAW///RAW///RAW///RAW///RAW
	$container_name = CONT_MAIN;
	$res = '';
	$page_name = $_GET["p"];
	$dbm = new DataBasePDO(null);
	
	$content = $dbm->GetContainerContent($page_name, $container_name);
	$doc = new DOMDocument(); // Создаем новый ДОМ дерево пустое
	$content = str_replace("&amp;", "&", $content);
	$content = str_replace("&", "&amp;", $content);
	if ($doc->loadXML("<" . TAG_IGNOR . ">" . $content. "</" . TAG_IGNOR . ">")){ // заполняем ДОМ. Так как документ XML требует корневой узел, помещаем во временнй тег
		$cic = new CicNodeXml;
		$res = $cic->Exect($doc->documentElement, $container_name, null, $dbm);
	}else{
		foreach (libxml_get_errors() as $error) {///RAW///RAW///RAW///RAW///RAW///RAW///RAW
			$res = $res.$error->message.". in page: <b>$_GET[p]</b>, container: <b>$container_name</b>.<br/>"; //." in Entity, line: " . $error->line . "<br/>");
			// Допускаются следующие ошибки (по возможности обработать здесь): 
			//[code] => 64; [message] => XML declaration allowed only at the start of the document. <?xml> - заголовок xml должен быть в начале документа
			//[code] => 68; [message] => StartTag: invalid element name. <!DOCTYPE html> -как то надо разобраться с этим
			//print_r($error);
		}
		libxml_clear_errors();
	}
	return $res;
}



//---- счетчик времени ----> 
$starttime = microtime(true);
$memory = memory_get_usage();
//<--- счетчик времени ---- 

// включение обработки ошибок пользователем
libxml_use_internal_errors(true);

// перенаправление в случае попытки открыть файл напрямую
if ($_SERVER['QUERY_STRING']=='') {
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/?p=index'); 
    exit();
}else{
	// Render mine container of page
	$res = Render();
	$res = str_replace("&amp;", "&", $res);
	echo($res);
}
//---- счетчик времени ----> 
$memory = memory_get_usage() - $memory;
$name = array('byte', 'Kb', 'Mb');
$i = 0;
while (floor($memory / 1024) > 0) {
 $i++;
 $memory /= 1024;
}
//echo "\n\n";
//echo '<!-- Script execution time: '.round(microtime(true) - $starttime, 4)." sec. -->\n";
//echo '<!-- Wasted memory: ' . round($memory, 2) . ' ' . $name[$i] . ' -->';
//<--- счетчик времени ---- 

?>
