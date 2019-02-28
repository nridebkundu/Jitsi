<?php

    $servername = "localhost";
    $username   = "root";
    $password   = "OIANDONAODNOIAND113";
    $database   = "staging_p2";


    $jitsi_id = $_REQUEST[ 'participant' ];

// Create connection
    $conn = new mysqli($servername, $username, $password, $database);

//    echo $jitsi_id;
    $fetch_data = mysqli_query($conn, "select * from `room` where user_id_jitsi='" . $jitsi_id . "'");
//    $fetch_data = mysqli_query($conn, "select * from `room` where user_id_jitsi=709aaf7b");
    $fetch      = mysqli_fetch_array($fetch_data);

//    echo "<pre>";
//    print_r($fetch);
//    die;
    $data = array ();
    if ( !empty($fetch) ) {
        $data[ 'type' ] = $fetch[ 'type' ];
        $data[ 'pid' ]  = $jitsi_id;
        echo json_encode($data);
    }
?>