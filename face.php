<?php
session_start();

require_once 'lib/Facebook/autoload.php';
$fb = new \Facebook\Facebook([
    'app_id' => '*******',
    'app_secret' => '******',
    'default_graph_version' => 'v2.10',
    //'default_access_token' => '{access-token}', // optional

]);

$Login = $fb->getRedirectLoginHelper();
$permissions = ['email'];
try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $Login->getAccessToken();
    }
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        $oAuth2Client = $fb->getOAuth2Client();
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    if (isset($_GET['code'])) {
        header('Location: http://localhost/projetos/loginface/face.php');
    }
    try {
        $profile_request = $fb->get('/me?fields=id,name,first_name,last_name,email,picture.width(200)');
        $profile = $profile_request->getGraphNode()->asArray();
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        header("http://localhost/projetos/loginface/face.php");
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }


    echo"<pre>";
    var_dump( $profile);
    echo"</pre>";

    echo '<img src="'.$profile['picture']['url'].'" alt="" title="">';


    $logoff = filter_input(INPUT_GET, 'sair', FILTER_DEFAULT);
    if (isset($logoff) && $logoff == 'true'):
        session_destroy();
        header("Location: http://localhost/projetos/loginface/face.php");
    endif;
    echo '<a href="?sair=true">Sair</a>';

    //var_dump($_SESSION);

}else {
    $loginUrl = $Login->getLoginUrl('http://localhost/projetos/loginface/face.php', $permissions);
    echo '<a href="' . $loginUrl . '">Entrar com facebook</a>';

    //var_dump($_SESSION);

}

?>
