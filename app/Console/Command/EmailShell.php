<?php
App::uses('CakeEmail', 'Network/Email');
class EmailShell extends Shell {
	public function main() {
		$Email = new CakeEmail();
		$Email->from(array('ducnv@tmh-techlab.vn' => 'Ducnv'));
		$Email->to('ducnv@tmh-techlab.vn');
		$Email->subject('Subject');
		$Email->send('My message 2');
	}
}