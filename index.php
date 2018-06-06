<?php
/*
** InputScanner v1.0 | Stay up to date with tools & updates on zseano.com!
** Code written by Sean R (@zseano) & Karl R

** SimpleHTMLDom authors:
  @author S.C. Chen <me578022@gmail.com>
  @author John Schlick
  @author Rus Carroll
  @author S.C. Chen <me578022@gmail.com>

** Licensed under The MIT License
** Redistributions of files must retain the above text.
*/

ini_set('max_execution_time', 900); // customize how you see fit.
ob_implicit_flush(true);
error_reporting(0);

$run = "0";
$showParams = "";
$output_post = "";
$output_get = "";
$run = $_GET['run'];

const URL_FILE = "urls.txt";
const PAYLOAD_FILE = "payloads.txt";

include("file-dom.php");

function loadData($fileName)
{
	$contents = file($fileName);
	
	if ($contents === false)
	{
		$last_error = error_get_last();
		
		if ($last_error != null)
		{
			throw new Exception($last_error['message']);
		}
		else
		{
			throw new Exception('Unknown error');
		}
	}
	
	if ($fileName == 'urls.txt') { $who = "urls"; } else { $who = "payloads"; }
	printf("<font color='orange'>»»</font> Loaded <font color='cyan'>%d %s</font> from %s!<br>", count($contents), $who, $fileName);
	
	return $contents;
}

function processUrls($urls, $payloads)
{
	$fPayload = "";
	$failed="0";
	$success="0";
	foreach($urls as $urls)
	{
		$i=0;
		$b=0;
		$params = "";
		$urls = strip_carriage_returns($urls);
		$html = file_get_html($urls);

		if($html == "") {
			$failed++;
			// this means it either 404'd, 500'd, or domain is down etc. 
			// you can output or handle w/e you want here

			//echo "<font color='red'>FAILED: ".$urls."</font><br><br>";
		} else {
		   $success++;
		// find all input names and ids	
		foreach($html->find('input') as $element) {
			$i++;
			if ($element->name == '') { // try the id if no input name is specified?
				$params .= $element->id . "=fuzz&";
			} else {
       			$params .= $element->name . "=fuzz&";
       		}
       	}

       	// find all script srcs.

	     foreach($html->find('script') as $element) {
			$b++;
			$found = "";
			if ($element->src == '') {
				// No src was found. Feel free to do w/e you want here.
			} else {
				// *** NOTE ***
				// *** Feel free to modify this code how you see fit ***

				// This script echos the url it is found on, for use in JS-Scan
				// It is echo'd in following format: found@https://www.example.com/|https://www.example.com/exampe.js|
				// JS-Scan will then scrape each .js file and let you know which endpoint it was found on (helpful!)

				// Check if it contains https:// http:// www. etc.. as sometimes
				// they just do <script src=/zseano.js>
				$a = ['http://', 'https://', 'www.'];
				$domain = $element->src;
				foreach($a as $word) {
					if (strpos($domain, $word) !== false) {
						$found="true";
					}
				}
				if ($found == "true") {
					$sParams .= "found@".$urls."|".$element->src . "|\n";
				} else {
					// no https etc found. grab the main domain
					$mDomain = GetBetween($urls,"://","/");
					$sParams .= "found@".$urls."|".$mDomain.$element->src . "|\n";
				}
			}
	     }


	    if ($i == '0') {
	    	$pPayload = "none";
	    } else {
	       	foreach($payloads as $payload) {
	       		$payload = strip_carriage_returns($payload);
	       		$pPayload = str_replace("fuzz",$payload,$params); // Append fuzz after every param, then replace to ours
	       		$fpPayload .= $pPayload."\r\n"; // for echoing to POSTData-output.txt
	       		$pUrls .= $urls."\r\n"; // for echoing to POSTHost-output.txt
	       		$fPayload .= $urls."?".$pPayload."\r\n"; // for echoing to GET-output.txt
	       	}
       }

       saveStuff($fPayload, $fpPayload, $pUrls,$sParams);
       $fPayload = "";
       $fpPayload = "";
       $pUrls = "";
       $sParams = "";
   
		echo "<font color='orange'>»»</font> Found <font color='#46FF06'>".$i." inputs</font> on <font color='white'>".$urls."</font><br>";
		echo "<font color='orange'>»»</font> Found <font color='#46FF06'>".$b." .js files</font> on <font color='white'>".$urls."</font><br><br>";
	}

		forceFlush();
	}

		// Save stuff.

	echo "<br><br><hr class='style1'><font color='#46FF06'>".$success."</font> valid endpoints";
	echo "<br><font color='red'>".$failed."</font> endpoints failed<br>";
	echo "<br><font color='white'><b>Outputs are ready to be imported to BURP Intruder!</b></font>";
	echo "<br><font color='white'><b>GET-Outputs - <a href='/outputs/GET-output.txt'>Here</a></b></font>";
	echo "<br><font color='white'><b>JS-Outputs - <a href='/outputs/JS-output.txt'>Here</a></b></font>";
	echo "<br><font color='white'><b>POSTData-Outputs - <a href='/outputs/POSTData-output.txt'>Here</a></b></font>";
	echo "<br><font color='white'><b>PostHost - <a href='/outputs/POSTHost-output.txt'>Here</a></b></font>";
}

