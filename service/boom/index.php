<?php
	define('ROOT', '../..');
	include ROOT . '/lib/includeForAjax.php';

	requireStrictRoute();

	$response = array();
	$response['error'] = 0;
	$response['message'] = '';

	if (Validator::getBool(Settings::get('restrictBoom')) && !isLoggedIn()) {
		$response['error'] = 1;
		$response['message'] = _t('로그인 한 사람만 이 기능을 사용할 수 있습니다');
		func::printRespond($response);
	}

	requireComponent('Bloglounge.Model.Boom');

	if (!Validator::enum($_POST['direction'], 'up,down')) {
		$response['error'] = 1;
		$response['message'] = $_POST['direction'].'is undefined direction';
		func::printRespond($response);
	}

	if (!Validator::is_digit($_POST['itemId'])) {
		$response['error'] = 1;
		$response['message'] = 'illegal id';
	}

	$itemId = $_POST['itemId'];

	switch($_POST['direction']) {
		case 'up':				
			if (isLoggedIn()) {
				$userid = $session['id'];
				$boomedUp = Boom::isBoomedUp($itemId, 'userid', $userid);	
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
				$boomedUp = Boom::isBoomedUp($itemId, 'ip', $ip);	
			}			

			if ($boomedUp ) {
				Boom::upReturn($itemId);
				$response['message'] = 'isntBoomedUp';
			} else {
				if (!Boom::up($itemId)) {
					$response['error'] = 1;
					$response['message'] = _t('이미 추천한 글입니다.');
				} else {
					$response['message'] = 'isBoomedUp';
				}
			}
		break;
		case 'down':	
			if (isLoggedIn()) {
				$userid = $session['id'];
				$boomedDown = Boom::isBoomedDown($itemId, 'userid', $userid);
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
				$boomedDown = Boom::isBoomedDown($itemId, 'ip', $ip);
			}	
			if ($boomedDown) {
				Boom::downReturn($itemId);
				$response['message'] = 'isntBoomedDown';
			} else {
				if (!Boom::down($_POST['itemId'])) {
					$response['error'] = 1;
					$response['message'] = _t('이미 반대한 글입니다.');
				} else { // 자동숨기기, 삭제기능..
					$response['message'] = 'isBoomedDown';
					list($reactor, $limit) = Settings::gets('boomDownReactor,boomDownReactorLimit');
					if ($reactor == 'delete') {
						requireComponent('Bloglounge.Data.FeedItems');
						list($myBoomDown) = FeedItem::get($_POST['itemId'], 'boomDown');
						if ($limit < $myBoomDown) {
							FeedItem::delete($_POST['itemId']);
						}
					}
				}		
			}
			break;
	}

	func::printRespond($response);
?>
