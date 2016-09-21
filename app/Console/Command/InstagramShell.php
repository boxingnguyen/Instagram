<?php
class InstagramShell extends AppShell {
	public function main() {
		passthru(ROOT."/app/Console/cake GetMedia");
		passthru(ROOT."/app/Console/cake GetAccountInfo");
		passthru(ROOT."/app/Console/cake CalculateReaction");
	}
}