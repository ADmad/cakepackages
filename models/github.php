<?php
class Github extends AppModel {
	var $name = 'Github';
	var $useTable = false;

	function saveUser($username = null) {
		if (!$username) return false;
		$user = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/user/show/{$username}"));
			$user = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $user);
		}

		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('by_name', $user['User']['login']);
		if ($existingUser) {
			CakeLog::write('dang');
			return false;
		}

		$data = array(
			'Maintainer' => array(
				'username' => $user['User']['login'],
				'gravatar_id' => $user['User']['gravatar-id']));

		$data['Maintainer']['name'] = (isset($user['User']['name'])) ? $user['User']['name'] : '';
		$data['Maintainer']['company'] = (isset($user['User']['company'])) ? $user['User']['company'] : '';
		$data['Maintainer']['url'] = (isset($user['User']['blog'])) ? $user['User']['blog'] : '';
		$data['Maintainer']['email'] = (isset($user['User']['email'])) ? $user['User']['email'] : '';
		$data['Maintainer']['location'] = (isset($user['User']['location'])) ? $user['User']['location'] : '';

		return $maintainer->save($data);
	}

	function __findUser($username = null) {
		if (!$username) return false;
		$user = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/user/show/{$username}"));
			$user = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $user);
		}

		return $user;
	}

	function __findNewPackages($username = null) {
		if (!$username) return false;
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('view', $username);
		$repoList = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$username}"));
			$repoList = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $repoList);
		}

		$repos = $maintainer->Package->find('list', array(
			'conditions' => array(
				'Package.maintainer_id' => $existingUser['Maintainer']['id'])));
		if (isset($repoList['Repositories']['Repository']['description'])) {
			if (in_array($repoList['Repositories']['Repository']['name'], $repos)) return false;
			if ($repoList['Repositories']['Repository']['fork']['value'] == 'true') return false;
			return array('0' => $repoList['Repositories']['Repository']);
		} else {
		    if (!isset($repoList['Repositories']['Repository'])) return false;
			foreach ($repoList['Repositories']['Repository'] as $key => $package) {
				if (in_array($package['name'], $repos) || ($package['fork']['value'] == 'true')) {
					unset($repoList['Repositories']['Repository'][$key]);
				}
			}
			return $repoList['Repositories']['Repository'];
		}
	}

	function savePackage($username, $name) {
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('view', $username);
		$repo = $maintainer->Package->find('list', array(
			'conditions' => array(
				'Package.maintainer_id' => $existingUser['Maintainer']['id'],
				'Package.name' => $name)));
		if ($repo) return false;

		$repo = $this->find('package', array('user' => $username, 'name' => $name));
		if ($repo['Repository']['fork']['value'] == 'true') return false;
		$data = array(
			'Package' => array(
				'maintainer_id' => $existingUser['Maintainer']['id'],
				'name' => $name,
				'package_url' => $repo['Repository']['url'],
				'homepage' => $repo['Repository']['url'],
				'description' => $repo['Repository']['description']));
		return $maintainer->Package->save($data);
	}

	function __findPackage($params = array()) {
		$package = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$params['user']}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$params['user']}/{$params['name']}"));
			$package = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$package}", $package);
		}
		return $package;
	}

	function __findPackages($username = null) {
		if (!$username) return false;
		$repoList = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$username}"));
			$repoList = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $repoList);
		}

		return $repoList['Repositories']['Repository'];
	}

	function __findFollowing($username = null) {
		if (!$username) return false;
		$following = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/user/show/{$username}/following"));
			$following = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $following);
		}

		return $following;
	}

	function __findUnlisted($username = 'josegonzalez') {
		$following = $this->find('following', 'josegonzalez');
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$maintainers = $maintainer->find('list', array('fields' => array('username')));
		$maintainers = array_values($maintainers);
		foreach ($following['Users']['User'] as $key => &$user) {
			if (in_array($user, $maintainers)) {
				unset($following['Users']['User'][$key]);
			}
		}
		return $following['Users']['User'];
	}

}
?>