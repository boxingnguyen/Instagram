<?php
include "../../../vendor/Instagram/src/Instagram.php";
use MetzWeb\Instagram\Instagram;
$instagram = new Instagram(array(
		'apiKey'      => 'f31c3725215449c6bde2871932e7bc15',
		'apiSecret'   => '0a64babe62df4bba919dcd685e85eead',
		'apiCallback' => 'http://192.168.33.20/Instagram/detail.php',
		'scope'             => array( 'likes', 'comments', 'relationships','basic','public_content','follower_list' )
		
));
// ------------------------------------------------------------------------
$code = $_GET['code'];
$data = $instagram->getOAuthToken($code);// set user access token

////https://api.instagram.com/v1/users/self/?access_token=ACCESS-TOKEN
// echo $data->user->username;
// echo "<pre>";
// print_r($data);
// echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/self/media/recent/?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $media = $instagram->getUserMedia();
// echo "<pre>";
// print_r($media);
// echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/{user-id}/media/recent/?access_token=ACCESS-TOKEN
$instagram->setAccessToken($data);
$id = '2124049456';  //3579361643(hang); 2996660725(nhi), 2124049456(q.anh), 3723129539(t.anh)
$mediaId = $instagram->getUserMedia($id);
echo "<pre>";
print_r($mediaId);
echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/self/media/liked?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $mediaLike = $instagram->getUserLikes();
// echo "<pre>";
// print_r($mediaLike);
// echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/search?q=jack&access_token=ACCESS-TOKEN
//sua thư viện
// $instagram->setAccessToken($data);
// $name = 'ylangincense';
// $search = $instagram->searchUser($name);
// echo "<pre>";
// print_r($search);
// echo "</pre>";

// ---------------------------Relatioships---------------------------------------------
// //https://api.instagram.com/v1/users/self/follows?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $follows = $instagram->getUserFollows();
// echo "<pre>";
// print_r($follows);
// echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/self/followed-by?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $follows_by = $instagram->getUserFollower();
// echo "<pre>";
// print_r($follows_by);
// echo "</pre>";

// ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/self/requested-by?access_token=ACCESS-TOKEN
//khong tim thay API 
// $instagram->setAccessToken($data);
// $follows_by = $instagram->getUserFollower();
// echo "<pre>";
// print_r($follows_by);
// echo "</pre>";


// // ------------------------------------------------------------------------
// // //https://api.instagram.com/v1/users/{user-id}/relationship?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $relationship = $instagram->getUserRelationship('2996660725');
// echo "<pre>";
// print_r($relationship);
// echo "</pre>";

// // ------------------------------------------------------------------------
// //https://api.instagram.com/v1/users/{user-id}/relationship?access_token=ACCESS-TOKEN
// ACTION	follow | unfollow | approve | ignore
// $instagram->setAccessToken($data);
// $relationshiPost = $instagram->modifyRelationship('follow','2996660725');
// echo "<pre>";
// print_r($relationshiPost);
// echo "</pre>";

// ----------------------------Media--------------------------------------------
// https://api.instagram.com/v1/media/{media-id}?access_token=ACCESS-TOKEN
//media_id : // //https://api.instagram.com/v1/users/self/media/recent/?access_token=ACCESS-TOKEN or https://api.instagram.com/v1/users/{user-id}/media/recent/?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $mediaId = '1338107381783777196_2124049456';
// $media = $instagram->getMedia($mediaId);
// echo "<pre>";
// print_r($media);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/shortcode/D?access_token=ACCESS-TOKEN
// k co API, loi
//media_id : // //https://api.instagram.com/v1/users/self/media/recent/?access_token=ACCESS-TOKEN or https://api.instagram.com/v1/users/{user-id}/media/recent/?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $mediaId = '1338107381783777196_2124049456';
// $media = $instagram->getMedia($mediaId);
// echo "<pre>";
// print_r($media);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/search?lat=48.858844&lng=2.294351&access_token=ACCESS-TO
//vi do and kinh độ: https://vie.timegenie.com/latitude_longitude/country/vn
//edit library
// $instagram->setAccessToken($data);
// $searchmedia = $instagram->searchMedia($lat = '21.03333', $lng = '105.85000');
// echo "<pre>";
// print_r($searchmedia);
// echo "</pre>";

