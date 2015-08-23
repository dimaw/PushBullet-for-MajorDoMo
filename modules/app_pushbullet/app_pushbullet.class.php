<?php
require_once('PushBullet.class.php');

function push_note($device, $title, $body) {
	$table_name = 'app_pushbullet';
	$rec = SQLSelectOne("SELECT * FROM $table_name WHERE name='$device'");
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		$p->pushNote($rec['iden'], $title, $body);
	}
}

function push_note_to_all($title, $body) {
	$table_name = 'app_pushbullet';
	$recs = SQLSelect("SELECT * FROM $table_name");
	foreach($recs as $rec) {
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		$p->pushNote($rec['iden'], $title, $body);
	}}
}

function push_list($devices, $title, $items) {
	$table_name = 'app_pushbullet';
	$rec = SQLSelectOne("SELECT * FROM $table_name WHERE name='$devices'");
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		$p->pushList($rec['iden'], $title, $items);
	}
}

function push_address($devices, $name, $address) {
	$table_name = 'app_pushbullet';
	$rec = SQLSelectOne("SELECT * FROM $table_name WHERE name='$devices'");
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		//в push выглядит верно, но на карте не открывает из-за не поддерживаемой кодировки:
		//$p->pushAddress($rec['iden'], $name, $address);
		//$p->pushAddress($rec['iden'], $name, mb_convert_encoding($address, 'utf8', mb_detect_encoding($address)));
		//$p->pushAddress($rec['iden'], $name, iconv(mb_detect_encoding($address), "UTF-8//TRANSLIT", $address));
				
		//адрес открывает на карте верно, но в push иероглифы:
		//$p->pushAddress($rec['iden'], $name, utf8_encode($address));
		//$p->pushAddress($rec['iden'], $name, mb_convert_encoding($address, 'utf8'));
		
		//будем использовать транслитерацию пока не починят:
		$p->pushAddress($rec['iden'], $name, encodestring($address));
	}
}

function push_file($devices, $fileName) {
	$table_name = 'app_pushbullet';
	$rec = SQLSelectOne("SELECT * FROM $table_name WHERE name='$devices'");
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		$p->pushFile($rec['iden'], $fileName);
	}
}

function push_link($devices, $title, $url, $body = NULL) {
	$table_name = 'app_pushbullet';
	$rec = SQLSelectOne("SELECT * FROM ".$table_name." WHERE name='".$devices."'");
	if ($rec['ID']) {
		$p = new PushBullet($rec['apikey']);
		$p->pushLink($rec['iden'], $title, $url, $body = NULL);
	}
}

function encodestring($string){
	$table = array( 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 
					'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 
					'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 
					'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 
					'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 
					'Ш' => 'SH', 'Щ' => 'SCH', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '', 
					'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 

					'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 
					'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 
					'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
					'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 
					'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 
					'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '', 
					'э' => 'e', 'ю' => 'yu', 'я' => 'ya');
	$output = str_replace(array_keys($table), array_values($table),$string);
	//также те символы что неизвестны
	//$output = preg_replace('/[^-a-z0-9._\[\]\'"]/i', ' ', $output);
	//$output = preg_replace('/ +/', '-', $output);
     return $output;
}

class app_pushbullet extends module {
	function app_pushbullet() {
	  $this->name="app_pushbullet";
	  $this->title="PushBullet";
	  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
	  $this->checkInstalled();
	}
	function saveParams() {
	 $p=array();
	 if (IsSet($this->id)) {
	  $p["id"]=$this->id;
	 }
	 if (IsSet($this->view_mode)) {
	  $p["view_mode"]=$this->view_mode;
	 }
	 if (IsSet($this->edit_mode)) {
	  $p["edit_mode"]=$this->edit_mode;
	 }
	 if (IsSet($this->tab)) {
	  $p["tab"]=$this->tab;
	 }
	 return parent::saveParams($p);
	}
	function getParams() {
	  global $id;
	  global $mode;
	  global $view_mode;
	  global $edit_mode;
	  global $tab;
	  if (isset($id)) {
	   $this->id=$id;
	  }
	  if (isset($mode)) {
	   $this->mode=$mode;
	  }
	  if (isset($view_mode)) {
	   $this->view_mode=$view_mode;
	  }
	  if (isset($edit_mode)) {
	   $this->edit_mode=$edit_mode;
	  }
	  if (isset($tab)) {
	   $this->tab=$tab;
	  }
	}
	function run() {
	 global $session;
	  $out=array();
	  if ($this->action=='admin') {
	   $this->admin($out);
	  } else {
	   $this->usual($out);
	  }
	  if (IsSet($this->owner->action)) {
	   $out['PARENT_ACTION']=$this->owner->action;
	  }
	  if (IsSet($this->owner->name)) {
	   $out['PARENT_NAME']=$this->owner->name;
	  }
	  $out['VIEW_MODE']=$this->view_mode;
	  $out['EDIT_MODE']=$this->edit_mode;
	  $out['MODE']=$this->mode;
	  $out['ACTION']=$this->action;
	  if ($this->single_rec) {
	   $out['SINGLE_REC']=1;
	  }
	  $this->data=$out;
	  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
	  $this->result=$p->result;
	}
	


