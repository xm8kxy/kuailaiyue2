<?php
ignore_user_abort ( TRUE );
set_time_limit ( 0 );
$interval = 10;
$stop = 1;
do {
    if( $stop == 10 ) break;
    file_put_contents('liuhui.php',' Current Time: '.time().' Stop: '.$stop);
    $stop++;
    sleep ( $interval );
} while ( true );
?>