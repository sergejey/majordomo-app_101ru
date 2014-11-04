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
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['ru101_categories_qry'];
  } else {
   $session->data['ru101_categories_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_ru101_categories;
  if (!$sortby_ru101_categories) {
   $sortby_ru101_categories=$session->data['ru101_categories_sort'];
  } else {
   if ($session->data['ru101_categories_sort']==$sortby_ru101_categories) {
    if (Is_Integer(strpos($sortby_ru101_categories, ' DESC'))) {
     $sortby_ru101_categories=str_replace(' DESC', '', $sortby_ru101_categories);
    } else {
     $sortby_ru101_categories=$sortby_ru101_categories." DESC";
    }
   }
   $session->data['ru101_categories_sort']=$sortby_ru101_categories;
  }
  if (!$sortby_ru101_categories) $sortby_ru101_categories="ID DESC";
  $out['SORTBY']=$sortby_ru101_categories;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM ru101_categories WHERE $qry ORDER BY ".$sortby_ru101_categories);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>