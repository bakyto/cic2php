<?php


/*
 * Content in container (CIC) ver 2.0
 * @author BAKYTO
 * @copyright 2021-09-28
 
 * все лучшие вещи в жизни живут по ту сторону страха
 
 *
 * ЗАДАЧИ:
 * +tag SQL
 * +tag JSON
 * +tag LOOP, FOREACH
 * +function ReplaceContainer
 * +function GetContainerValue
 * function Condition
 * function Render
 * 
 * добавить чистку памяти по статье https://habr.com/ru/post/134784/
 * добавить error_in_comment = true; ошибку закоментировать <!-- error message -->
 * добавить функцию вывода ошибок:
 ** ERROR in page=index, container=HTTP, line in container=187
 ** Error message
 *
 * все сообщения вытащить на константы или на переменные, чтобы можно было менять язык
 *
 
 */

//****************************************************************************
define('DEFAULT_COND_TYPE', 'php');	// синтаксис условного выражения по умолчанию

//define('CONT_CONT', 'CONT');		// переменные из контейнера
define('CONT_POST', 'POST');		// переменные из метода POST
define('CONT_GET', 'GET');			// переменные из метода GET
define('CONT_COOKIE', 'COOKIE');	// переменные из куки
define('CONT_ENV', 'ENV');			// переменные из переменного окружения
define('CONT_SERVER', 'SERVER');	// информация о сервере и среде исполнения
define('CONT_FILES', 'FILES');		// Переменные файлов, загруженных по HTTP
define('CONT_REQUEST', 'REQUEST');	// Переменные HTTP-запроса 
define('CONT_SESSION', 'SESSION');	// Переменные сессии

define('CONT_MAIN', 'MAIN');		// Имя объязательного основного контейнера страницы

define('TAG_IGNOR', 'cic-tag-ignore');	// Корневой узел XML 

define('TAG_HTTP', 'HTTP-headers');	// cic-тег HTTP-заголовка
define('TAG_FILE', 'file');			// cic-тег подключения файла
define('TAG_CIC', 'cic');			// cic-тег переменные
define('TAG_LOOP', 'loop');			// cic-тег цикла 
define('TAG_FOREACH', 'foreach');	// cic-тег цикла
define('TAG_IF', 'if');				// cic-тег условия
define('TAG_THEN', 'then');			// cic-тег условия +
define('TAG_ELSE', 'else');			// cic-тег условия -

//define('CONT_PATTERN', '/\[\*+[-0-9._\[\]()a-zA-Z]+\*\]/');  //[*container*]
//define('OPEN_VAR', '[*'); 
//define('CLOSE_VAR', '*]'); 

define('CONT_PATTERN', '/\{\{+[-0-9._\[\]()a-zA-Z]+\}\}/');  //{{container}}
define('OPEN_VAR', '{{'); 
define('CLOSE_VAR', '}}');   


/// Проверка тега на cic-тег
function IsCicTag($tag){
	if (strcasecmp($tag, TAG_HTTP) == 0){return true;}
	elseif (strcasecmp($tag, TAG_FILE) == 0){return true;}
	elseif (strcasecmp($tag, TAG_CIC) == 0){return true;}
	elseif (strcasecmp($tag, TAG_LOOP) == 0){return true;}
	elseif (strcasecmp($tag, TAG_FOREACH) == 0){return true;}
	elseif (strcasecmp($tag, TAG_IF) == 0){return true;}
	elseif (strcasecmp($tag, TAG_THEN) == 0){return true;}
	elseif (strcasecmp($tag, TAG_ELSE) == 0){return true;}
	elseif (strcasecmp($tag, TAG_IGNOR) == 0){return true;}
	else {return false;}
}