function saveStuff($fPayload, $fpPayload, $pUrls,$sParams) {
		$GEToutput = fopen("outputs/GET-output.txt", "a") or die("Unable to open file!");
		fwrite($GEToutput, $fPayload);
		fclose($GEToutput);

		$POSToutput = fopen("outputs/POSTData-output.txt", "a") or die("Unable to open file!");
		fwrite($POSToutput, $fpPayload);
		fclose($POSToutput);
		$POSToutput = fopen("outputs/POSTHost-output.txt", "a") or die("Unable to open file!");
		fwrite($POSToutput, $pUrls);
		fclose($POSToutput);			

		$JSoutput = fopen("outputs/JS-output.txt", "a") or die("Unable to open file!");
		fwrite($JSoutput, $sParams);
		fclose($JSoutput);	
}

function strip_carriage_returns($string)
{
    return str_replace(array("\n\r", "\n", "\r"), '', $string);
}

?>

<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <title>zScanner v1.0 by zseano</title>
    <link rel="stylesheet" href="style/styles.css">
    <style type="text/css">
		hr.style1 {
			border-top: 3px double #8c8b8b;
		}
    </style>
   </head>
 <body>
 <div class="wrapper">
 <div id="header">
    <div class="wrapper">
        <center><h2>zScanner v1.0 by zseano</h2></center>
    </div>
 </div>
  <div style="margin-top:150px; padding-right:50px; padding-left:50px; padding-bottom:30px; padding-top:30px; border: thin solid green">
<?php
if ($run == '1') {
	$urls = loadData(URL_FILE);
	$payloads = loadData(PAYLOAD_FILE);
	echo "<br><hr class='style1'>";
	processUrls($urls, $payloads);
} else {
 ?>
 	A tool designed to scrape a list of urls and extract all input names. Once extracted, 
	payloads you define in payloads.txt will be appended to each parameter, then outputted for you to import into BURP.
	This scanner will also extract all .js files found.
	<br><br>
	To get started, modify urls.txt with a list of urls you wish to scrape and input data into payloads.txt. All data is outputted in the /outputs/ folder.<br><br>
	<font color="red">NOTE:</font> This tool will output endpoints only, without the url. This is so it can be easily imported
	into an Intruder attack on BURP. For example, /test.php?example=1 will be outputted, rather than https://www.example.com/test.php?example=1.
	<br><br>
	JS urls found are outputted as "found@https://www.example.com/|https://www.example.com/test.js|" - this is so you can
	easily import it into JS-scan and discover urls contained in .js files, and the location the .js file was found.
	<br><br>
	<hr class='style1'>
	<form action="index.php" method="GET">
	<?php $urls = loadData(URL_FILE);
	$payloads = loadData(PAYLOAD_FILE);
	?>
	<br>
	All outputs are saved in the /outputs/ directory.
	<br><br><font color="red">Note:</font> This script does NOT follow redirects. Input hard coded URLS to scan!
	<br><br>
	<input type="hidden" name="run" value="1">
	<input type="submit" value="Run scanner"><br><br>
</form>
	<hr class='style1'>
	<div style="font-size: 18px; padding-bottom:10px;"><u>Advanced users</u></div>
	You can customize the HTTP request in the <font color='cyan'>file-dom.php</font> file in the <font color='cyan'>file_get_html</font> function. 
	Some requests may require authentication, so feel free to input your cookies etc.
<?php } ?>
</div>
<br>
This script uses <a href="http://sourceforge.net/projects/simplehtmldom/" target="_blank">SimpleHTMLDom</a>. 
Details can be found in the file-dom.php file.

<?php
function forceFlush() {    
    ob_start(); 
    ob_end_clean(); 
    flush(); 
    ob_end_flush(); 
} 
function GetBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}
?>
