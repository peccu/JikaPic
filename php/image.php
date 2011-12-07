<?php
/* urlから画像そのもののurlを取り出す．それが画像投稿サイトなら */
$u = $_GET['u'];

/* urlだけで画像のurlがわかるもの */
$thum = getThumbnailUrl($u);
if($thum != ''){
  echo $thum;
  exit();
}

/* urlの内容を取得してきて正規表現で画像のurlを取得するもの */
/* こことcurl.phpを編集すること */

/* urlから画像投稿サイトを判断する */
if(preg_match("/(https?:\/\/([^\/]*)\/.*)/",$u,$matches)){
  //var_dump($matches);
  $type = $matches[2];
}
/* サイト用の取り出しpatternを設定 */
switch($type){
case "movapic.com":
  $pattern = '/<img class="image" src="(http:\/\/image.movapic.com\/pic\/[^.]*\.jpeg)"/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
case "twitpic.com":
  $pattern = '/<img\sclass="photo" id="photo-display" src="(http:\/\/[^"]*?)"/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
case "yfrog.com":
  $pattern = '/id="main_image" src="(http:\/\/.*\.yfrog.com\/[^"]*?)"/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
case "f.hatena.ne.jp":
  $pattern = '/<img id="foto-for-html-tag-.*?"\ssrc="(http:\/\/img\.f\.hatena\.ne\.jp\/images\/[^.]*\.jpg)"/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
case "photozou.jp":
  // フォト蔵
  /* http://photozou.jp/photo/show/355305/40292693 */
  /* http://photozou.jp/photo/photo_only/355305/40292693 */
  /* http://art23.photozou.jp/pub/295/142295/photo/38687084_org.v1276144930.jpg */
  $pattern = '/(http:\/\/.*\.photozou\.jp\/pub\/.*\/photo\/[^.]*\.[^.]*\.jpg)/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
case "picplz.com":
  /*   // <img src="http://picplz-1.s3.amazonaws.com/upload/img/35/2a/03/352a037265d411447595deb31c8758b17935989e_wmeg_00001.jpg?Signature=vq1XZ%2F40MQ6so4hl3YRZT0hEDn4%3D&amp;Expires=1294060209&amp;AWSAccessKeyId=AKIAIUTZUFITIJU4M2YA" width="480" height="640" id="mainImage" class="main-img" alt="人生初ネコ撮り" /> */
  /*   $pattern = '/<img src="(http:\/\/picplz.*?amazonaws.com\/upload\/img\/.*?\.jpg\?Signature=.*?)".*id="mainImage"/'; */
  // "large_url": "http://picplz-1.s3.amazonaws.com/upload/img/35/2a/03/352a037265d411447595deb31c8758b17935989e_wmlg_00001.jpg?Signature=4vjCJO0cmTz16TLNAwccgV1dGd4%3D&Expires=1294060208&AWSAccessKeyId=AKIAIUTZUFITIJU4M2YA",
  $pattern = '/"large_url":"(http:\/\/.*?)",/';
  $no = 1;
  break;
case "plixi.com":
  /* <input type="hidden" name="url" value="http://c0013598.cdn1.cloudfiles.rackspacecloud.com/x2_3fe99af"/> */
  $pattern = '/<input type="hidden" name="url" value="(http:\/\/.*?)"/';
  $no = 1;
  echo "before($u)";
  $u = preg_replace("/http:\/\/plixi.com/","http://m.plixi.com",$u);
  echo "after($u)";
  break;
case "instagr.am":
  /* <meta property="og:image" content="http://distillery.s3.amazonaws.com/media/2011/01/01/9260c5cdf8384d90b91b771cb64123f4_7.jpg"/> */
  $pattern = '/<meta property="og:image" content="(http:\/\/.*)"/';
  $no = 1;
  break;
case "foursquare.com":
  /* <img src="https://img-s.foursquare.com/pix/5NNMQARILGZJBY5OVJEXMW1ZJCKRTF0RZNR22VT1KFLD33LZ.jpg" /> */
  $pattern = '/<img src="(https:\/\/.*\.jpg)"/';
  $no = 1;
  break;
default:
  $pattern = '/<img class="image" src="(http:\/\/image.movapic.com\/pic\/[^.]*\.jpeg)"/';
  /* パターン中の画像URLの場所 */
  $no = 1;
  break;
}
/* URLの中身を取り込む */
if($fp = @fopen("$u","r")){
  $data = "";
  $matches = array("");
  /* 画像のurlのpatternにマッチするまで読み込み，マッチしたものを$matchesに格納 */
  while(
        (!feof($fp))
        && (!preg_match( $pattern,$data .= fgets($fp, 4096), $matches) )
        ) {}
  fclose($fp);
  //echo $data;
  //var_dump($matches);
  /* 取り出せなければURLをもってくる */
  /* 画像投稿とかの画像そのもののurl */
  echo isset($matches[$no]) ? $matches[$no] : $u;
}else{
  echo "Can't open URL($u).";
}

