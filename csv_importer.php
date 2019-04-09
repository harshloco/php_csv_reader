<?php
function VerifyKey($key)
{	
	if (strlen($key) != 10)
		return false;
	
	$checkDigit = GenerateCheckCharacter(substr(strtoupper($key), 0, 9));
	
	return $key[9] == $checkDigit;
}
// Implementation of Luhn Mod N algorithm for check digit.
function GenerateCheckCharacter(string $input)
{
	$validChars2 = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
	
	$factor = 2;
	$sum = 0;
	$n = strlen($validChars2);
	
	// Starting from the right and working leftwards is easier since
	// the initial "factor" will always be "2"
	for ($i = strlen($input) - 1; $i >= 0; $i--)
	{
		for($j = 0; $j < $n; $j++)
			if($validChars2[$j] == $input[$i])
			{
				$codePoint = $j;
				break;
			}
		
		$addend = $factor * $codePoint;
		// Alternate the "factor" that each "codePoint" is multiplied by
		$factor = ($factor == 2) ? 1 : 2;

		// Sum the digits of the "addend" as expressed in base "n"
		$addend = ($addend / $n) + ($addend % $n);
		
		$sum += (int)($addend);
	}
	
	// Calculate the number that must be added to the "sum"
	// to make it divisible by "n"
	$remainder = $sum % $n;
	$checkCodePoint = ($n - $remainder) % $n;
	
	return $validChars2[$checkCodePoint];
}

// function used to sort the date in ascending order
function date_compare($a, $b)
{
    $t1 = strtotime($a['Date and time']);
    $t2 = strtotime($b['Date and time']);
    return $t1 - $t2;
}    


 ?>
 

<!--- --------HTML CODE BEGINS------->
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>

</head>

<body>
    <div id="wrap">
        <div class="container">
            <div class="row">

                <form class="form-horizontal" action="" method="post" name="upload_excel" enctype="multipart/form-data">
                    <fieldset>

                        <!-- Form Name -->
                        <legend>CSV Object Importer</legend>

                        <!-- File Button -->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="filebutton">Select File</label>
                            <div class="col-md-4">
                                <input type="file" name="file" id="file" class="input-large">
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="form-group">
                            <label class="col-md-4 control-label" for="singlebutton">Import data</label>
                            <div class="col-md-4">
                                <button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Import</button>
                            </div>
                        </div>

                    </fieldset>
                </form>

            </div>
            
<!----////////////-----------PHP CODE BEGINS---------\\\\\\\\\\\\------>
            <?php
            ///// run the code if csv file is selected\\\\\\
               if(isset($_POST["Import"])){
                     $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
					if(in_array($_FILES['file']['type'],$mimes)){
				$filename=$_FILES["file"]["tmp_name"];		

       
		 if($_FILES["file"]["size"] > 0)
		 {
			 echo "<div class='table-responsive'><table id='myTable' class='table table-striped table-bordered'>
             <thead><tr>
                          <th>Date and Time</th>
                          <th>Transaction Number</th>
						  <th>Valid</th>
                          <th>Customer</th>
                          <th>Reference Data</th>
						  <th>Amount</th>
                        </tr></thead><tbody>";
		  	$file = fopen($filename, "r");
			$i = 0;
	        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
	         {
				 //ignore the first line so that the csv table header is not printed again
				 if($i == 0)
				 {
					 $i++;
					 continue;
				 }
				 
				 $valid = VerifyKey($getData[1]);
                //// check if the VerifyKey function returned true or false
				 if($valid == true)
					 $output = "Valid";
				 else
					 $output = "Invalid";
                
    /////////// create an array of the csv data\\\\\\\\\\\\\\\\\

	          $results[] = array(
                     'Date and time' => $getData[0],
					 'Transaction' => $getData[1],
					 'Valid' => $output,
                     'Customer' => $getData[2],
					 'Reference ' => $getData[3],
					 'Amount' => $getData[4]

                    
                    ) ;
   ///////// //////sort the array by Date and Time\\\\\\\\\\\\\\\\\\\\
				usort($results, 'date_compare');
	         }
			 
   ////////////////// Loop through the csv data array, convert cents to dollar and show if it's a debit or credit\\\\\\\\\\\\
			 for($i=0; $i < count($results); $i++)
			 {
				 echo "<tr><td>" . $results[$i]['Date and time']."</td>
                   <td>" . $results[$i]['Transaction']."</td>
				   <td>" .   $results[$i]['Valid'] . "</td>
                   <td>" . $results[$i]['Customer']."</td>
                   <td>" . $results[$i]['Reference ']."</td>";
				   
                 ///////convert the amount in cents to dollar\\\\\\\\
				   if(intval($results[$i]['Amount']) < 0)
                       ////////assign red color to debit amount\\\\\\\\\        
					echo "<td style=\"color: red\">" . intval($results[$i]['Amount']) / 100 ."</td></tr>";  
				   else
					 echo "<td>" . intval($results[$i]['Amount']) / 100 ."</td></tr>";   
			 }
			
 //////////close the csv file\\\\\\\\\\\
	         fclose($file);	
		 }
	} else {
			die("Please, Upload a CSV file!!!");
	}
            
           
	} 
		
		 /////////close the php file\\\\\\\\\\\\
            ?>
        </div>
    </div>
</body>

</html>
