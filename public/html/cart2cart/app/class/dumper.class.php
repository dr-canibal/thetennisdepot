<?php
/***************************************************************************\
| Sypex Dumper Lite          version 1.0.8b                                 |
| (c)2003-2006 zapimir       zapimir@zapimir.net       http://sypex.net/    |
| (c)2005-2006 BINOVATOR     info@sypex.net                                 |
|---------------------------------------------------------------------------|
|     created: 2003.09.02 19:07              modified: 2006.10.27 03:30     |
|---------------------------------------------------------------------------|
| This program is free software; you can redistribute it and/or             |
| modify it under the terms of the GNU General Public License               |
| as published by the Free Software Foundation; either version 2            |
| of the License, or (at your option) any later version.                    |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA. |
\***************************************************************************/

// Ограничение размера данных доставаемых за одно обращения к БД (в мегабайтах)
// Нужно для ограничения количества памяти пожираемой сервером при дампе очень объемных таблиц
define('LIMIT', 1);
// Кодировка соединения с MySQL
// auto - автоматический выбор (устанавливается кодировка таблицы), cp1251 - windows-1251, и т.п.
define('CHARSET', 'auto');
// Кодировка соединения с MySQL при восстановлении
// На случай переноса со старых версий MySQL (до 4.1), у которых не указана кодировка таблиц в дампе
// При добавлении 'forced->', к примеру 'forced->cp1251', кодировка таблиц при восстановлении будет принудительно заменена на cp1251
// Можно также указывать сравнение нужное к примеру 'cp1251_ukrainian_ci' или 'forced->cp1251_ukrainian_ci'
define('RESTORE_CHARSET', 'cp1251');
// Типы таблиц у которых сохраняется только структура, разделенные запятой
define('ONLY_CREATE', 'MRG_MyISAM,MERGE,HEAP,MEMORY');

// Дальше ничего редактировать не нужно

class dumper 
{
	var $_link = null;
	var $action = null;
	var $databaseName = null;
	var $compMethod = null;
	var $compLevel = null;
	
	var $path = "";
	var $filename = "";
	
	function dumper($link, $databaseName) 
	{
		$this->_link = $link;
    $this->databaseName = $databaseName;
		
		$this->SET['tables'] = '';
		$this->SET['comp_method'] = 2;
		$this->SET['comp_level']  = 7;
		$this->SET['last_db_restore'] = '';
		
		$this->tabs = 0;
		$this->records = 0;
		$this->size = 0;
		$this->comp = 0;

		// Версия MySQL вида 40101
		preg_match("/^(\d+)\.(\d+)\.(\d+)/", mysql_get_server_info($this->_link), $m);
		$this->mysql_version = sprintf("%d%02d%02d", $m[1], $m[2], $m[3]);

		$this->only_create = explode(',', ONLY_CREATE);
		$this->forced_charset  = false;
		$this->restore_charset = $this->restore_collate = '';
		if (preg_match("/^(forced->)?(([a-z0-9]+)(\_\w+)?)$/", RESTORE_CHARSET, $matches)) {
			$this->forced_charset  = $matches[1] == 'forced->';
			$this->restore_charset = $matches[3];
			$this->restore_collate = !empty($matches[4]) ? ' COLLATE ' . $matches[2] : '';
		}
	}
	
