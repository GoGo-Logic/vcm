<?php

include ("inc/inc_version.php");
include_lcm ("inc_session");

// Determine where we want to fallback after the operation
if ($url)
	$cible = new Link($url);
else
	$cible = new Link('/');

// Replay the cookie to renew lcm_session
if ($change_session == 'oui') {
	if (verifier_session($lcm_session)) {
		// Attention : seul celui qui a le bon IP a le droit de rejouer,
		// ainsi un eventuel voleur de cookie ne pourrait pas deconnecter
		// sa victime, mais se ferait deconnecter par elle.
		if ($author_session['hash_env'] == hash_env()) {
			$author_session['ip_change'] = false;
			$cookie = creer_cookie_session($author_session);
			supprimer_session($lcm_session);
			lcm_setcookie('lcm_session', $cookie);
		}
		@header('Content-Type: image/gif');
		@header('Expires: 0');
		@header("Cache-Control: no-store, no-cache, must-revalidate");
		@header('Pragma: no-cache');
		@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@readfile('ecrire/img_pack/rien.gif');
		exit;
	}
}


// Attemp to connect via auth_http
// [ML] TODO
if ($essai_auth_http AND !$ignore_auth_http) {
	include_local ("inc-login.php3");
	auth_http($cible, $essai_auth_http);
	exit;
}

// Attempt to logout
if ($logout) {
	include_lcm('inc_session');
	verifier_visiteur();

	if ($author_session['username'] == $logout) {
		if ($lcm_session) {
			zap_sessions($author_session['id_author'], true);
			lcm_setcookie('lcm_session', $lcm_session, time() - 3600 * 24);
		}
		if ($PHP_AUTH_USER AND !$ignore_auth_http) {
			include_local ("inc-login.php"); // [ML] XXX
			auth_http($cible, 'logout');
		}
		unset ($author_session);
	}

	$test = 'yes=' . $author_session['login'];

	if (!$url)
		@Header("Location: ./lcm_login.php" . '?' . $test);
	else
		@Header("Location: $url");
	exit;
}


// If the user logins with bonjour=oui (hello=yes), we try to put
// a cookie and then go to lcm_login.php which will try to make a 
// diagnostic if necessary.
// [ML] echec == failure
if ($test_echec_cookie == 'oui') {
	lcm_setcookie('lcm_session', 'test_echec_cookie');
	$link = new Link("lcm_login.php?var_echec_cookie=oui");
	$link->addVar("var_url", $cible->getUrl());
	@header("Location: ".$link->getUrl());
	exit;
}

// Attempt to login
unset ($cookie_session);
if ($essai_login == 'oui') {
	// Recuperer le login en champ hidden
	if ($session_login_hidden AND !$session_login)
		$session_login = $session_login_hidden;

	$login = $session_login;
	$pass = $session_password;

	// Try different authentication methods, starting with "db" (database)
	$auths = array('db');

	// Test if LDAP is available
	include_config('inc_connect'); 
	if ($ldap_present) $auths[] = 'ldap';

	// Add other methods here (with associated inc/inc_auth_NAME.php)
	// ...

	$ok = false;
	reset($auths);
	while (list(, $nom_auth) = each($auths)) {
		include_lcm('inc_auth_'.$nom_auth);
		$classe_auth = 'Auth_'.$nom_auth;
		$auth = new $classe_auth;
		if ($auth->init()) {
			// Essayer les mots de passe md5
			$ok = $auth->verifier_challenge_md5($login, $session_password_md5, $next_session_password_md5);
			// Sinon essayer avec le mot de passe en clair
			if (!$ok && $session_password) $ok = $auth->verifier($login, $session_password);
		}
		if ($ok) break;
	}

	if ($ok) $ok = $auth->lire();

	if ($ok) {
		$auth->activate();

		if ($auth->login AND $auth->statut == 'admin') // force cookies for admins
			$cookie_admin = "@".$auth->login;

		$query = "SELECT * FROM lcm_author WHERE username='".addslashes($auth->login)."'";
		$result = lcm_query($query);
		if ($row_author = lcm_fetch_array($result))
			$cookie_session = creer_cookie_session($row_author);

		$cible->addVar('bonjour','oui');
	} else {
		$cible = new Link("lcm_login.php");

		$cible->addVar('var_login', $login);
		$cible->addVar('var_url', urldecode($url));

		if ($session_password || $session_password_md5)
			$cible->addVar('var_erreur', 'pass');
	}
}


// Set an administrative cookie?
// [ML] Not very useful I think
if ($cookie_admin == 'non') {
	lcm_setcookie('lcm_admin', $lcm_admin, time() - 3600 * 24);
	$cible->delVar('var_login');
	$cible->addVar('var_login', '-1');
} else if ($cookie_admin AND $lcm_admin != $cookie_admin) {
	lcm_setcookie('lcm_admin', $cookie_admin, time() + 3600 * 24 * 14);
}

// Set a session cookie?
if ($cookie_session) {
	if ($session_remember == 'yes')
		lcm_setcookie('lcm_session', $cookie_session, time() + 3600 * 24 * 14);
	else
		lcm_setcookie('lcm_session', $cookie_session);

	$prefs = ($row_author['prefs']) ? unserialize($row_author['prefs']) : array();
	$prefs['cnx'] = ($session_remember == 'yes') ? 'perma' : '';

	lcm_query("UPDATE lcm_author 
				SET prefs = '" . addslashes(serialize($prefs)) . "' 
				WHERE id_author = " . $row_author['id_author']);
}

// Change the language of the private area (or login)
if ($var_lang_lcm) {
	include_lcm('inc_lang');
	include_lcm('inc_session');
	$verif = verifier_visiteur();

	if (changer_langue($var_lang_lcm)) {
		lcm_setcookie('lcm_lang', $var_lang_lcm, time() + 365 * 24 * 3600);

		// [ML] Strange, if I don't do this, id_auteur stays null,
		// and I have no idea where the variable should have been initialized
		$id_auteur = $GLOBALS['author_session']['id_author'];

		// Save language preference only if we are installed
		if (@file_exists('inc/config/inc_connect.php')) {
			include_lcm('inc_admin');

			if (verifier_action_auteur('var_lang_lcm', $valeur, $id_auteur)) {
				lcm_query("UPDATE lcm_author SET lang = '".addslashes($var_lang_lcm)."' WHERE id_author = ".$id_auteur);
				$author_session['lang'] = $var_lang_lcm;
				ajouter_session($author_session, $lcm_session);	// enregistrer dans le fichier de session
			}
		}

		$cible->delvar('lang');
		$cible->addvar('lang', $var_lang_lcm);
	}
}

// Redirection
// Under Apache, cookies with a redirection work
// Else, we do a HTTP refresh
if (ereg("^Apache", $SERVER_SOFTWARE)) {
	@header("Location: " . $cible->getUrl());
}
else {
	@header("Refresh: 0; url=" . $cible->getUrl());
	echo "<html><head>";
	echo "<meta http-equiv='Refresh' content='0; url=".$cible->getUrl()."'>";
	echo "</head>\n";
	echo "<body><a href='".$cible->getUrl()."'>"._T('navigateur_pas_redirige')."</a></body></html>";
}

?>