/* urlだけで画像のURLがわかるもの */
function getThumbnailUrl($status_text) {
  //http://blog.irons.jp/2009/12/23/twitter_thumb_url/
  $html = '';
  $patterns = array(
                    // Mobypicture
                    array('/http:\/\/moby\.to\/(\w+)/'
                          , 'http://mobypicture.com/?$1:medium'),

                    /* tweetphoto */
                    /* http://groups.google.com/group/tweetphoto/web/fetch-image-from-tweetphoto-url */
                    array('/(http:\/\/tweetphoto\.com\/(\w+))/'
                          , 'http://TweetPhotoAPI.com/api/TPAPI.svc/imagefromurl?size=big&url=$1'),

                    // 携帯百景
                    array('/http:\/\/movapic\.com\/pic\/(\w+)/'
                          , 'http://image.movapic.com/pic/s_$1.jpeg'),

                    // はてなフォトライフ
                    array('/http:\/\/f\.hatena\.ne\.jp\/(([\w\-])[\w\-]+)\/((\d{8})\d+)/'
                          , 'http://img.f.hatena.ne.jp/images/fotolife/$2/$1/$4/$3.jpg'),

                    // PhotoShare
                    array('/http:\/\/(?:www\.)?bcphotoshare\.com\/photos\/\d+\/(\d+)/'
                          , 'http://images.bcphotoshare.com/storages/$1/thumb180.jpg"'),

                    // PhotoShare の短縮 URL
                    array('/http:\/\/bctiny\.com\/p(\w+)/e'
                          , '\'<img src="http://images.bcphotoshare.com/storages/\' . base_convert("$1", 36, 10) . \'/thumb180.jpg'),

                    // img.ly
                    array('/http:\/\/img\.ly\/(\w+)/'
                          , 'http://img.ly/show/thumb/$1'),

                    // brightkite
                    array('/http:\/\/brightkite\.com\/objects\/((\w{2})(\w{2})\w+)/'
/*                           , 'http://cdn.brightkite.com/$2/$3/$1-feed.jpg'), */
                          , 'http://s3.amazonaws.com/bkcontent/$2/$3/$1.png'),

                    // Twitgoo
                    array('/http:\/\/twitgoo\.com\/(\w+)/'
                          , 'http://twitgoo.com/$1/mini'),

                    // pic.im
                    array('/http:\/\/pic\.im\/(\w+)/'
                          , 'http://pic.im/website/thumbnail/$1'),

                    // youtube
                    array('/http:\/\/www\.youtube\.com\/watch\?v=([\w\-\_]+)/'
                          , 'http://i.ytimg.com/vi/$1/default.jpg'),

                    // imgur
                    array('/http:\/\/imgur\.com\/(\w+)\.jpg/'
                          , 'http://i.imgur.com/$1l.jpg'),
                    // twipple
                    array('/http:\/\/p\.twipple\.jp\/(\w+)/'
                          , 'http://p.twipple.jp/show/orig/$1'),
                    );

  foreach ($patterns as $pattern) {
    if (preg_match($pattern[0], $status_text, $matches)) {
      $url = $matches[0];
      $html = preg_replace($pattern[0], $pattern[1], $url);
      //$html = '<a href="' . $url . '">' . $html . '</a>';
      break;
    }
  }

  return $html;
}


?>
