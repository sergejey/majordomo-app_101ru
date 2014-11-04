<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  if (IsSet($this->category_id)) {
   $category_id=$this->category_id;
   $qry.=" AND CATEGORY_ID='".$this->category_id."'";
  } else {
   global $category_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['ru101_stations_qry'];
  } else {
   $session->data['ru101_stations_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_ru101_stations;
  if (!$sortby_ru101_stations) {
   $sortby_ru101_stations=$session->data['ru101_stations_sort'];
  } else {
   if ($session->data['ru101_stations_sort']==$sortby_ru101_stations) {
    if (Is_Integer(strpos($sortby_ru101_stations, ' DESC'))) {
     $sortby_ru101_stations=str_replace(' DESC', '', $sortby_ru101_stations);
    } else {
     $sortby_ru101_stations=$sortby_ru101_stations." DESC";
    }
   }
   $session->data['ru101_stations_sort']=$sortby_ru101_stations;
  }
  if (!$sortby_ru101_stations) $sortby_ru101_stations="ID DESC";
  $out['SORTBY']=$sortby_ru101_stations;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM ru101_stations WHERE $qry ORDER BY ".$sortby_ru101_stations);
  if ($res[0]['ID']) {
   if ($this->action=='admin') {
    paging($res, 50, $out); // search result paging
   }
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>