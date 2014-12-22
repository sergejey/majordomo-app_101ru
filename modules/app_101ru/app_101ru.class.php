<?php
/**
* App_101ru 
*
* App_101ru
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:09:45 [Sep 18, 2014])
*/
//
//
class app_101ru extends module {
/**
* app_101ru
*
* Module class constructor
*
* @access private
*/
function app_101ru() {
  $this->name="app_101ru";
  $this->title="Radio 101.ru";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
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
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
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
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
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
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if (IsSet($this->category_id)) {
   $out['IS_SET_CATEGORY_ID']=1;
  }
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='ru101_stations' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_ru101_stations') {
   $this->search_ru101_stations($out);
  }
  if ($this->view_mode=='edit_ru101_stations') {
   $this->edit_ru101_stations($out, $this->id);
  }
  if ($this->view_mode=='delete_ru101_stations') {
   $this->delete_ru101_stations($this->id);
   $this->redirect("?data_source=ru101_stations");
  }
  if ($this->view_mode=='refresh') {
   $this->refresh_all_stations();
   $this->redirect("?");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='ru101_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_ru101_categories') {
   $this->search_ru101_categories($out);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 global $op;
 global $ajax;
 if ($ajax) {
//  DebMes("101msg");
  if (!headers_sent()) {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
  }
  if ($op=='playstation') {
   global $id;

   $rec=SQLSelectOne("SELECT * FROM ru101_stations WHERE (ID='".(int)$id."' OR TITLE LIKE '".DBSafe($id)."')");
   if ($rec['PAGE_URL']) {
     $data=getURL($rec['PAGE_URL'], 5);
     if (preg_match('/\'pl\':\'(\/play.m3u.+?)\'/isu', $data, $matches)) {
      $playlist_json='http://101.ru'.$matches[1];
      $playlist_json=str_replace('|', '&', $playlist_json);
      $playlist_data=getURL($playlist_json, 5);
      $data=json_decode($playlist_data);
      //var_dump($data);
      $items=$data->playlist;
      $playlist_url=$items[0]->file;
      //echo $playlist_url."\n";
      if ($playlist_url!='') {
       $out['PLAY']=$playlist_url;
       $url=BASE_URL.ROOTHTML.'popup/app_player.html?ajax=1';
       $url.="&command=refresh&play=".urlencode($out['PLAY']);
       getURL($url, 0);
      }
     }
   }
   echo "OK";
  }
  exit;
 }
 

 $categories=SQLSelect("SELECT * FROM ru101_categories ORDER BY TITLE");
 $total=count($categories);
 for($i=0;$i<$total;$i++) {
  $categories[$i]['STATIONS']=SQLSelect("SELECT * FROM ru101_stations WHERE CATEGORY_ID='".$categories[$i]['ID']."' ORDER BY TITLE");
 }
 $out['CATEGORIES']=$categories;

}

/**
* Title
*
* Description
*
* @access public
*/
 function refresh_all_stations() {
   //http://101.ru/?an=port_allchannels
   //SQLExec("DELETE FROM ru101_stations");

   $ids=array();
   SQLExec("DELETE FROM ru101_categories");

   $page1=getURL('http://101.ru/?an=port_allchannels', 5);
   //<a href="/?an=port_groupchannels&amp;group=2" class="h7 genre left">Танцевальные</a>
   $seen=array();
   if (preg_match_all('/tab-item "><a href="(\/\?an=port_groupchannels\&amp;group=\d+)">(.+?)<\/a>/isu', $page1, $matches)) {
    //categories
    $total=count($matches[1]);
    for($i=0;$i<$total;$i++) {
     $title=$matches[2][$i];
     if ($seen[$title]) {
      continue;
     }
     $url='http://101.ru'.$matches[1][$i];
     $rec=array();
     $seen[$title]=1;
     $rec['TITLE']=$title;
     $rec['ID']=SQLInsert('ru101_categories', $rec);

     $url=str_replace('&amp;', '&', $url);
     DebMes($url);
     $page2=getURL($url, 5);

     if (preg_match_all('/href="(\/\?an=port_channel_mp3\&amp;channel=\d+)">([^<]+?)<\/a>/isu', $page2, $m)) {
       $total2=count($m[1]);
       for($i2=0;$i2<$total2;$i2++) {
        $title=$m[2][$i2];
        $url='http://101.ru'.$m[1][$i2];
        $url=str_replace('&amp;', '&', $url);
        $station=array();
        $station['TITLE']=$title;
        $station['PAGE_URL']=$url;
        $station['CATEGORY_ID']=$rec['ID'];
        $old_station=SQLSelectOne("SELECT * FROM ru101_stations WHERE TITLE LIKE '".DBSafe($station['TITLE'])."'");
        if ($old_station['ID']) {
         $station['ID']=$old_station['ID'];
         SQLUpdate('ru101_stations', $station);
        } else {
         $station['ID']=SQLInsert('ru101_stations', $station);
        }
        $ids[]=$station['ID'];
       }
     } else {
      //echo "No matches";exit;
     }

    }

    if (count($ids>0)) {
     SQLExec("DELETE FROM ru101_stations WHERE ID NOT IN (".implode(', ', $ids).")");
    }

   }

 }

/**
* ru101_stations search
*
* @access public
*/
 function search_ru101_stations(&$out) {
  require(DIR_MODULES.$this->name.'/ru101_stations_search.inc.php');
 }
/**
* ru101_stations edit/add
*
* @access public
*/
 function edit_ru101_stations(&$out, $id) {
  require(DIR_MODULES.$this->name.'/ru101_stations_edit.inc.php');
 }
/**
* ru101_stations delete record
*
* @access public
*/
 function delete_ru101_stations($id) {
  $rec=SQLSelectOne("SELECT * FROM ru101_stations WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM ru101_stations WHERE ID='".$rec['ID']."'");
 }
/**
* ru101_categories search
*
* @access public
*/
 function search_ru101_categories(&$out) {
  require(DIR_MODULES.$this->name.'/ru101_categories_search.inc.php');
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
  $this->refresh_all_stations();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS ru101_stations');
  SQLExec('DROP TABLE IF EXISTS ru101_categories');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {
/*
ru101_stations - Stations
ru101_categories - Categories
*/
  $data = <<<EOD
 ru101_stations: ID int(10) unsigned NOT NULL auto_increment
 ru101_stations: TITLE varchar(255) NOT NULL DEFAULT ''
 ru101_stations: CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 ru101_stations: PAGE_URL char(255) NOT NULL DEFAULT ''
 ru101_stations: PLAYLIST_URL char(255) NOT NULL DEFAULT ''
 ru101_categories: ID int(10) unsigned NOT NULL auto_increment
 ru101_categories: TITLE varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDE4LCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>