<?php
header('Content-Type: application/json');
const PLOT_STEP = 60 * 60 * 2; //2hours

if (isset($_GET['singleton'])) {
   $singleton = $_GET['singleton'];
   $json = file_get_contents('https://api.xchpool.org/v1/members/get?search='.$singleton);
   sleep(0.5);
   $jsonS = file_get_contents('https://api.xchpool.org/v1/poolstats');
   $jsonR = file_get_contents('https://api.xchpool.org/v1/rewards/list');
   
   if ($json) {
      $obj = json_decode( $json );
      $poolstats = json_decode( $jsonS );
      $rewards = json_decode( $jsonR );
      $today_r = 0;
      foreach ($rewards->rewards as $item) {
         if (strpos($item->timestamp, date("Y-m-d")) !== false) {
            $today_r += 1;      
         }             
      }      
      
      $data['title'] = 'XCHPool Info';
      $data['title_color'] = 'green';
      
      $data['tiles'][0]['tile'] = 'stack';
      $data['tiles'][0]['row'] = '1';
      $data['tiles'][0]['col'] = '1';
      $data['tiles'][0]['row_span'] = '1';
      $data['tiles'][0]['col_span'] = '2';
      if (!empty((float)str_replace('XCH ','',$obj->unpaidBalance))) {
         $data['tiles'][0]['background'] = '#990000';
      } else {
         $data['tiles'][0]['background'] = '#00802b';
      }          
      $data['tiles'][0]['title'] = 'Payout XCH';
      $data['tiles'][0]['value'] = str_replace('XCH ','',$obj->totalPayout);
      $data['tiles'][0]['label'] = 'Unpaid '.str_replace('XCH ','',$obj->unpaidBalance);
      
      $data['tiles'][1]['tile'] = 'stack';
      $data['tiles'][1]['row'] = '1';
      $data['tiles'][1]['col'] = '3';
      $data['tiles'][1]['row_span'] = '1';
      $data['tiles'][1]['col_span'] = '2';
      $data['tiles'][1]['title'] = 'Points';
      $data['tiles'][1]['background'] = '#002699';
      $data['tiles'][1]['value'] = $obj->points;
      $data['tiles'][1]['label'] = 'Farmer difficulty '.$obj->farmerDifficulty;
      
      $data['tiles'][2]['tile'] = 'stack';
      $data['tiles'][2]['row'] = '2';
      $data['tiles'][2]['col'] = '1';
      $data['tiles'][2]['row_span'] = '1';
      $data['tiles'][2]['col_span'] = '2';
      $data['tiles'][2]['title'] = 'Estimated Size';
      $data['tiles'][2]['value'] = number_format($obj->netspace / 1024 / 1024 / 1024 / 1024,2).'TiB';
      $data['tiles'][2]['label'] = 'Update time '.date('m/d H:i', $obj->payouts->date);
      
      $data['tiles'][3]['tile'] = 'stack';
      $data['tiles'][3]['row'] = '2';
      $data['tiles'][3]['col'] = '3';
      $data['tiles'][3]['row_span'] = '1';
      $data['tiles'][3]['col_span'] = '2';
      $data['tiles'][3]['title'] = 'Round Share';
      $data['tiles'][3]['value'] = number_format($obj->currentPoolShare * 100,4).'%';
      $data['tiles'][3]['label'] = 'Pool Capacity '.number_format($poolstats->poolCapacityBytes / 1024 / 1024 / 1024 / 1024 / 1024 ,2).'PiB';
      
      $data['tiles'][4]['tile'] = 'bar_graph';
      $data['tiles'][4]['row'] = '3';
      $data['tiles'][4]['col'] = '1';
      $data['tiles'][4]['row_span'] = '1';
      $data['tiles'][4]['col_span'] = '4';
      $data['tiles'][4]['title'] = 'Farm - Partials [today]';
      $data['tiles'][4]['margin'] = 1;
      $data['tiles'][4]['label_size'] = 9;
      $k = 0;
      $y = 0;
      foreach ($obj->partials as $idx => $item) {
        $ndate = $item->timestamp;         
        if ($idx == 0) {
          $sdate = $ndate;
        }        
        if ( $ndate-$sdate < PLOT_STEP ) {
           $y += $item->partials;    
        } else {
           $data['tiles'][4]['data'][$k]['x'] = date('H',$sdate + PLOT_STEP).':00';
           $data['tiles'][4]['data'][$k]['y'] = $y;            
           $y = $item->partials;    
           $sdate = $item->timestamp;         
           $k += 1;
        }
      }      
      
      $data['tiles'][5]['tile'] = 'bar_graph';
      $data['tiles'][5]['row'] = '4';
      $data['tiles'][5]['col'] = '1';
      $data['tiles'][5]['row_span'] = '1';
      $data['tiles'][5]['col_span'] = '4';
      $data['tiles'][5]['title'] = 'Pool - Won Blocks [days] [today:'.$today_r.']';
      $data['tiles'][5]['margin'] = 1;
      $data['tiles'][5]['label_size'] = 9;
      foreach ($poolstats->wonBlocks as $idx => $item) {
        $data['tiles'][5]['data'][$idx]['x'] = date('d',$item->timestamp);
        $data['tiles'][5]['data'][$idx]['y'] = $item->blocks;
      }      
      $idx += 1;
      $data['tiles'][5]['data'][$idx]['x'] = date('d');
      $data['tiles'][5]['data'][$idx]['y'] = $today_r;
      
      $data['tiles'][6]['tile'] = 'bar_graph';
      $data['tiles'][6]['row'] = '5';
      $data['tiles'][6]['col'] = '1';
      $data['tiles'][6]['row_span'] = '1';
      $data['tiles'][6]['col_span'] = '4';
      $data['tiles'][6]['title'] = 'Farm - Payouts [days]';
      $data['tiles'][6]['margin'] = 1;
      $data['tiles'][6]['label_size'] = 9;
      foreach (array_reverse($obj->payouts->earnings) as $idx => $item) {
        $data['tiles'][6]['data'][$idx]['x'] = date('d',$item->date_earned);
        $data['tiles'][6]['data'][$idx]['y'] = $item->amount;
      }            
      echo json_encode( $data );
   }      
}
?>





















