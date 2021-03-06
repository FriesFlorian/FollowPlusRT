<?php

// autoloader
require 'vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

define('CONSUMER_KEY','');
define('CONSUMER_SECRET','');

$result = "";

// handle post request
if(isset($_POST['twitter_username']) && htmlspecialchars($_POST['twitter_username']) != '')
{
	$access_token = $_SESSION['access_token'];

	$connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET);

	$statuses = $connection->get("statuses/user_timeline", array(
		"count" => 200,
		"exclude_replies" => false,
		"include_rts" => true,
		"screen_name" => htmlspecialchars($_POST['twitter_username'])
	));

	if(is_array($statuses))
	{
		$positives = 0;

		foreach($statuses as $tweet)
		{
			if(
				isset($tweet->retweeted_status) && 
				strstr(strtolower($tweet->retweeted_status->text), 'follow') &&
				strstr(strtolower($tweet->retweeted_status->text), 'rt')
			)
			{
				$positives++;
			}
		}
		$result  = "Score du compte ";
		$result .= htmlspecialchars($_POST['twitter_username']);
		$result .= '<span id="frt">' . $positives/2 . ' FRT';
	}
	else
	{
		$result = "Cet utilisateur n'existe pas.";
	} 
		
}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<!--<link rel="icon" type="image/png" href="favicon.png"> TODO -->
		<title>FollowPlusRT</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, minimum-scale=1.0">
		<meta name="description" content="Outil calculant le pourcentage de retweets contenant les mots-clefs 'follow' et 'rt' d'un compte Twitter.">
		<meta name="keywords" content="twitter, follow, rt">
		<meta property="og:title" content="FollowPlusRT">
		<meta property="og:description" content="Outil calculant le pourcentage de retweets contenant les mots-clefs 'follow' et 'rt' d'un compte Twitter.">
		<meta property="og:type" content="website">
		<meta property="og:url" content="http://frt.georgeslasaucisse.fr/">
		<!--<meta property="og:image" content="http://"> TODO -->
		<style>
			a{color:#FFFFFF;}
			body{background-color:#000000;color:#FFFFFF;font-family:Helvetica;text-align:center;}
			body *{padding:1%;}
			#frt{color:#FF0000;}
		</style>
	</head>
	<body itemscope itemtype="http://schema.org/WebPage">
		<noscript><b>ATTENTION : Le JavaScript est desactivé sur votre navigateur. Votre navigation sur le site risque de ne pas être optimale.</b></noscript>
		<header>
			<h1>FollowPlusRT</h1>
			<h2>Outil calculant le pourcentage de retweets contenant les mots-clefs 'follow' et 'rt' d'un compte Twitter.</h2>
		</header>
		<form method="post">
			<input type="text" id="twitter_username" name="twitter_username" title="twitter_username" placeholder="Nom d'utilisateur Twitter">
			<input type="submit" value="Calculer">
		</form>
		<?= '<span id="result">'.$result.'</span>'	?>
		<section>
			<p>Il n'est pas rare de voir passer des concours sur Twitter. Ceux-ci tournent souvent autour du même principe : il faut partager ("retweeter" ou "rt") le concours et suivre ("follow") le compte organisateur du-dit concours.
				En échange de quelques clics, le participant s'offre alors une chance de remporter un lot qui pourrait lui plaire. L'organisateur voit quant à lui l'occasion de davantage faire connaître son compte.
				La formulation "RT+Follow" et ses dérivés sont devenus une sorte de norme pour facilement identifier ces concours. Cela entraîne un problème : l'apparition de participations "automatisées".</p>
			<p>Des comptes sont programmés pour détecter tout tweet contenant les mots-clés "follow", "rt" et dérivés, et participer aux-dits concours, parfois en partageant le tweet et en suivant le compte dans la seconde ou est posté le tweet.
				En cas de victoire, les participants comme l'organisateur peuvent être frustrés de voir le lot est accordé à ce type de compte, bien que certains propriétaires de ce type de compte refusent les lots qui ne les intéressent pas.
				L'organisateur ne tire quant à lui qu'un gain quasi nul de la participation de ces comptes. S'il n'est pas notifié (en cas de victoire au concours), le compte automatisé ne réagit pas.
				Le "Follow" n'amène rien vu que le compte ne lira pas les nouveaux tweets du compte organisateur. Le "RT" n'amène rien car les utilisateurs de Twitter suivent rarement un compte leur partageant dix concours à la seconde.</p>
			<p>FollowPlusRT tente de trouver une solution à ce problème en proposant un critère de participation aux concours de ce type : l'indice FRT. Derrière ce nom barbare se cache en fait un algorithme très simple.
				Les 200 derniers tweets d'un compte Twitter donné sont analysés. Si FollowPlusRT trouve un retweet contenant les mots-clés "RT" et "Follow", il considère qu'il s'agit d'une participation à un concours.
				L'indice FRT est ensuite calculé, il s'agit du pourcentage de participations à des concours trouvé dans ces 200 derniers tweets. Plus il est élevé, plus il est probable qu'il s'agisse d'un compte automatisant ses participations.</p>
			<p>Il peut donc être envisageable de comptabiliser ce critère pour l'organisation de futurs concours, et de n'autoriser que des participations de comptes ayant un indice inférieur à celui demandé.
				Cela n'empêchera pas les comptes automatisés de participer, mais si l'un d'entre eux est désigné gagnant, il peut être disqualifié du fait de son indice trop élevé, et donc laisser sa chance à un autre compte.
				Ces comptes écartés, la probabilité que le gagnant d'un concours soit bien humain augmente, le lot lui reviendra, et la frustration de voir un automate repartir avec le lot s'éteindra.</p>
		</section>
		<footer>Site crée par <a href="https://twitter.com/Desmu_CS/">@Desmu_CS</a></footer>
	</body>
</html>