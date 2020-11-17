<?php
    //Connect to database
    require('connectDB.php');
//**********************************************************************************************
    //Get current date and time
    date_default_timezone_set('Asia/Seoul');
    $d = date("Y-m-d");
    $t = date("H:i:sa");
//**********************************************************************************************
    $Tarrive = mktime(01,30,00);
    $TimeArrive = date("H:i:sa", $Tarrive);
//**********************************************************************************************
    $Tleft = mktime(02,30,00);
    $Timeleft = date("H:i:sa", $Tleft);
    $total_length = 0;
    $standard_t = 37.5;
//**********************************************************************************************

if(!empty($_GET['test'])){
    if($_GET['test'] == "test"){
        echo "The Website is online";
        exit();
    }
}

if(!empty($_GET['CardID'])){

    $Card_ = $_GET['CardID'];
    $total_legnth = strlen($Card_);
    $Temp_length = intval(substr($Card_,-1,1));
    $Card_length = $total_length - $Temp_length - 1;
    $Temp = substr($Card_, -($Temp_length+1), $Temp_length);
    $Temp_f = floatval($Temp);
    $Card = substr($Card_, 0, $Card_length);

    $sql = "SELECT * FROM users WHERE CardID=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error_Select_card";
        exit();
    }
    else{
        mysqli_stmt_bind_param($result, "s", $Card);
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)){
            //*****************************************************
            //An existed card has been detected for Login or Logout
            $cnt = $row['Cnt'];
            $total = $row['Total'];
            $avg = $row['Average'];
            if (!empty($row['username'])){
                $Uname = $row['username'];
                $Number = $row['SerialNumber'];
                $sql = "SELECT * FROM logs WHERE CardNumber=? ORDER BY id DESC";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_logs";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "s", $Card);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    $row = mysqli_fetch_assoc($resultl);
                    //*****************************************************
                    //Login
                    if (!$row or $row['UserStat']!="in"){
                        $cnt = $cnt + 1;
                        $total = $total + $Temp_f;
                        $avg = $total/$cnt;
                        if($Temp_f >= $standard_t){
                            $UserStat = "COVID-19";
                        }
                        else{
                            $UserStat = "in";
                        }
                        $sql = "INSERT INTO logs (CardNumber, Name, SerialNumber, DateLog, TimeIn, UserStat, Temperature) VALUES (? ,?, ?, CURDATE(), CURTIME(), ?,$Temp)";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_Select_login1";
                            exit();
                        }
                        else{
                            mysqli_stmt_bind_param($result, "ssds", $Card, $Uname, $Number, $UserStat);
                            mysqli_stmt_execute($result);
                            if($UserStat == "in"){
                                echo "login";
                            }
                            else{
                                echo "Access Denied";
                            }
                            $sql = "UPDATE users SET Cnt=$cnt, Total=$total, Average=$avg WHERE CardID=?";
                            $result = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($result, $sql)){
                                echo "SQL_Error_update_users";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "d", $Card);
                                mysqli_stmt_execute($result);
                                exit();
                            }
                        }
                    }
                    //*****************************************************
                    //Logout
                    else {
                        $cnt = $cnt + 1;
                        $total = $total + $Temp_f;
                        $avg = $total/$cnt;
                        $TI = $row['TimeIn'];
                        $UserStat = "out";
                        $sql="UPDATE logs SET Temperature = $Temp, TimeOut=CURTIME(), UserStat=? WHERE CardNumber=? AND TimeIn=?";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert_logout1";
                            exit();
                        }
                        else{
                            if($Temp >= $standard_t){
                                $UserStat = "COVID-19";
                                echo "Caution";
                            }
                            else{
                                echo "logout";
                            }
                            mysqli_stmt_bind_param($result, "sds", $UserStat,$Card,$TI);
                            mysqli_stmt_execute($result);
                            $sql = "UPDATE users SET Cnt=$cnt, Total=$total, Average=$avg WHERE CardID=?";
                            $result = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($result, $sql)){
                                echo "SQL_Error_update_users";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "d", $Card);
                                mysqli_stmt_execute($result);
                                exit();
                            }
                        }
                    }
                }
            }
            //*****************************************************
            //An available card has been detected
            else{
                $sql = "SELECT CardID_select FROM users WHERE CardID_select=?";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select";
                    exit();
                }
                else{
                    $card_sel = 1;
                    mysqli_stmt_bind_param($result, "i", $card_sel);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);

                    if ($row = mysqli_fetch_assoc($resultl)) {

                        $sql="UPDATE users SET CardID_select =?";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert";
                            exit();
                        }
                        else{
                            $card_sel = 0;
                            mysqli_stmt_bind_param($result, "i", $card_sel);
                            mysqli_stmt_execute($result);

                            $sql="UPDATE users SET CardID_select =? WHERE CardID=?";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error_insert_An_available_card";
                                exit();
                            }
                            else{
                                $card_sel = 1;
                                mysqli_stmt_bind_param($result, "is", $card_sel, $Card);
                                mysqli_stmt_execute($result);

                                echo "Cardavailable";
                                exit();
                            }
                        }
                    }
                    else{
                        $sql="UPDATE users SET CardID_select =? WHERE CardID=?";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert_An_available_card";
                            exit();
                        }
                        else{
                            $card_sel = 1;
                            mysqli_stmt_bind_param($result, "is", $card_sel, $Card);
                            mysqli_stmt_execute($result);

                            echo "Cardavailable";
                            exit();
                        }
                    }
                }
            }
        }
        //*****************************************************
        //New card has been added
        else{
            $Uname = "";
            $Number = "";
            $gender= "";

            $sql = "SELECT CardID_select FROM users WHERE CardID_select=?";
            $result = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($result, $sql)) {
                echo "SQL_Error_Select";
                exit();
            }
            else{
                $card_sel = 1;
                mysqli_stmt_bind_param($result, "i", $card_sel);
                mysqli_stmt_execute($result);
                $resultl = mysqli_stmt_get_result($result);
                if ($row = mysqli_fetch_assoc($resultl)) {

                    $sql="UPDATE users SET CardID_select =?";
                    $result = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($result, $sql)) {
                        echo "SQL_Error_insert";
                        exit();
                    }
                    else{
                        $card_sel = 0;
                        mysqli_stmt_bind_param($result, "i", $card_sel);
                        mysqli_stmt_execute($result);

                        $sql = "INSERT INTO users (username , SerialNumber, gender, CardID, CardID_select, Cnt, Total, Average) VALUES (?, ?, ?, ?, ?, 0, 0, 0)";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_Select_add";
                            exit();
                        }
                        else{
                            $card_sel = 1;
                            mysqli_stmt_bind_param($result, "sdssi", $Uname, $Number, $gender, $Card, $card_sel);
                            mysqli_stmt_execute($result);

                            echo "succesful";
                            exit();
                        }
                    }
                }
                else{
                    $sql = "INSERT INTO users (username , SerialNumber, gender, CardID, CardID_select, Cnt, Total, Average) VALUES (?, ?, ?, ?, ?, 0, 0, 0)";
                    $result = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($result, $sql)) {
                        echo "SQL_Error_Select_add";
                        exit();
                    }
                    else{
                        $card_sel = 1;
                        mysqli_stmt_bind_param($result, "sdssi", $Uname, $Number, $gender, $Card, $card_sel);
                        mysqli_stmt_execute($result);

                        echo "succesful";
                        exit();
                    }
                }
            }
        }
    }
}
//*****************************************************
//Empty Card ID
else{
    echo "Empty_Card_ID";
    exit();
}
mysqli_stmt_close($result);
mysqli_close($conn);
?>
