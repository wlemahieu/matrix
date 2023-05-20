<?php
/**
 * @author Jose Soto <jsoto@mediatemple.net
 * inserts the uploaded csv file data into the sales db table
*/

class XactlyUpload extends Universal
{

    public function getUploadedFileDates(){
        $query = <<<SQL
            SELECT STR_TO_DATE(order_date, '%m/%d/%Y') AS dates_entered,
                date_add(date_add(LAST_DAY(curdate()),interval 1 DAY),interval -1 MONTH) AS first_of_the_month
            FROM xactly_sales
            GROUP BY dates_entered
            HAVING dates_entered >= first_of_the_month
            ORDER BY dates_entered DESC

SQL;
        return $this->db->query($query);
    }
}

$affectedRowsTotal = 0;

if (isset($_FILES['upload']['size']) && $_FILES['upload']['size'] > 0) {
    $databasehost = "localhost";
    $databasename = "matrix";
    $databasetable = "xactly_sales";
    $databaseusername="app";
    $databasepassword = "n8&&5s_wXL~\d!C";
    $fieldseparator = ",";
    $lineseparator = "\n";
    $enclosedby = '"';

//Loop through each file
for($i=0; $i<count($_FILES['upload']['name']); $i++) {
  //Get the temp file path
  $csvfile = $_FILES['upload']['tmp_name'][$i];

    if(!file_exists($csvfile)) {
        die("File not found. Make sure you specified the correct path.");
    }

    try {
        $pdo = new PDO("mysql:host=$databasehost;dbname=$databasename",
            $databaseusername, $databasepassword,
            array(
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );
    } catch (PDOException $e) {
        die("database connection failed: ".$e->getMessage());
    }

    $affectedRows = $pdo->exec("
        LOAD DATA LOCAL INFILE ".$pdo->quote($csvfile)." INTO TABLE `$databasetable`
            FIELDS TERMINATED BY ".$pdo->quote($fieldseparator)."
            ENCLOSED BY ".$pdo->quote($enclosedby)."
            LINES TERMINATED BY ".$pdo->quote($lineseparator)."
            IGNORE 1 LINES");

    $affectedRowsTotal += $affectedRows;
    $pdo = null;
}
echo "Loaded a total of $affectedRowsTotal records from this import.\n";
echo "<br>";
if($affectedRowsTotal==0){echo "File(s) may have been previously imported.\n";}
}


?>