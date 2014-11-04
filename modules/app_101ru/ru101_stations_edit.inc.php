<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='ru101_stations';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'CATEGORY_ID' (int)
   if (IsSet($this->category_id)) {
    $rec['CATEGORY_ID']=$this->category_id;
   } else {
   global $category_id;
   $rec['CATEGORY_ID']=(int)$category_id;
   }
  //updating 'PAGE_URL' (url, required)
   global $page_url;
   $rec['PAGE_URL']=$page_url;
   if ($rec['PAGE_URL']=='' || $rec['PAGE_URL']=='http://') {
    $out['ERR_PAGE_URL']=1;
    $ok=0;
   }
  //updating 'PLAYLIST_URL' (url)
   global $playlist_url;
   $rec['PLAYLIST_URL']=$playlist_url;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>