	function admin(&$out) {
		/*if ($this->mode == 'update') {
			global $API_Key;
            setGlobal('PushBullet.API_KEY',$API_Key);
			$out['API_KEY'] = getGlobal('PushBullet.API_KEY');
		}*/
		if ($this->mode == 'import' || $this->mode == 'update') {
			global $API_Key;
			if ($API_Key != "") {
				$table_name = 'app_pushbullet';
				$p = new PushBullet($API_Key);
				$devices = $p->getDevices();
				$rec_ok = 1;
				$i = 0;
				while ($rec_ok) {
					$rec = array();
					$rec['apikey']=$API_Key;
					$rec['iden']=$devices['devices'][$i]['iden'];
					$rec['name']=$devices['devices'][$i]['nickname'];
					if ($rec['iden'] == '') {
						$rec_ok = 0;
					}
					if ($rec_ok) {
						$old = SQLSelectOne("SELECT ID FROM " . $table_name . " WHERE iden LIKE '" . DBSafe($rec['iden']) . "'");
						if ($old['ID']) {
							//$rec['ID'] = $old['ID'];
							//SQLUpdate($table_name, $rec);
						} else {
							SQLInsert($table_name, $rec);
						}
						$out["TOTAL"]++;
					}
					$i++;
				}
				$out['OK'] = 1;
			} else {
                //$out['ERR'] = 1;
            }

		}
		if ($this->view_mode == '' || $this->view_mode == 'view_devices') {
			$this->view_devices($out);
        }
		if ($this->view_mode == 'edit_devices') {
			$this->edit_devices($out, $this->id);
		}
		if ($this->view_mode == 'delete_devices') {
			$this->delete_devices($this->id);
			$this->redirect("?");
		}
    }

	function usual(&$out) {
	 $this->admin($out);
	 
	}
	
	function view_devices(&$out)
    {
        $table_name = 'app_pushbullet';
        $res = SQLSelect("SELECT * FROM $table_name");
        if ($res[0][ID]) {
            $out['RESULT'] = $res;
        }
    }
	
	function edit_devices(&$out, $id) {
		$table_name = 'app_pushbullet';
        $rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

        if ($this->mode == 'update') {
            $ok = 1;
            global $apikey;
            global $iden;
            global $name;
			$rec['apikey'] = $apikey;
            $rec['iden'] = $iden;
			$rec['name'] = $name;
            if (($rec['apikey'] == '') || ($rec['iden'] == '') || ($rec['name'] == '')) {
                $out['ERR_stations'] = 1;
                $ok = 0;
            }
            //UPDATING RECORD
			if ($ok) {
                if ($rec['ID']) {
                    SQLUpdate($table_name, $rec); // update
                } else {
                    $new_rec = 1;
                    $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
                }
                $out['OK'] = 1;
            } else {
                $out['ERR'] = 1;
            }
        }
        outHash($rec, $out);
	}
	
	function delete_devices($id) {
		$table_name = 'app_pushbullet';
		$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
		SQLExec("DELETE FROM $table_name WHERE ID='" . $rec['ID'] . "'");
	}

	 function install() {

		//$className = 'Push';
        //$objectName = 'PushBullet';
        //$propertis = array('API_KEY1', 'Device1');

        $rec = SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '" . DBSafe($className) . "'");
        if (!$rec['ID']) {
            $rec = array();
            $rec['TITLE'] = $className;
            //$rec['PARENT_LIST']='0';
            $rec['DESCRIPTION'] = 'Push-notes';
            $rec['ID'] = SQLInsert('classes', $rec);
        }

        $obj_rec = SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='" . $rec['ID'] . "' AND TITLE LIKE '" . DBSafe($objectName) . "'");
        if (!$obj_rec['ID']) {
            $obj_rec = array();
            $obj_rec['CLASS_ID'] = $rec['ID'];
            $obj_rec['TITLE'] = $objectName;
            $obj_rec['DESCRIPTION'] = 'Настройки';
            $obj_rec['ID'] = SQLInsert('objects', $obj_rec);
        }

        for ($i = 0; $i < count($propertis); $i++) {
            $prop_rec = SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID='" . $obj_rec['ID'] . "' AND TITLE LIKE '" . DBSafe($propertis[$i]) . "'");
            if (!$prop_rec['ID']) {
                $prop_rec = array();
                $prop_rec['TITLE'] = $propertis[$i];
                $prop_rec['OBJECT_ID'] = $obj_rec['ID'];
                $prop_rec['ID'] = SQLInsert('properties', $prop_rec);
            }
        }
	  parent::install();
	 }
	 
	 function dbInstall($data)
    {

$data = <<<EOD
 app_pushbullet: ID int(10) unsigned NOT NULL auto_increment
 app_pushbullet: apikey text
 app_pushbullet: iden text
 app_pushbullet: name text
EOD;
        parent::dbInstall($data);
    }
}