// -------------------------------Comment-----------------------------------------
//https://api.instagram.com/v1/media/{media-id}/comments?access_token=ACCESS-TOKEN
//edit library
// $instagram->setAccessToken($data);
// $comment = $instagram->getMediaComments('1338094859756305059_2124049456');
// echo "<pre>";
// print_r($comment);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/search?lat=48.858844&lng=2.294351&access_token=ACCESS-TO
//vi do and kinh độ: https://vie.timegenie.com/latitude_longitude/country/vn
//edit library
// $instagram->setAccessToken($data);
// $searchmedia = $instagram->searchMedia($lat = '21.03333', $lng = '105.85000');
// echo "<pre>";
// print_r($searchmedia);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/search?lat=48.858844&lng=2.294351&access_token=ACCESS-TO
//vi do and kinh độ: https://vie.timegenie.com/latitude_longitude/country/vn
// $instagram->setAccessToken($data);
// $commentMedia = $instagram->addMediaComment('1338094513315303314_2124049456','Quyen Anh ham');
// echo "<pre>";
// print_r($commentMedia);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/search?lat=48.858844&lng=2.294351&access_token=ACCESS-TO
//vi do and kinh độ: https://vie.timegenie.com/latitude_longitude/country/vn
// get comment_id == post comment_id
// $instagram->setAccessToken($data);
// $deleteComment = $instagram->deleteMediaComment('1338094513315303314_2124049456','17865685030036296');
// echo "<pre>";
// print_r($deleteComment);
// echo "</pre>";

// -------------------------------Likes-----------------------------------------
// https://api.instagram.com/v1/media/{media-id}/likes?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $likes = $instagram->getMediaLikes('1338094859756305059_2124049456');
// echo "<pre>";
// print_r($likes);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/1338094513315303314_2124049456/likes?access_token=2996660725.f31c372.87f532027e034aacb3ae99ea50365473
// $instagram->setAccessToken($data);
// $likeMedia = $instagram->likeMedia('1338094513315303314_2124049456');
// echo "<pre>";
// print_r($likeMedia);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/media/search?lat=48.858844&lng=2.294351&access_token=ACCESS-TO
// $instagram->setAccessToken($data);
// $deleteLike = $instagram->deleteLikedMedia('1338094513315303314_2124049456');
// echo "<pre>";
// print_r($deleteLike);
// echo "</pre>";

// -------------------------------Tags-----------------------------------------
// https://api.instagram.com/v1/media/{media-id}/likes?access_token=ACCESS-TOKEN
// edit library
// $instagram->setAccessToken($data);
// $tags = $instagram->getTag('love');
// echo "<pre>";
// print_r($tags);
// echo "</pre>";

// ------------------------------------------------------------------------
//https://api.instagram.com/v1/tags/{tag-name}/media/recent?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $tagMedia = $instagram->getTagMedia('ngon');
// echo "<pre>";
// print_r($tagMedia);
// echo "</pre>";

// ------------------------------------------------------------------------
// https://api.instagram.com/v1/tags/search?q=snowy&access_token=ACCESS-TOKEN
// edit library
// $instagram->setAccessToken($data);
// $searchTags = $instagram->searchTags('ngon');
// echo "<pre>";
// print_r($searchTags);
// echo "</pre>";

// -------------------------------Locations-----------------------------------------
// https://api.instagram.com/v1/locations/{location-id}?access_token=ACCESS-TOKEN
// location_id = https://api.instagram.com/v1/users/{user-id}/media/recent/?access_token=ACCESS-TOKEN
// $instagram->setAccessToken($data);
// $locations = $instagram->getLocation('212979535');
// echo "<pre>";
// print_r($locations);
// echo "</pre>";












