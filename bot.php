<?php
set_time_limit(60);

require_once('config.php'); 

//$url = $addresse_fcp.$start_page;
//$url = 'http://www.lemonde.fr/';

$sitekey = 'PFeLTa1si2Ml5sDeUy7eDhPso6TPdmw-2gWfQ4Jg02w,3ocfrqgUMVWA2PeorZx40TW0c-FiIOL-TWKQHoDbVdE,AQABAAE';
$sitename = 'Index';

$sitepath = "/USK@$sitekey/$sitename";

$buffer_file = 'local.html';

$bot = new bot();

$bot->getDistantFile($buffer_file, $fcp_host, $fcp_port, $sitepath.'/-1');
//$bot->buffer_contents = $bot->getFileContents($buffer_file);

//$urls = $bot->extractURLs();
//$bot->cleanURLs($urls, $sitekey, $sitename);

//print_r($urls);
//echo $bot->buffer_contents;


class bot {
	
	var $buffer_contents;
	
	function getDistantFile ($buffer_file, $fcp_host, $fcp_port, $sitepath='')
	{
		global $timeout, $wget_dir;
		
		//exec($wget_dir."wget.exe --timeout=$timeout ${fcp}$sitepath -O $buffer_file");
		
		$fp = fsockopen($fcp_host, $fcp_port, $errno, $errstr, $timeout);
		if (!$fp) {
			echo "$errstr ($errno)<br />\n";
		}
		else
		{
			$out = "GET $sitepath HTTP/1.1\r\n";
			$out .= "Host: $fcp_host\r\n";
			$out .= "Connection: Close\r\n\r\n";
		
			fwrite($fp, $out);
			
			while (!feof($fp)) {
				echo fgets($fp, 128);
			}
			fclose($fp);
		}
		
		$this->buffer_contents = $this->getFileContents($buffer_file);
	}
	
	function getFileContents ($file)
	{
		
		$handle = fopen($file, 'r') or die('Erreur � l\'ouverture du fichier'.$file);
		$contents = fread($handle, filesize ($file));
		fclose($handle);
		
		return $contents;
	}
	
	function getLastEdition ()
	{
		
	}
	
	function extractTitle ()
	{
		if ( preg_match_all('/<title>(.+?)<\/title>/s', $this->buffer_contents, $title) ) {
			return $title[1][0];
		}
	}
	
	function extractMetas ()
	{
		if (preg_match_all('/<meta(.+?)>/si', $this->buffer_contents, $matches))
		{
			foreach ($matches[1] as $value) // contenu de chaque balise meta
			{
				preg_match_all('/ ?(.+?)="(.+?)" ?/si', $value, $matches2);
				foreach ($matches2[1] as $key => $value) // chaque cl�e
				{
					if ($value == 'name' || $value == 'content')					
						$buf[ $matches2[1][$key] ] = $matches2[2][$key];
				}
				
				if ( !empty($buf['name']) && !empty($buf['content']) )
					$meta[$buf['name']] = $buf['content'];

				unset($buf);

			}
		}
		
		return $meta;
		
	}
	
	function extractURLs ()
	{
			
	    if ( preg_match_all('/<a href="(.*?)".*>/i', $this->buffer_contents, $matches) )  
	    	return $matches[1];
	    	
	}
	
	function cleanURLs (&$urls, $sitekey, $sitename)
	{
		// todo: support des ../
		
		foreach ($urls as $key => $value)
		{
			
			$value = trim($value);
			
			if ( substr($value, 0, 7) == 'http://') // si l'url commence par http://, on la retire
			{
				$value = '';
			}
			elseif ( substr($value, 0, 1) != '/') // si ce n'est pas une url absolue alors
			{
				if ( substr($value, 0, 2) == './') // on enl�ve �ventuellement ./
					$value = substr($value, 2);
				
				// on ajoute $sitepath
				$value = '/'.$sitekey.'/'.$sitename.'/'.$value;
			}
			
			if ( substr($value, -1) == '/') // si l'url fini par un slash, on le retire
				$value = substr($value, 0, -1);
			
			// On retire les liens vers les diverses versions
			if ( preg_match("#^/[A-Z]{3,3}@$sitekey/$sitename/?-[0-9]+$#i", $value, $matches, PREG_OFFSET_CAPTURE) )
				$value = '';
				
			// mise � jour de l'url
			$urls[$key] = $value; 
		
		}
	}
	

}


/*
$addresse_complete = "$addresse_fcp" . "$start_page";

exec("c:\wget\wget.exe --timeout=$timeout $addresse_complete -O c:\serveur\www\freenetbot\local.html");




$fich='local.html';
$ouvre=fopen($fich,'r');
$filesize = filesize("local.html");


while(!feof($ouvre))
{
	$ligne=fgets($ouvre,$filesize);
	
	if (eregi("<title>(.*)</title>", $ligne, $titre) == TRUE) {
		//echo $titre[1];
	}
	
	if (eregi("<a(.*)>(.*)</a>", $ligne, $liens) == TRUE) {
		$liens_complet = $liens[0];
		$test = explode("href=",$liens_complet);
		$testa = $test[1];
		$test1 = explode("\"",$testa);
		$testb = $test1[1];
		
		if (eregi("newbookmark",$testb) == TRUE) { }
		elseif (eregi("@",$testb) == TRUE) {
			$cible = "$addresse_fcp" . "$testb";
			echo "externe : $cible<br>";
		}
		else { 
			$cible = "$addresse_complete" . "$testb";
			echo "interne : $cible<br>"; 
		}
		//exit();
	}
    break;
}

fclose($ouvre);
*/

?>