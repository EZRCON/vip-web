<?php

if ($user->loggedIn()) {

	// Auf Loginseite weiterleiten:	
		header('Location: index.php');
		die();
}


$settings['title'] = 'Login | '.$settings['title'];

$settings['jsAdd'] = '<script type="text/javascript">
jQuery(document).ready(function() {
(function(){
var lTimeoutField = document.getElementById("login_timeout"),
    lTimeout = lTimeoutField ? +lTimeoutField.innerHTML : 0;

if (lTimeout) {
    var lTimer = window.setInterval (
        function() {
            if (lTimeout > 0) {
                lTimeoutField.innerHTML = lTimeout;
                lTimeout--;
            } else {
                window.clearInterval(lTimer);
                var lDiv = document.getElementById("wait_box");
                if (lDiv) {
                    lDiv.parentNode.removeChild(lDiv);
                    return true;
                }
            }
        },
        1000 );
}})();

});
</script>
';
$add = '';
$tmpMail = '';
$addClass = '';

if (!isset($_GET['err'])) $_GET['err'] = 0;

if ($_GET['err'] == 1) {
	
	$err = $user->getErrorString();
	$arr = explode('-', $err);
	$tmpMail = $arr[1];
	$arr2 = explode('&', $arr[0]);
	
	$add2 = 'Username or password is wrong!';
	if ($arr2[1] == 'BLOCKED;') $add2 = 'The login attempt was blocked!';
	if ($arr2[1] == 'DOUBLE;') $add2 = 'This user is already logged in!';
	
	$add = '<div class="alert alert-danger">
	'.$add2.' <div id="wait_box">Please wait <span id="login_timeout">5</span> seconds until the next login attempt.</div>
	</div>';
	$addClass = ' has-error';
}

// Inhalt:
$settings['left'] = '
        <div class="row">
            <div class="col-md-6 col-md-offset-3" style="text-align:left">
			<h1><span>Login</span></h1>

                    <div class="panel-body">'.$add.'
                        <form role="form" method="post" action="action.php?action=login" autocomplete="off">
                            <fieldset>
                                <div class="form-group'.$addClass.'">
                                    <input class="form-control" placeholder="Web user" name="email" type="text" value="'.$tmpMail.'" />
                                </div>
                                <div class="form-group'.$addClass.'">
                                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                </div>
                                <input type="submit" class="btn btn-lg btn-info btn-block" value="Einloggen" />
                            </fieldset>
                        </form>
                    </div>
            </div>
        </div>
	';

?>