/// Возвращает оригинал cic-тега
function GetCicTag($tag){
	if (strcasecmp($tag, TAG_HTTP) == 0){return TAG_HTTP;}
	elseif (strcasecmp($tag, TAG_FILE) == 0){return TAG_FILE;}
	elseif (strcasecmp($tag, TAG_CIC) == 0){return TAG_CIC;}
	elseif (strcasecmp($tag, TAG_LOOP) == 0){return TAG_LOOP;}
	elseif (strcasecmp($tag, TAG_FOREACH) == 0){return TAG_FOREACH;}
	elseif (strcasecmp($tag, TAG_IF) == 0){return TAG_IF;}
	elseif (strcasecmp($tag, TAG_THEN) == 0){return TAG_THEN;}
	elseif (strcasecmp($tag, TAG_ELSE) == 0){return TAG_ELSE;}
	elseif (strcasecmp($tag, TAG_IGNOR) == 0){return TAG_IGNOR;}
	else {return false;}
}


// innerHTML in PHP DOM
function DOMinnerHTML ($element){
    $innerHTML = "";
    $children = $element->childNodes;
    foreach ($children as $child){
        $innerHTML = $innerHTML.($child->ownerDocument->saveXML($child));
    }
    return $innerHTML;
}

// setInnerHTML from text to element
function setInnerHTML($element, $html)
{
    $fragment = $element->ownerDocument->createDocumentFragment();
    $fragment->appendXML($html);
    while ($element->hasChildNodes())
        $element->removeChild($element->firstChild);
    $element->appendChild($fragment);
}

// передача заголовок Http-header 
function SetHttpHeaders($headers){
	$hh = explode("\n", $headers); 
	foreach($hh as $h){
		if ($h!="") {
			$h = XmlSymbolDecode($h); // Раскодировать спец символы XML 
			header($h, false); // если передать false, можно задать несколько однотипных заголовков.
		}
	}
}

/* Экранировать символы xml  
"   &quot;
'   &apos;
<   &lt;
>   &gt;
&   &amp;
*/
function XmlSymbolEncode($res){ // закодировать
	$res = str_replace('"', "&quot;", $res);
	$res = str_replace("'", "&apos;", $res);
	$res = str_replace("<", "&lt;", $res);
	$res = str_replace(">", "&gt;", $res);
	$res = str_replace("&", "&amp;", $res);
	return $res;
}
function XmlSymbolDecode($res){ // раскодировать
	$res = str_replace("&quot;", '"', $res);
	$res = str_replace("&apos;", "'", $res);
	$res = str_replace("&lt;", "<", $res);
	$res = str_replace("&gt;", ">", $res);
	$res = str_replace("&amp;", "&", $res);
	return $res;
}
function XmlSymbolEscape($value){ //расодировать и закодировать символ &
	$res = str_replace("&amp;", "&", $value);
	$res = str_replace("&", "&amp;", $res);
	return $res;
}

/// Класс одного узла в формате xml
class CicNodeXml
{ 
	var $container_name;
	var $node;
	var $vars;
	var $dbm;
	
	function GetVars(){
		return $this->vars;
	}
	
	function Condition($condition, $type){ ///RAW///RAW///RAW///RAW///RAW///RAW///RAW///RAW///RAW
		$condition = $this->ReplaceContainer($condition);
		if($type == "php"){ // если php
			try{
				$res = eval("return ($condition);"); // выполнить php код
			}catch (ParseError $e){
				$res = $e->getMessage(); // передать текст ошибки
			}
		/*
		}elseif($type == "js" || $type == "java"){ // если js 
			$v8 = new V8Js();
			try{
				$v8->executeString("return ($condition);");
			}catch (V8JsException $e) {
				$res = $e.message; // передать текс ошибки
			}
			
			//$res
		*/
		}else{
			$res = "Неизвестный тип <b>" . $type . "</b> in Page: <b>$_GET[p]</b>, container: <b>$this->container_name</b>.<br/>";
		}
		if (!$res) {
			return array(0, "");
		}elseif($res == 1){
			return array(1, "");
		}else{
			return array(-1, "Ошибка в логическом выражиении\n" . $res);
		}
		
	}
	
