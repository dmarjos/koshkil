<?php
namespace Koshkil\Framework\Wrappers;
use Koshkil\Framework\Core\Application;

class File {

	public static $status=0;

	public static function file_get_contents($filename,$use_include_path=null,$context=null,$offset=0,$maxlen=null) {

		$parsedUrl=parse_url($filename);
		if (!isset($parsedUrl["scheme"]) || Application::get("CURLWRAPPERS_ENABLED")!==true) {
			// es un archivo local
			$contents=file_get_contents($filename);
		} else if (Application::get("CURLWRAPPERS_ENABLED")===true && function_exists("curl_init")) {
			//e_rror_log("Obteniendo via CURL {$filename}");
			$ch = @curl_init(self::curl_redir_exec($filename));
//			$ch = curl_init();
			//e_rror_log("Fetching {$filename}");
			//@curl_setopt($ch, CURLOPT_URL,$filename);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$contents=@curl_exec($ch);
			self::$status=@curl_getinfo($ch);
			@curl_close($ch);
		} else {
			$contents=false;
		}
		return $contents;
	}

	private static function curl_redir_exec($url) {
		$ch=curl_init($url);
        static $curl_loops = 0;
        static $curl_max_loops = 20;
        if ($curl_loops++ >= $curl_max_loops)
        {
            $curl_loops = 0;
            $retVal= FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        @list($header, $data) = @explode("\n\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302)
        {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            //e_rror_log("Matches: ".serialize($matches));
            $url2parse=trim(array_pop($matches));
            //e_rror_log("URL to parse: {$url2parse}");
            $url = @parse_url($url2parse);
            //e_rror_log("Parsed URL: ".serialize($url));
            if (!$url)
            {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                $retVal=trim($data);
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            if (!$url['scheme'])
                $url['scheme'] = $last_url['scheme'];
            if (!$url['host'])
                $url['host'] = $last_url['host'];
            if (!$url['path'])
                $url['path'] = $last_url['path'];
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (@$url['query']?'?'.$url['query']:'');

            //e_rror_log("New URL: {$new_url}");

            $retVal=trim($new_url);
        } else {
            $curl_loops=0;
            $retVal=$url;
        }
        curl_close($ch);
        return $retVal;
    }

    private static function get_right_url($url) {
        $curl = curl_init($url);
        return self::curl_redir_exec($curl);
    }
}