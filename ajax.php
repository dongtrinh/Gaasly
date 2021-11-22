<?php
set_time_limit(900000);
ini_set('memory_limit','1024M');
if (isset($_POST['action']) && $_POST['action'] == 'getxml' && isset($_POST['url']))
{
    $crawler = new TCT_Crawler();
    $crawler->add_sitemap($_POST['url']);
    echo json_encode($crawler->_urls);
    die;
}
if (isset($_POST['action']) && $_POST['action'] == 'getresult' && isset($_POST['url']))
{
    $dataarray = $_POST['dataarray'];
    echo get_result_by_url($_POST['url'],$dataarray);
    die;
}
function check_back_link($remote_url, $your_link) {
  $match_pattern = preg_quote(rtrim($your_link, "/"), "/");
  $pos = strpos($your_link, 'www.');
  if ($pos === false) {
    $your_link2 = str_replace('http://', 'http://www.', $your_link);
    $your_link2 = str_replace('https://', 'https://www.', $your_link2);
  }else{
    $your_link2 = str_replace('http://www.', 'http://', $your_link);
    $your_link2 = str_replace('https://www.', 'https://', $your_link2);
  }
  $match_pattern2 = preg_quote(rtrim($your_link2, "/"), "/");
  //var_dump($match_pattern);
  $found = false;
  if($handle = @fopen($remote_url, "r")){
    while(!feof($handle)){
      $part = fread($handle, 1024);
      if(preg_match("/<a(.*)href=[\"']".$match_pattern."(\/?)[\"'](.*)>(.*)<\/a>/", $part)){
        $found = true;
        break;
      }elseif(preg_match("/<a(.*)href=[\"']".$match_pattern2."(\/?)[\"'](.*)>(.*)<\/a>/", $part)){
        $found = true;
        break;
      }
    }
    fclose($handle);
  }
  return $found;
}