	function backup() 
  {
		set_error_handler("SXD_errorHandler");

		$this->SET['tables_exclude']  = !empty($_POST['tables']) && $_POST['tables']{0} == '^' ? 1 : 0;
		$this->SET['tables']          = isset($_POST['tables']) ? $_POST['tables'] : '';
		$this->SET['comp_method']     = $this->compMethod;
		$this->SET['comp_level']      = $this->compLevel;
		$this->SET['tables']          = explode(",", $this->SET['tables']);
		
		if (!empty($_POST['tables'])) {
      foreach($this->SET['tables'] AS $table){
        $table = preg_replace("/[^\w*?^]/", "", $table);
				$pattern = array( "/\?/", "/\*/");
				$replace = array( ".", ".*?");
				$tbls[] = preg_replace($pattern, $replace, $table);
      }
		} else {
			$this->SET['tables_exclude'] = 1;
		}

		if ($this->SET['comp_level'] == 0) {
		  $this->SET['comp_method'] = 0;
		}
		
		//$db = $this->SET['last_db_backup'];
		$db = $this->databaseName;

		$tables = array();
    $result = mysql_query("SHOW TABLES", $this->_link);
		$all = 0;

		while($row = mysql_fetch_array($result)) {
			$status = 0;
			if (!empty($tbls)) {
		    foreach($tbls AS $table) {
   				$exclude = preg_match("/^\^/", $table) ? true : false;
   				if (!$exclude) {
   					if (preg_match("/^{$table}$/i", $row[0])) {
   					  $status = 1;
   					}
   					$all = 1;
   				}
   				if ($exclude && preg_match("/{$table}$/i", $row[0])) {
   				  $status = -1;
   				}
   			}
			} else {
				$status = 1;
			}
			if ($status >= $all) {
    			$tables[] = $row[0];
    	}
    }

		$tabs = count($tables);
		// Определение размеров таблиц
		$result = mysql_query("SHOW TABLE STATUS", $this->_link);
		$tabinfo = array();
		$tab_charset = array();
		$tab_type = array();
		$tabinfo[0] = 0;
		$info = '';
		while($item = mysql_fetch_assoc($result)){
			//print_r($item);
			if(in_array($item['Name'], $tables)) {
				$item['Rows'] = empty($item['Rows']) ? 0 : $item['Rows'];
				$tabinfo[0] += $item['Rows'];
				$tabinfo[$item['Name']] = $item['Rows'];
				$this->size += $item['Data_length'];
				$tabsize[$item['Name']] = 1 + round(LIMIT * 1048576 / ($item['Avg_row_length'] + 1));
				if($item['Rows']) $info .= "|" . $item['Rows'];
				if (!empty($item['Collation']) && preg_match("/^([a-z0-9]+)_/i", $item['Collation'], $m)) {
					$tab_charset[$item['Name']] = $m[1];
				}
				$tab_type[$item['Name']] = isset($item['Engine']) ? $item['Engine'] : $item['Type'];
			}
		}
		$show = 10 + $tabinfo[0] / 50;
		$info = $tabinfo[0] . $info;
		$name = $db . '_' . date("Y-m-d_H-i-s");
    $fp = $this->fn_open(dirname(__FILE__) . "/../../dump/" . $name, "w");

		$this->fn_write($fp, "#SKD101|{$db}|{$tabs}|" . date("Y.m.d H:i:s") ."|{$info}\n\n");
		$t=0;
		
		$this->fn_write($fp, 'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;' . "\n\n");
    $this->fn_write($fp, "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" . "\n\n");

		$result = mysql_query("SET SQL_QUOTE_SHOW_CREATE = 1", $this->_link);
		// Кодировка соединения по умолчанию
		if ($this->mysql_version > 40101 && CHARSET != 'auto') {
			mysql_query("SET NAMES '" . CHARSET . "'", $this->_link) or trigger_error ("Неудается изменить кодировку соединения.<BR>" . mysql_error($this->_link), E_USER_ERROR);
			$last_charset = CHARSET;
		} else {
			$last_charset = '';
		}
    
		foreach ($tables AS $table){
			// Выставляем кодировку соединения соответствующую кодировке таблицы
			if ($this->mysql_version > 40101 && $tab_charset[$table] != $last_charset) {
				if (CHARSET == 'auto') {
					mysql_query("SET NAMES '" . $tab_charset[$table] . "'", $this->_link) or trigger_error ("Неудается изменить кодировку соединения.<BR>" . mysql_error($this->_link), E_USER_ERROR);
					$last_charset = $tab_charset[$table];
				} else {
		//			echo ('Кодировка соединения и таблицы не совпадает:');
    //			echo ('Таблица `'. $table .'` -> ' . $tab_charset[$table] . ' (соединение '  . CHARSET . ')');
				}
			}
	//		echo tpl_l("Обработка таблицы `{$table}` [" . fn_int($tabinfo[$table]) . "].");
        	// Создание таблицы
			$result = mysql_query("SHOW CREATE TABLE `{$table}`", $this->_link);
      $tab = mysql_fetch_array($result);
			$tab = preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|COLLATE=\w+|character set \w+|collate \w+)/i', '/*!40101 \\1 */', $tab);
      $this->fn_write($fp, "DROP TABLE IF EXISTS `{$table}`;\n{$tab[1]};\n\n");

      // Проверяем нужно ли дампить данные
      if (in_array($tab_type[$table], $this->only_create)) {
			  continue;
      }
        	
      // Опредеделяем типы столбцов
      $NumericColumn = array();
      $result = mysql_query("SHOW COLUMNS FROM `{$table}`", $this->_link);
      $field = 0;
      while($col = mysql_fetch_row($result)) {
        $NumericColumn[$field++] = preg_match("/^(\w*int|year)/", $col[1]) ? 1 : 0;
      }
			$fields = $field;
      $from = 0;
			$limit = $tabsize[$table];
			$limit2 = round($limit / 3);
			
		  if ($tabinfo[$table] > 0) { 
				$i = 0;
				$this->fn_write($fp, "INSERT INTO `{$table}` VALUES");
	      
	      while(($result = mysql_query("SELECT * FROM `{$table}` LIMIT {$from}, {$limit}", $this->_link)) && ($total = mysql_num_rows($result))){
	        while($row = mysql_fetch_row($result)) {
	          $i++;
	          $t++;
	
	          for($k = 0; $k < $fields; $k++){
	            if ($NumericColumn[$k])
	              $row[$k] = isset($row[$k]) ? $row[$k] : "NULL";
	            else
	              $row[$k] = isset($row[$k]) ? "'" . $this->escape($row[$k]) . "'" : "NULL";
	          }
	
	          $this->fn_write($fp, ($i == 1 ? "" : ",") . "\n(" . implode(", ", $row) . ")");
	        }
	        
	        mysql_free_result($result);
	
	        if ($total < $limit) {
	          break;
	        }
	    		$from += $limit;
	      }
			  $this->fn_write($fp, ";\n\n");
	    }
		}
		$this->tabs = $tabs;
		$this->records = $tabinfo[0];
		$this->comp = $this->SET['comp_method'] * 10 + $this->SET['comp_level'];
		
