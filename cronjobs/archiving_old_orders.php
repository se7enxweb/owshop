<?php

$date = strtotime("last Year");

if ($date !== false)
{
    $orderList = eZOrder::activeOrdersForMoreDate( $date );
    foreach($orderList as $order)
    {
        if(!$isQuiet) {
            $cli->output( 'Archiving order number:' . $order['id'] );
        }
        eZOrder::archiveOrder($order['id']);
    }
}

?>