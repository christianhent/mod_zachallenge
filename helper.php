<?php
defined('_JEXEC') or die;

require 'vendor/autoload.php';

use Goutte\Client as Client;
use GuzzleHttp\Exception\RequestException;

abstract class modZachallengeHelper
{
	public static function getData(&$params)
	{
		$client                = new Client();
		$challenge             = new stdClass();
		$challenge->base_uri   = 'https://www.endomondo.com/challenges/';
		$challenge->id         = (int) $params->get('challenge_id');
		$challenge->uri        = $challenge->base_uri . $challenge->id;
		$challenge->name       = '';
		$challenge->desc       = '';
		$challenge->logo_xs    = '';
		$challenge->logo_full  = '';
		$challenge->goal       = '';
		$challenge->prize      = '';
		$challenge->start      = '';
		$challenge->end        = '';
		$challenge->odo        = NULL;
		$challenge->attn_count = NULL;
		$challenge->ranking    = NULL;

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$cacheFile = JFile::makeSafe('challenge_' . $challenge->id . '.json');
		$cacheFilePath = JPath::clean(JPATH_ROOT . '/modules/mod_zachallenge/cache/' . $cacheFile);

		if(JFile::exists($cacheFilePath))
		{
			$fileinfos = stat($cacheFilePath);

			if ($fileinfos[9] > (time() - (60 * $params->get('cache'))))
			{
				if(!file_get_contents($cacheFilePath))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('Failed to load the challenge'), 'notice');

					return false;
				}

				$result = json_decode(file_get_contents($cacheFilePath), true);

				return (object)$result;
			}
			else
			{
				$result =  modZachallengeHelper::getChallengeData($client, $challenge, (int) $params->get('measure'));

				if ($result)
				{
					modZachallengeHelper::updateCacheFile($cacheFilePath, $result);

					return $result;
				}

				return false;
			}
		}
		else
		{
			$result =  modZachallengeHelper::getChallengeData($client, $challenge, (int) $params->get('measure'));

			modZachallengeHelper::updateCacheFile($cacheFilePath, $result);

			return $result;
		}
	}

	static function getChallengeData($client, $challenge, $measure)
	{
		$app = JFactory::getApplication();

		try
		{
			$response = $client->request('GET', $challenge->uri);
		}
		catch (RequestException $e)
		{
			$app->enqueueMessage(JText::_('Unable to load the Endomondo page '), 'error');

			return false;
		}

		$status_code = $client->getResponse()->getStatus();

		if ($status_code == '200')
		{
			try
			{
				modZachallengeHelper::setProfile($response, $challenge, $measure);
				modZachallengeHelper::setRanking($response, $challenge, $measure);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(JText::_('Something got wrong'), 'error');
			}

			unset($challenge->base_uri);

			return $challenge;
		}
	}

	static function updateCacheFile($cacheFilePath, $result)
	{
		
		file_put_contents($cacheFilePath, json_encode($result, true));
	}

	static function setProfile($response, $challenge, $measure)
	{
		$challenge->name       = $response->filter('div.navigationHeading')->eq(0)->text();
		$challenge->desc       = $response->filter('div.seeMoreText > p')->eq(0)->html();
		$challenge->logo_xs    = $response->filter('div.tinyPicture > img')->attr('src');
		$challenge->logo_full  = $response->filter('div.challengePicture > img.thumbnail')->attr('src');
		$challenge->goal       = $response->filter('div.info > div.type > div > span')->eq(1)->text();
		$challenge->start      = $response->filter('div.duration > div.start > span')->eq(1)->text();
		$challenge->end        = $response->filter('div.duration > div.end > span')->eq(1)->text();
		$challenge->odo        = modZachallengeHelper::asNumeric($response->filter('div.summaryPanel > div.item')->eq(2)->text());
		$challenge->attn_count = modZachallengeHelper::asNumeric($response->filter('div.summaryPanel > div.item')->eq(0)->text());

		# converts distance values to miles
    	if ($measure == 1)
    	{
    		$challenge->odo = round(modZachallengeHelper::covertDistance2Miles($challenge->odo));
    	}

		# prize
		if($response->filter('div.info > div.type > div > span')->eq(2)->text() == 'Prize:')
		{
			$challenge->prize  = $response->filter('div.info > div.type > div > span')->eq(3)->text();
		}
		
		return $challenge;
	}

	static function setRanking($response, $challenge, $measure)
	{
		$position = $response->filter('div.chart-area > div > div > ul > li.item > div.chart-row > div > div > span.rank')->each(function ($node)
		{
			return $node->text();
    	});

    	$user = $response->filter('div.chart-area > div > div > ul > li.item > div.chart-row > div > div > table > tr > td > a.name')->each(function ($node)
    	{
    		return $node->text();
    	});



    	$distance = $response->filter('div.chart-area > div > div > ul > li.item > div.chart-row > div > div.nose')->each(function ($node)
    	{
    		return round(modZachallengeHelper::asNumeric($node->text()));
    	});

    	$profile = $response->filter('div.chart-area > div > div > ul > li.item > div.chart-row > div > div > table > tr > td > a.name')->each(function ($node)
    	{
    		return modZachallengeHelper::formatUri($node->attr('href'));
    	});

    	$avatar = $response->filter('div.chart-area > div > div > ul > li.item > div.chart-row > div > div > a.thumbnail > img.thumbnail')->each(function ($node)
    	{
    		return $node->attr('src');
    	});

    	$challenge->ranking = array_combine($position, array_map(null, $user, $distance, $profile, $avatar));

    	$challenge->ranking = array_map(function($rank) {

    		return array(
    			'name' => $rank[0],
    			'distance' => $rank[1],
    			'profile' => $rank[2],
    			'avatar' => $rank[3]
    		);
    	}, $challenge->ranking);

    	# converts distance values to miles
    	if ($measure == 1)
    	{
    		foreach ($challenge->ranking as &$rank)
    		{
    			$rank['distance'] =  round(modZachallengeHelper::covertDistance2Miles($rank['distance']));
    		}
    	}

    	return $challenge;
    }

    static function formatUri($str)
	{
  		$str = str_replace("../profile/", "", $str);

  		return 'https://www.endomondo.com/profile/' . (float) $str;
	}

	static function asNumeric($str)
	{
  		$str = str_replace(",", "", $str);

  		if(preg_match("#([0-9\.]+)#", $str, $match))
  		{
      		$str = $match[0];

      		return $str;
  		}

  		return $str;
	}

	static function covertDistance2Miles($distance)
    {
        $ratio = 0.621371192;

        $miles = $distance * $ratio;
        
        return $miles;
    }   
}