<?php

class scrapTiktok {

    public $proxy = '';

    public function __construct() {
        
    }


    /**
     * Get user complete profile data
     * @param string $url url from tiktok containing account name
     * @param boolean $simplaeFormat optional formating result to a simpler array
     * @return array
     */
    function getProfile($url, $simpleFormat=false) 
    {
        $data = $this->getContent($url);

        //getting profile info
        if( isset($data['props']['pageProps'])){
            $data = $data['props']['pageProps'];
        }else $data = null;

        if($simpleFormat && $data){
            $formated = [
                'profile' => $this->formatProfile($data),
                'latestPost' => $this->getLatestPost($data)
            ];

            return $formated;

        }else return $data;
    }


    /**
     * Get tiktok post detail data 
     * @param string $url url from tiktok containing posted feed
     * @param boolean $simplaeFormat optional formating result to a simpler array
     * @return array
     */
    function getPost($url, $simpleFormat=false) 
    {
        
        $data = $this->getContent($url);

        //getting post info
        if( isset($data['props']['pageProps'])){
            $data = $data['props']['pageProps'];
        }else $data = null;

        if($simpleFormat && $data){
            $data = $this->formatPost($data);
        }

        return $data;
        
    }


    /**
     * Get tiktok hashtag detail data 
     * @param string $url url from tiktok containing hahstag posts
     * @return array
     */
    function getHashtag($url) 
    {
        
        $data = $this->getContent($url);

        //getting post info
        if( isset($data['props']['pageProps'])){
            $data = $data['props']['pageProps'];
        }else $data = null;

        return $data;
        
    }


    /**
     * Formating raw data to a simpler profile data
     * @param array $data array rows from profiles content
     * @return array
     */
    function formatProfile($data)
    {
        $profile = [];

        //simplify profile 
        $profile = $data['userInfo'];

        return $profile;
    }

    
    /**
     * Formating raw data to a simpler post detail data
     * @param array $data array rows from profiles content
     * @return array
     */
    function formatPost($data)
    {
        $post = [];

        //simplify post 
        $post = $data['itemInfo']['itemStruct'];

        return $post;
    }


    /**
     * Get 12 latest post from a profile
     * @param array $data array rows from profiles content
     * @return array
     */
    function getLatestPost($data)
    {
        $latestPost = [];

        $latestPost = $data['items'];
        
        return $latestPost;
    }


    /**
     * Get latest comment from a post
     * @param array $data array row from post content
     * @return array
     */
    function getLatestComment($data)
    {
        $latestComment = [];

        $rawLatesComment = $data['graphql']['user']['edge_owner_to_timeline_media']['edges'];
        foreach($rawLatesComment as $cmt){
            $cmt = $cmt['node'];
            $latestComment[] = [
                'id' => $cmt['id'],
                'time' => $cmt['taken_at_timestamp'],
                'time_formated' => date("Y-m-d H:i:s", $cmt['taken_at_timestamp'])
            ];
        }

        //adding formated data to result
        $data['formated']['latestComment'] = $latestComment;

        return $data;
    }


    /**
     * Getting tiktok content source and sanitize the data layer info
     * @param string $url url from tiktok containing account or post url
     * @return array
     */
    function getContent($url)
    {
        $rawdata = null;

        //requestion content
        if($content = $this->http_request($url)) {
            #echo $content;
            
            //scrapping data layer info
            $regx = '/(?<=id="__NEXT_DATA__" )(.*?)(?=<\/script>)/';
            preg_match_all($regx, $content, $output_array, 0);
            #echo is_countable($output_array);
            #echo count($output_array[0]).'<hr />';

            if( count((is_array($output_array)?$output_array[0] : [])) ) {
                $regx = '/(?<=>).*/';
                preg_match_all($regx, $output_array[0][0], $output_array, 0);

                if( count((is_array($output_array)?$output_array : [])) ) {
                    #echo 'here 1';
                    if(count($output_array[0]) > 0){ 
                        #echo 'here 2';
                        //decoding data layer
                        $rawdata = isset($output_array[0][0]) ? json_decode($output_array[0][0], TRUE) : [];
                    }
                }
            }
        }

        return $rawdata;
    }


    /**
     * Requesting content by simulating browser access using curl
     * @param string $url url from tiktok containing account or post url
     * @return string
     */
    private function http_request($url) {
        // prepare curl
        $ch = curl_init(); 

        // assign url 
        curl_setopt($ch, CURLOPT_URL, $url);
        
        // setup user agent    
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getRandomUserAgent());

        $this->getRandomProxies();
        if($this->proxy) curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        
        #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = curl_exec($ch); 

        // close curl 
        curl_close($ch);      

        // return result
        return $output;
    }

    /**
     * Randomize user agent of browser
     * @return string
     */
    private function getRandomUserAgent() {
        $userAgents = [
            "Mozilla/5.001 (windows; U; NT4.0; en-us) Gecko/25250101",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.4) Gecko/20070515 Firefox/2.0.0.4",
            "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.132 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36", 
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36", 
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36", 
            "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36", 
            "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36", 
        ];
        $random = rand(0,count($userAgents)-1);
        
        return $userAgents[$random];
    }

    function getRandomProxies()
    {
        $proxies = [
            
        ];
        $this->proxy = rand(0,count($proxies)-1);

    }

    /**
     * debugging utility
     * @param array $data any kind of array
     */
    function echoPre($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
?>