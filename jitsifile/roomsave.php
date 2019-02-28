<?php

    $servername = "localhost";
    $username   = "root";
    $password   = "OIANDONAODNOIAND113";
    $database   = "staging_p2";


    $jitsi_id = $_REQUEST[ 'participant' ];
    $type     = $_REQUEST[ 'type' ];
    $meetid   = $_REQUEST[ 'meeting_id' ];
// Create connection
    $conn     = new mysqli($servername, $username, $password, $database);
    

    $fetch_data = mysqli_query($conn, "select * from `room` where `meet_id`='".$meetid."' and user_id_jitsi='".$jitsi_id."'");
    $fetch=mysqli_fetch_array($fetch_data);
    if(empty($fetch)){
        $insert_data = mysqli_query($conn, "INSERT INTO room (user_id_jitsi,type,meet_id) VALUES ('$jitsi_id', '$type','$meetid')");
    }
    
?>