function get_result_by_url($url,$dataarray){

    $current_domain = parse_url($url, PHP_URL_HOST);
    $current_domain = str_replace('www.', '', $current_domain);
    $contenthtml = file_get_contents($url);
    $geth1 = '';
    $getarrayh1 = (array)geth1111($contenthtml);
    if(count($getarrayh1) == 1){
        $geth1 = strip_tags($getarrayh1[0]);
    }elseif(count($getarrayh1) == 0){
        $geth1 = '<span class="too-many-headlines">Headline (h1) missing, every page should have one headline (h1)</span>';
    }else{
        $geth1 = '<span class="too-many-headlines">Too many headlines (h1), one headline (h1) per page is the rule</span>';
    }
    $txt_link_in = '0 inbound link';
    $numberinboundlink = 0;
    $link_in_separated = '';
    //var_dump($dataarray);
    //$dataarray = json_decode($dataarray);
    if(is_array($dataarray) && count($dataarray) > 0){
        //var_dump(count($dataarray));
        foreach ($dataarray as $key_a => $value_a) {
            //var_dump($value_a.'||'.$url);
            if($value_a != $url){
                $aaazz = check_back_link(trim($value_a),trim($url));
                //var_dump($aaazz);
                if ($aaazz) {
                    $numberinboundlink++;
                    $link_in_separated .= '<br>'.$value_a;
                }else{
                    //$numberinboundlink++;
                    //$link_in_separated .= '<br>'.$value_a.'||'.$url;
                }
            }
            
        }
    }
    if($numberinboundlink > 0){
        $txt_link_in = '<div class="show_incoming_links"><strong>'.$numberinboundlink.'</strong> inbound links</div><div class="wrap_link">'.$link_in_separated.'</div>';
    }else{
        //$txt_link_in = '<div class="show_incoming_links">'.$link_in_separated.'</div>';
    }
    
    return '<tr><td>' . $geth1 . '</td><td>'.$txt_link_in.'</td><td>' . $url . '</td></tr>';
}
class TCT_Crawler
{
    //protected $_sitemaps = null;
    public $_urls = null;
    public $_numberno = 0;
    function __construct( ) {
        $this->_urls = [];
    }
    function add_sitemap($sitemapurl)
    {
        //var_dump($sitemapurl);
        $iii = 0;
        $current_domain = parse_url($sitemapurl, PHP_URL_HOST);
        $current_domain = str_replace('www.', '', $current_domain);
        /*$dom = new DOMDocument();
        $arraylink = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sitemapurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $http_return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ('200' != $http_return_code)
        {
            return false;
        }*/
        $xml = simplexml_load_file($sitemapurl, 'SimpleXMLElement', LIBXML_NOCDATA);

        //$xml = new SimpleXMLElement($content);

        if (!$xml)
        {
            return false;
        }

        switch ($xml->getName())
        {
            case 'sitemapindex':
                foreach ($xml->sitemap as $sitemap)
                {
                    $this->add_sitemap($sitemap->loc);
                }
            break;

            case 'urlset':
                foreach ($xml->url as $url)
                {
                    $stra = str_replace(array("//<![CDATA[","//]]>"),"",$url->loc);
                    $this->add_url( $stra );
                }
            break;

            default:
            break;
        }
        //var_dump($arraylink);
        
    }
    public function add_url( $url ) {

        if ( ! in_array( $url, $this->_urls ) ) {
            $this->_urls[] = $url;
        }

    }
}
function get_title($url)
{
    $str = file_get_contents($url);
    if (strlen($str) > 0)
    {
        $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
        preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title); // ignore case
        return $title[1];
    }
}
function getDescription($url)
{
    $tags = get_meta_tags($url);
    return @($tags['description'] ? $tags['description'] : "NULL");
}
function getDescription_from_content($contenthtml)
{
    $description_first = '';
    preg_match('/(?<=\<[Mm][Ee][Tt][Aa]\s[Nn][Aa][Mm][Ee]\=\"[Dd]escription\" content\=\")(.*?)(?="\s*?\/?\>)/U', $contenthtml, $description);
    if(isset($description[0])){
        $description_first = $description[0];
    }
    return $description_first;
}
function get_page_title_by_contenthtml($contenthtml)
{
    // $str = file_get_contents($url);
    if (strlen($contenthtml) > 0)
    {
        $str = trim(preg_replace('/\s+/', ' ', $contenthtml)); // supports line breaks inside <title>
        preg_match("/\<title\>(.*)\<\/title\>/i", $contenthtml, $title); // ignore case
        return $title[1];
    }
}
function getTextBetweenTags($string, $tagname) {
    preg_match( '|<h[^>]+>(.*)</h[^>]+>|iU', $string, $matches );
   //$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
   //preg_match($pattern, $string, $matches);
   //var_dump($matches);
   //return $matches;
   if(isset($matches[1])){
        return $matches[1];
    }else{
        return '';
    }
   
}
function get_page_h1_by_contenthtml($contenthtml)
{
    // $str = file_get_contents($url);
    if (strlen($contenthtml) > 0)
    {
        $str = trim(preg_replace('/\s+/', ' ', $contenthtml)); // supports line breaks inside <title>
        preg_match("/\<h1\>(.*)\<\/h1\>/i", $contenthtml, $title); // ignore case
        if (isset($title[1]))
        {
            return $title[1];
        }
        else
        {
            return '';
        }

    }
}
function geth1111($html)
{
    $linkArray = array();
    if (preg_match_all('|<\s*h[1](?:.*)>(.*)</\s*h|Ui', $html, $matches, PREG_SET_ORDER))
    {
        foreach ($matches as $match){
            $linkArray[] = $match[1];
        }
    }
    return $linkArray;
}
function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
}
function linkExtractor_new($html, $current_domain,$fullurl)
{
    $linkArray = array();
    if (preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>/i', $html, $matches, PREG_SET_ORDER))
    {
        foreach ($matches as $match)
        {
            $getfirst = substr($match[1], 0, 1);
            if ($match[1] == '#' || $match[1] == '' || $getfirst == '#' || $match[1] == $fullurl)
            {
                continue;
            }
            if (strpos($match[1], $current_domain) !== false)
            {
                if(get_http_response_code($match[1]) != "200"){
                    //echo "error";
                }else{
                    if (check_back_link($match[1],$fullurl)) {
                      $linkArray['in'][] = $match[1];
                    } else {
                      //echo 'link NOT found';
                    }
                    /*if(strpos($fullurl, $contenthtml_new) !== false){
                        $linkArray['in'][] = $match[1];
                    }*/
                }
                //if (str_contains($current_domain, $match[1])) {
                //continue;
                
                //$linkArray['in'][] = $match[1];
            }else{
                 $linkArray['out'][] = $match[1];
            }
            //$linkArray[] = $match[1];
            //array_push($linkArray, array($match[1], strip_tags($match[2])));
            
        }
    }
    return $linkArray;
}
function linkExtractor($html, $current_domain)
{
    $linkArray = array();
    if (preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>/i', $html, $matches, PREG_SET_ORDER))
    {
        foreach ($matches as $match)
        {
            $getfirst = substr($match[1], 0, 1);
            if ($match[1] == '#' || $match[1] == '' || $getfirst == '#')
            {
                continue;
            }
            if (strpos($match[1], $current_domain) !== false)
            {
                //if (str_contains($current_domain, $match[1])) {
                continue;
            }
            $linkArray[] = $match[1];
            //array_push($linkArray, array($match[1], strip_tags($match[2])));
            
        }
    }
    return $linkArray;
}
?>