		$this->fn_write($fp, "SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'');\n\n");
    $this->fn_write($fp, "SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS,0);\n\n");
		
    $this->fn_close($fp);
		$filesize = round(filesize($this->path . DIRECTORY_SEPARATOR . $this->filename) / 1048576, 2) . " МБ";

    return $this->filename;
	}
	
  function restore($file)
  {
    set_error_handler("SXD_errorHandler");
    $db = $this->databaseName;
    $this->filename = $file;
    // Определение формата файла
    if(preg_match("/^(.+?)\.sql(\.(bz2|gz))?$/", $file, $matches)) {
      if (isset($matches[3]) && $matches[3] == 'bz2') {
          $this->SET['comp_method'] = 2;
      } elseif (isset($matches[2]) && $matches[3] == 'gz') {
        $this->SET['comp_method'] = 1;
      } else {
        $this->SET['comp_method'] = 0;
      }
      $this->SET['comp_level'] = '';
      if (!file_exists($file)) {
            echo ("ОШИБКА! Файл не найден!");
            exit;
      }
      echo ("\tReading dump file: {$file}\n");
      $file = $matches[1];
    } else {
      echo ("ОШИБКА! Не выбран файл!");
      exit;
    }

    $fp = $this->fn_open($file, "r");
    $this->file_cache = $sql = $table = $insert = '';
        $is_skd = $query_len = $execute = $q =$t = $i = $aff_rows = 0;
    $limit = 300;
        $index = 4;
    $tabs = 0;
    $cache = '';
    $info = array();

    // Установка кодировки соединения
    if ($this->mysql_version > 40101 && (CHARSET != 'auto' || $this->forced_charset)) { // Кодировка по умолчанию, если в дампе не указана кодировка
      mysql_query("SET NAMES '" . $this->restore_charset . "'", $this->_link) or trigger_error ("Неудается изменить кодировку соединения.<BR>" . mysql_error($this->_link), E_USER_ERROR);
      echo ("Установлена кодировка соединения `" . $this->restore_charset . "`.\n");
      $last_charset = $this->restore_charset;
    } else {
      $last_charset = '';
    }
    
    $last_showed = '';
    
    while(($str = $this->fn_read_str($fp)) !== false) {
      if (empty($str) || preg_match("/^(#|--)/", $str)) {
        if (!$is_skd && preg_match("/^#SKD101\|/", $str)) {
            $info = explode("|", $str);
          //echo tpl_s(0, $t / $info[4]);
          $is_skd = 1;
        }
        continue;
      }
      
      $query_len += strlen($str);

      if (!$insert && preg_match("/^(INSERT INTO `?([^` ]+)`? .*?VALUES)(.*)$/i", $str, $m)) {
        if ($table != $m[2]) {
          $table = $m[2];
          $tabs++;
          $last_showed = $table;
          $i = 0;
        }
        $insert = $m[1] . ' ';
        $sql .= $m[3];
        $index++;
        $info[$index] = isset($info[$index]) ? $info[$index] : 0;
        $limit = round($info[$index] / 20);
        $limit = $limit < 300 ? 300 : $limit;
      } else {
        $sql .= $str;
        if ($insert) {
          $i++;
          $t++;
        }
      }

      if (!$insert && preg_match("/^CREATE TABLE (IF NOT EXISTS )?`?([^` ]+)`?/i", $str, $m) && $table != $m[2]){
        $table = $m[2];
        $insert = '';
        $tabs++;
        $is_create = true;
        $i = 0;
      }
      if ($sql) {
        if (preg_match("/;$/", $str)) {
          $sql = rtrim($insert . $sql, ";");
          if (empty($insert)) {
            if ($this->mysql_version < 40101) {
                $sql = preg_replace("/ENGINE\s?=/", "TYPE=", $sql);
            } elseif (preg_match("/CREATE TABLE/i", $sql)) {
              // Выставляем кодировку соединения
              if (preg_match("/(CHARACTER SET|CHARSET)[=\s]+(\w+)/i", $sql, $charset)) {
                if (!$this->forced_charset && $charset[2] != $last_charset) {
                  if (CHARSET == 'auto') {
                    mysql_query("SET NAMES '" . $charset[2] . "'", $this->_link) or trigger_error ("Неудается изменить кодировку соединения.<BR>{$sql}<BR>" . mysql_error($this->_link), E_USER_ERROR);
                    //$cache .= ("Установлена кодировка соединения `" . $charset[2] . "`.");
                    $last_charset = $charset[2];
                  } else {
                    /*$cache .= ('Кодировка соединения и таблицы не совпадает:');
                    $cache .= ('Таблица `'. $table .'` -> ' . $charset[2] . ' (соединение '  . $this->restore_charset . ')');*/
                  }
                }
                // Меняем кодировку если указано форсировать кодировку
                if ($this->forced_charset) {
                  $sql = preg_replace("/(\/\*!\d+\s)?((COLLATE)[=\s]+)\w+(\s+\*\/)?/i", '', $sql);
                  $sql = preg_replace("/((CHARACTER SET|CHARSET)[=\s]+)\w+/i", "\\1" . $this->restore_charset . $this->restore_collate, $sql);
                }
              } elseif (CHARSET == 'auto') { // Вставляем кодировку для таблиц, если она не указана и установлена auto кодировка
                $sql .= ' DEFAULT CHARSET=' . $this->restore_charset . $this->restore_collate;
                if ($this->restore_charset != $last_charset) {
                  mysql_query("SET NAMES '" . $this->restore_charset . "'", $this->_link) or trigger_error ("Неудается изменить кодировку соединения.<BR>{$sql}<BR>" . mysql_error($this->_link), E_USER_ERROR);
                  //$cache .= ("Установлена кодировка соединения `" . $this->restore_charset . "`.");
                  $last_charset = $this->restore_charset;
                }
              }
            }
          } elseif ($this->mysql_version > 40101 && empty($last_charset)) { // Устанавливаем кодировку на случай если отсутствует CREATE TABLE
            mysql_query("SET $this->restore_charset '" . $this->restore_charset . "'") or trigger_error ("Неудается изменить кодировку соединения.<BR>{$sql}<BR>" . mysql_error($this->_link), E_USER_ERROR);
            //echo tpl_l("Установлена кодировка соединения `" . $this->restore_charset . "`.");
            $last_charset = $this->restore_charset;
          }

          $insert = '';
          $execute = 1;
        }
        
        if ($query_len >= 65536 && preg_match("/,$/", $str)) {
          $sql = rtrim($insert . $sql, ",");
          $execute = 1;
        }
        
        if ($execute) {
          $q++;
          mysql_query($sql, $this->_link) or trigger_error ("SQL Error, query was: $sql\n" . mysql_error($this->_link), E_USER_ERROR);
          if (preg_match("/^insert/i", $sql)) {
            $aff_rows += mysql_affected_rows($this->_link);
          }
          $sql = '';
          $query_len = 0;
          $execute = 0;
        }
      }
    }
    echo ("\t\tTables Created: {$tabs}\n");
    echo ("\t\tRows added: {$aff_rows}\n");

    $this->tabs = $tabs;
    $this->records = $aff_rows;
    $this->size = filesize($this->path . DIRECTORY_SEPARATOR . $this->filename);
    $this->comp = $this->SET['comp_method'] * 10 + $this->SET['comp_level'];

    $this->fn_close($fp);
  }

	function fn_open($name, $mode){
    $this->path = dirname($name);
    $name = basename($name);
		if ($this->SET['comp_method'] == 2) {
			$this->filename = "{$name}.sql.bz2";
		    return bzopen($this->path . DIRECTORY_SEPARATOR . $this->filename, "{$mode}b{$this->SET['comp_level']}");
		}
		elseif ($this->SET['comp_method'] == 1) {
			$this->filename = "{$name}.sql.gz";
		    return gzopen($this->path . DIRECTORY_SEPARATOR . $this->filename, "{$mode}b{$this->SET['comp_level']}");
		}
		else{
			$this->filename = "{$name}.sql";
			return fopen($this->path . DIRECTORY_SEPARATOR . $this->filename, "{$mode}b");
		}
	}

	function fn_write($fp, $str){
		if ($this->SET['comp_method'] == 2) {
		    bzwrite($fp, $str);
		}
		elseif ($this->SET['comp_method'] == 1) {
		    gzwrite($fp, $str);
		}
		else{
			fwrite($fp, $str);
		}
	}

	function fn_read($fp){
		if ($this->SET['comp_method'] == 2) {
		    return bzread($fp, 4096);
		}
		elseif ($this->SET['comp_method'] == 1) {
		    return gzread($fp, 4096);
		}
		else{
			return fread($fp, 4096);
		}
	}

	function fn_read_str($fp){
		$string = '';
		$this->file_cache = ltrim($this->file_cache);
		$pos = strpos($this->file_cache, "\n", 0);
		if ($pos < 1) {
			while (!$string && ($str = $this->fn_read($fp))){
    			$pos = strpos($str, "\n", 0);
    			if ($pos === false) {
    			    $this->file_cache .= $str;
    			}
    			else{
    				$string = $this->file_cache . substr($str, 0, $pos);
    				$this->file_cache = substr($str, $pos + 1);
    			}
    		}
			if (!$str) {
			    if ($this->file_cache) {
					$string = $this->file_cache;
					$this->file_cache = '';
				    return trim($string);
				}
			    return false;
			}
		}
		else {
  			$string = substr($this->file_cache, 0, $pos);
  			$this->file_cache = substr($this->file_cache, $pos + 1);
		}
		return trim($string);
	}

	function fn_close($fp){
		if ($this->SET['comp_method'] == 2) {
		    bzclose($fp);
		}
		elseif ($this->SET['comp_method'] == 1) {
		    gzclose($fp);
		}
		else{
			fclose($fp);
		}
		@chmod($this->path . DIRECTORY_SEPARATOR .$this->filename, 0666);
	}
	
	function escape($str) {
		if(function_exists('mysql_real_escape_string')) {
			return mysql_real_escape_string($str, $this->_link);
		} else {
			return mysql_escape_string($str);
		}
	}
}



function fn_int($num){
	return number_format($num, 0, ',', ' ');
}

function fn_arr2str($array) {
	$str = "array(\n";
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$str .= "'$key' => " . fn_arr2str($value) . ",\n\n";
		}
		else {
			$str .= "'$key' => '" . str_replace("'", "\'", $value) . "',\n";
		}
	}
	return $str . ")";
}


function SXD_errorHandler($errno, $errmsg, $filename, $linenum, $vars) {
	if ($errno == 2048) return true;
	if (preg_match("/chmod\(\).*?: Operation not permitted/", $errmsg)) return true;
    $dt = date("Y.m.d H:i:s");
    $errmsg = addslashes($errmsg);

	echo "{$errmsg} ({$errno})";
	die();
}