	// Возвращает зачение cic переменного
	function GetCicVar($codevar){
		$buff = explode(".", $codevar);
		$cic_var_key = $buff[0];
		$buff = explode("[", $cic_var_key);
		$cic_var_key = $buff[0]; // отсечь до знака [
		$$cic_var_key = $this->vars[$cic_var_key];
		$codevar = str_replace(".", "->", $codevar);
		
		$res = "";
		eval('$res = $'.$codevar.';');
		/*
		set_exception_handler(function($_errno, $errstr) {
			// Convert notice, warning, etc. to error.
			throw new Error($errstr);
		});
		try {
			eval('$res = $'.$codevar.';');
		} catch (Throwable $e) {
			$res = "";//OPEN_VAR . $codevar . CLOSE_VAR; // если не определена переменная, то пишем как есть
			//echo $e; // Error: Undefined variable: tw...
		}
		*/
		return $res;
	}
	
	// значение контейнера, например [*GET.id*]
	function GetContainerValue($container){ 
		$buff = substr($container, 2, strlen($container) - 4);
		$keys = explode(".", $buff);
		$key = substr($buff, strlen($keys[0])+1);
		switch (strtoupper($keys[0])) {
			case CONT_GET:     $value = $_GET["$key"]; break; 
			case CONT_POST:    $value = $_POST["$key"]; break; 
			case CONT_COOKIE:  $value = $_COOKIE["$key"]; break;
			case CONT_SERVER:  $value = $_SERVER["$key"]; break;
			case CONT_ENV:     $value = $_ENV["$key"]; break;
			case CONT_FILES:   $value = $_FILES["$key"]; break;
			case CONT_REQUEST: $value = $_REQUEST["$key"]; break;
			case CONT_SESSION: $value = $_SESSION["$key"]; break;
			
			case CONT_CONT:   $value = $this->dbm->GetContainerContent($_GET['p'], $key); break;
			default:
				
				if ((count($keys)==1) and (!stripos($keys[0], '['))){ // Значит это контейнер
					//print_r($this->dbm);
					$value = $this->dbm->GetContainerContent($_GET['p'], $keys[0]); 
					$doc = new DOMDocument(); // Создаем новый ДОМ дерево пустое
					$value = XmlSymbolEscape($value);
					if ($doc->loadXML("<" . TAG_IGNOR . ">" . $value. "</" . TAG_IGNOR . ">")){ // заполняем ДОМ. Так как документ XML требует корневой узел, помещаем во временнй тег
						$cic = new CicNodeXml;
						$res = $cic->Exect($doc->documentElement, $keys[0], null, $this->dbm);
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
					$value = $res;
					
				}else{ // Значит это cic переменное
					$value = $this->GetCicVar($buff);
				}
				break;
		}
		return $value;
	}
	
	/// Замена всех типов контейнеров 
	function ReplaceContainer($str){ 
		preg_match_all(CONT_PATTERN, $str, $out, PREG_OFFSET_CAPTURE); 
		foreach ($out[0] as $container){
			$value = $this->GetContainerValue($container[0]);
			
			$str = str_replace($container[0], $value, $str);
		}
		return ($str);
	}
	/// возвреащет массив библиотек из sql запроса (ВЫТАЩИТЬ НА ОТДЕЛЬНЫЙ ФАЙЛ)
	function GetVarFromSql($sql_cont, $sql_type){ 
		preg_match_all(CONT_PATTERN, $sql_cont, $out, PREG_OFFSET_CAPTURE);
		$start = 0;
		$pdo_query = $sql_cont;
		$pdo_array = array();
		foreach ($out[0] as $val){
			$key = str_replace(".", "_", substr($val[0], 2, -2));
			$pdo_query = str_replace($val[0], ":".$key, $pdo_query);
			$key_val = $this->GetContainerValue($val[0]);
			$pdo_array += array($key => $key_val);
		}
		$db = new DataBasePDO($sql_type);
		$tab = $db->GetTabFromSQL($pdo_query, $pdo_array);
		return array($tab, $db->error);
	}
	
	// Создает кик переменное из кода (ВЫТАЩИТЬ НА ОТДЕЛЬНЫЙ ФАЙЛ)
	function CreateCicVar($codetype, $code){
		$tab[1] = ""; //error message
		$code = XmlSymbolDecode($code);
		$code1 = $this->ReplaceContainer($code);
		switch($codetype){
			case "json": 
				$tab[0] = json_decode($code1, true);
				//print_r($tab[0]); exit();
				break;
			case "xml": 
				$tab[0] = simplexml_load_string($code1);
				break;
			case "sql": $tab = $this->GetVarFromSql($code1, DBMS_DEFAULT); break;
			case "sqlite": // SQLite
			case "pgsql": // PostgreSQL
			case "sybase": // Sybase
			case "mssql": // MS SQL
			case "mysql": // MySQL
				//print_r($tab[0]);
				$tab = $this->GetVarFromSql($code1, $codetype);
				break;
		}
		return $tab;
	}
	/// Функция выполняет cic-теги, и возвращает html
	/// ---------------------------------------------
	function Exect($innode, $incontname, $invars, $dbm){ 
		$this->node = $innode;
		$this->container_name = $incontname;
		$this->vars = $invars;
		$this->dbm = $dbm;
		$res = '';
		// проверка $this->node на содержание cic-тегов, если нет, то просто отрисовка с учетом переменных (контейнеров)
		// -------------------------------------------------------------------------------------------------------------
		$cichas = false;
		foreach ($this->node->getElementsByTagName('*') as $node) {
			if ($node->textContent == "" & $node->tagName=="div"){$node->textContent=" ";} // Если пустой div-тег то добавляем пробел. Исправить RAW RAW RAW RAW RAW RAW RAW 
			if(GetCicTag($node->tagName)){$cichas = true;}
		}
		
		if (!$cichas){// если нет кик-теги то:
			$res = $this->ReplaceContainer($this->node->ownerDocument->saveXML($this->node)); // вставка переменных(контейнеров)
			// ---------- Убрать тег если cic-тег -----------
			if (IsCicTag($this->node->tagName)){// если тег cic-тег
				$tdoc = new DOMDocument(); // Создаем временный ДОМ дерево пустое чтобы отсечь тег
				$res = XmlSymbolEscape($res); // обработать знак &
				if ($tdoc->loadXML($res)){ // заполняем ДОМ
					$res = DOMinnerHTML($tdoc->documentElement); // берем внутренний html
					//print_r($res); exit;
					//$cic = new CicNodeXml; // создаем кик-узел
					//$res = $res . $cic->Exect($tdoc->documentElement, $this->container_name, $this->vars, $dbm); // передаем содержание в кик-узел и выполним
				}else{
					foreach (libxml_get_errors() as $error) {///RAW///RAW///RAW///RAW///RAW///RAW///RAW
						$res = $res.$error->message.". in Page: <b>$_GET[p]</b>, container: <b>$this->container_name</b>.<br/>";
						// Допускаются следующие ошибки (по возможности обработать здесь): 
						//[code] => 64; [message] => XML declaration allowed only at the start of the document. <?xml> - заголовок xml должен быть в начале документа
						//[code] => 68; [message] => StartTag: invalid element name. <!DOCTYPE html> -как то надо разобраться с этим
						//print_r($error);
					}
					libxml_clear_errors();
				}
			}
			// ---------------------------------------------
			return $res;
		}
		// --------------------------------------------------------------------------------------------------------------
		
		
		// ------ Отрисовка тега и аттрибутов кроме кик-тегов --------
		if (!IsCicTag($this->node->tagName)){// если не кик-тег, то:
			$attrib = "";
			foreach ($this->node->attributes as $attr){ // берем каждый атрибут
				$attrib = $attrib . $attr->nodeName . '="' . $attr->nodeValue . '" '; // собираем (в значениях аттрибута должны использоваться одинарные ковычки
			}
			$tn = $this->node->tagName; // берем название тега
			$res = $res . "<$tn $attrib>"; // напишем тег и атрибуты
			$res = $this->ReplaceContainer($res);
		}
		// -----------------------------------------------------------
		foreach ($this->node->childNodes as $node) {
			switch (get_class($node)) {
				case "DOMElement":  // 
					switch (GetCicTag($node->tagName)){
						
						case TAG_HTTP: 
							// http-header тег должен быть в основном контейнере и в начале без предыдущих пробелов и пустых строк, иначе игнорируется
							//if(($this->container_name == CONT_MAIN)&&($node->parentNode->tagName == TAG_IGNOR)&&(!$node->previousSibling)){
								
							// http-header тег должен быть в основном контейнере иначе игнорируется
							if(($this->container_name == CONT_MAIN)){
								$cic = new CicNodeXml; // создаем кик-узел
								$header = $cic->Exect($node, $this->container_name, $this->vars, $dbm); // выполняем кик-узел
								if ($header!==""){
									$tdoc = new DOMDocument(); // Создаем временный ДОМ дерево пустое чтобы отсечь тег
									$header = XmlSymbolEscape($header);
									if ($tdoc->loadXML("<" . TAG_IGNOR . ">" . $header. "</" . TAG_IGNOR . ">")){ // заполняем ДОМ
										$header = DOMinnerHTML($tdoc->documentElement); // берем внутренний html
										SetHttpHeaders($header); // в других cgi-приложениях нужно добавить в конце две пустые строки: ($header."\n\n")
									}else{
										foreach (libxml_get_errors() as $error) {/// Если возникнет ошибка при объявления заголовка то выводится до тега <Html>
											$res = $res.$error->message.". in Page: <b>$_GET[p]</b>, container: <b>$this->container_name</b>111111.<br/>";
										}
										libxml_clear_errors();
									}

								}
							}
							break;
							
						case TAG_FILE: 
							$filename = $node->getAttribute('src'); // имя файла берем из атрибута src
							$filetype = $node->getAttribute('type'); // тип файла
							if (!$filename){$filename = DOMinnerHTML($node);} // если не нашел в атрибутах то берем из контента
							
							if ($filename){ // если есть имя файла, то:
								if ($filetype==="php"){ // если тип php то подключить
									include($filename);
									break;
								}
								$doc = new DOMDocument(); // Создаем новый ДОМ дерево пустое
								
								$content = file_get_contents($filename);// получаем содержимое файла
								$content = XmlSymbolEscape($content);
								if ($doc->loadXML($content)){ // заполняем ДОМ
								//if ($doc->load($filename)){ // заполняем ДОМ из файла
									$cic = new CicNodeXml; // создаем кик-узел
									$res = $res . $cic->Exect($doc->documentElement, $this->container_name, $this->vars, $dbm); // передаем содержание файла в кик-узел и выполним
								}else{ // разберем ошибку
									$res = $res . "<br/>Error in File <b>'$filename'</b> in Page: <b>$_GET[p]</b>, container: <b>$this->container_name</b>.<br/>";
									foreach (libxml_get_errors() as $error) {
										$res = $res . $error->message . "<br/>";
									}
									libxml_clear_errors();
								}	
							}
							break;
						
						case TAG_CIC:
							$codefile = $node->getAttribute('src'); // ссылка на файл с кодом
							$codetype = $node->getAttribute('type'); // тип исходного кода
							$codevar = $node->getAttribute('key'); // название переменных
							$code = ""; // сам sql,json код
							if ($codefile){ // если указан аттрибут файл, то:
								$context = stream_context_create(array('http' => array('ignore_errors' => true),)); // отключаем ошибку
								$code = file_get_contents($codefile, false, $context); // считываем файл
								if (!$code){$code = $node->textContent;}; // если не удалось получить из файла то берем текст внутри тега
							}else{
								$code = $node->textContent; // если не указан файл, то берем текст внутри тега
							}
							$tab = $this->CreateCicVar($codetype, $code);
							if ($tab[1]==""){
								if($codevar==""){
									$this->vars[$codetype] = $tab[0];
								}else{
									$this->vars[$codevar] = $tab[0];
								}
							}else{// Если ошибка сообщаем об этом
								echo($tab[1]); ///RAW///RAW///RAW///RAW///RAW///RAW
							}
							break;
							
						case TAG_LOOP:  // если тег loop или foreach 
						case TAG_FOREACH:
							$codevar = $node->getAttribute('key'); // название cic переменных 
							$loopvar = $this->GetCicVar($codevar); // cic переменное по ключу key
							$i = 0;
							$innerHtml = DOMinnerHTML($node);
							while ($i < count($loopvar)){// проход по массиву loopvar
								$innerHtmlWithIndex = str_replace(OPEN_VAR.$codevar, OPEN_VAR.$codevar.'['.$i.']', $innerHtml); // найти в содержании innerHtml $codevar заменить на '$codevar['.$i.']'
								setInnerHTML($node, $innerHtmlWithIndex);
								$cic = new CicNodeXml; // создаем кик-узел
								$res= $res . $cic->Exect($node, $this->container_name, $this->vars, $dbm); // выполняем кик-узел
								$i = $i + 1;
							}
							break;
						case TAG_IF: // Условие
							$type = $node->getAttribute('type'); // тип кода в условии (php, java, js)
							if (!$type or $type == ""){ // если аттрибут type пуст или не указан то:
								$type = DEFAULT_COND_TYPE;
							}
							$condition = $node->getAttribute('cond'); // логическое выражение
							$cond_res = $this->Condition($condition, $type); // получаем результат в виде массива (bool, error_msg)
							
							foreach ($node->childNodes as $ifnode) { // проход по узлам внутри тега if
								if ($ifnode->tagName == TAG_THEN){ // если встречаются теги then то 
									if ($cond_res[0] == 1){
										$cic = new CicNodeXml; // создаем кик-узел
										$res= $res . $cic->Exect($ifnode, $this->container_name, $this->vars, $dbm); // выполняем кик-узел
									}
								}elseif ($ifnode->tagName == TAG_ELSE){ // если встречаются теги else то
									if ($cond_res[0] == 0){
										$cic = new CicNodeXml; // создаем кик-узел
										$res= $res . $cic->Exect($ifnode, $this->container_name, $this->vars, $dbm); // выполняем кик-узел
									}
								}else {// если встречаются другие теги то обработать здесь, но лучше игнорировать
									
								}
							}
							break;
						/* теги then и else игнорируется так как обрабатываются внутри if */
						case TAG_THEN: break;
						case TAG_ELSE: break; 
						
						default:
							$cic = new CicNodeXml; // создаем кик-узел
							$res = $res . $cic->Exect($node, $this->container_name, $this->vars, $dbm); // выполняем кик-узел
							break;
					}
					break;
					
				case "DOMText": // если предыдущий родственник кик-тег и первый символ текста \n, то убрать \n, иначе написать текст как есть
					if ((IsCicTag($node->previousSibling->tagName))&&($node->textContent[0]=="\n")) {
						$node->textContent = substr($node->textContent, 1);
					}
					$res = $res . $this->ReplaceContainer($node->textContent); // заменить все переменные
					break;
					
                case "DOMComment": // нарисуем комментарий
					$res = $res . $node->ownerDocument->saveXML($node);
					break;
					
				default: // если появяться новые DOM классы то добавить обработку выше
					$res = $res . "<!-- WARNING: New DOM Class: ". get_class($node) . "-->"; 
					break; 
				}
			}
		// Отрисовка закрывающего тега кроеме кик-тега
		if (!IsCicTag($this->node->tagName)){
			$tn = $this->node->tagName;
			$res = $res . "</$tn>";
		}
			// -------------------------------------------
		/// -----------------------------------------------------------
		return $res;
	}
}

?>