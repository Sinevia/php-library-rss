<?php

class RSS{
	private $url;
	private $data = false;
	private $cache_folder = false;
	private $cache_time = 3600;
	private $namespaces = array (
		'http://purl.org/rss/1.0/' => 'RSS 1.0',
		'http://purl.org/rss/1.0/modules/content/' => 'RSS 2.0',
		'http://www.w3.org/2005/Atom' => 'ATOM 1.0',
	);
	private $type = false;
	private $version = false;
	
  /**
   * Constructor.
   */
	function __construct($url="",$options=array()){
	  $this->url = $url;
		
		// Setting cache folder
		if(isset($options["CACHE_FOLDER"])){
			if(is_dir($options["CACHE_FOLDER"])){
				$this->cache_folder = $options["CACHE_FOLDER"];
			}elseif(mkdir($options["CACHE_FOLDER"])==true){
				$this->cache_folder = $options["CACHE_FOLDER"];
			}
		}
		
	    // Setting cache time
		if(isset($options["CACHE_TIME"])){
			$this->cache_time = $options["CACHE_TIME"];
		}
	}
    

  /**
   * Fetches an RSS URL
   */
  public function fetch_url(){
		$url = $this->url;
		// No cache folder
		if($this->cache_folder==false){
			$url = str_replace(' ','%20',$url);
			$content = @file_get_contents($url);
			return $content;
		}
		
		$file_name = str_replace("/","_",$url);
		$file_name = str_replace(":","_",$file_name);
		$file_name = str_replace("?","_",$file_name);
		$file_name = str_replace("=","_",$file_name);
		$file_name = str_replace("+","_",$file_name);
		$cache_file = $this->cache_folder.DIRECTORY_SEPARATOR.$file_name.'.txt';
		
		// Is it in cache
		if(file_exists($cache_file)&& (time()-$this->cache_time < filemtime($cache_file))){
			//s::alert("Loading from cache");
			return file_get_contents($cache_file);
		}else{
			//s::alert("Loading from URL");	
			$url = str_replace(' ','%20',$url);		
			$content = @file_get_contents($url);
			//s::alert("Saving to cache");			
			file_put_contents($cache_file,$content);
			return $content;
		}
	}
	//=====================================================================//
    //  METHOD: fetch_url                                                  //
    //========================== END OF METHOD ============================//
    
	//========================= START OF METHOD ===========================//
	//  METHOD: parse                                                      //
	//=====================================================================//
	/**
	 * @access private
	*/
	private function parse(){
		if($this->data==false){
			$xml = $this->fetch_url($this->url);
			foreach($this->namespaces as $namespace=>$version){
				if(stripos($xml, $namespace)!==false){
					if(stripos($version,'rss')!==false){
						$this->type = 'rss';
						if(stripos($version,'1.0')!==false){
							$this->version = '1.0';
						}
						if(stripos($version,'2.0')!==false){
							$this->version = '2.0';
						}
					}
					if(stripos($version,'atom')!==false){
						$this->type = 'atom';
						if(stripos($version,'1.0')!==false){
							$this->version = '1.0';
						}
					}
				}
			}
			
			if ($this->type == false) {
				throw new RuntimeException('Unsupported format. Supported formats:'.implode(',', $this->namespaces));
			}
			
			$this->data = $xml;
		}
		return $this->data;
	}
	//=====================================================================//
	//  METHOD: parse                                                      //
	//========================== END OF METHOD ============================//
    
    function array_is_assoc($array){
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
	//========================= START OF METHOD ===========================//
    //  METHOD: channels                                                   //
    //=====================================================================//	
	function channels(){
		$rss = $this->get_data(); // Init
		
		$channels = array();
		if(isset($rss['channel'])){
			if($this->array_is_assoc($rss['channel'])==true){
				$channels=array($rss['channel']);
			}else{
			    for($i=0;$i<count($rss['channel']);$i++){
				    $channels[] = $rss['channel'][$i];
			    }
			}
		}
		return $channels;
	}
	//=====================================================================//
    //  METHOD: channels                                                   //
    //========================== END OF METHOD ============================//
    
	//========================= START OF METHOD ===========================//
    //  METHOD: items                                                      //
    //=====================================================================//	
	function items(){
		$rss = $this->parse(); // Init
		$items = array();
		
		if ($this->type=='atom') {
			$data = $this->simplexml_to_array($this->data);
		    utils::alert($data);
		    for($i=0;$i<count($data['entry']);$i++){
		    	$entry = $data['entry'];
		    	if(isset($entry['title']) && $entry['title']!=''){
		    		$item = array();
		    		$item['title']=$entry['title'];
		    	}
		    	
		    }
		    
		}
		
		
		
// 		$channels = $this->channels();
// 		$items = array();
// 		for($i=0;$i<count($channels);$i++){
// 			if(isset($channels[$i]["item"])){
// 				foreach($channels[$i]["item"] as $item){
// 					$items[] = $item;
// 				}
// 			}			
// 		}
		return $items;
	}
	//=====================================================================//
    //  METHOD: items                                                      //
    //========================== END OF METHOD ============================//
    
	//========================= START OF METHOD ===========================//
    //  METHOD: test                                                       //
    //=====================================================================//	
	function test(){
		$data = $this->get_data();
		if(empty($data)==false)return true;
		return false;
	}
	//=====================================================================//
    //  METHOD: test                                                       //
    //========================== END OF METHOD ============================//
    
	
	//========================= START OF METHOD ===========================//
	//  METHOD: simplexml_to_array                                         //
	//=====================================================================//
	function simplexml_to_array ($xml_string) {
		$simplexml = simplexml_load_string($xml_string);
		$json = json_encode($simplexml);
		$array = json_decode($json,TRUE);
		return $array;
	}
	//=====================================================================//
	//  METHOD: simplexml_to_array                                         //
	//========================== END OF METHOD ============================//
	
}
